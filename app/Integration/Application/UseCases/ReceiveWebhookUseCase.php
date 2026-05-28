<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Application\Services\AdapterPipeline;
use App\Integration\Domain\Repositories\IntegrationCredentialRepositoryInterface;
use App\Integration\Domain\Repositories\IntegrationRepositoryInterface;
use App\Integration\Domain\Repositories\WebhookRequestRepositoryInterface;
use App\Integration\Domain\Services\WebhookSignatureVerifier;
use App\Middleware\Application\Services\EventPublisherService;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * Webhook ingress: verify signature → adapters → publish → audit (Plan_Integraciones Fase 1).
 */
final class ReceiveWebhookUseCase
{
    public function __construct(
        private readonly IntegrationRepositoryInterface $integrations,
        private readonly IntegrationCredentialRepositoryInterface $credentials,
        private readonly WebhookRequestRepositoryInterface $webhooks,
        private readonly WebhookSignatureVerifier $signatureVerifier,
        private readonly AdapterPipeline $adapterPipeline,
        private readonly EventPublisherService $eventPublisher,
    ) {}

    /**
     * @param array<string, mixed> $body
     * @param array<string, string|null> $headers
     * @return array{webhook_request_id: string, event_id: string, entry_id: int}
     */
    public function execute(
        string $integrationCode,
        string $rawBody,
        array $body,
        array $headers,
        string $httpMethod,
        string $requestPath,
        ?string $sourceIp,
    ): array {
        $started = microtime(true);
        $integration = $this->integrations->findActiveByCode($integrationCode);
        if ($integration === null) {
            throw new RuntimeException("Integration '{$integrationCode}' not found or inactive.", 404);
        }

        if (($integration['direction'] ?? '') !== 'inbound') {
            throw new RuntimeException("Integration '{$integrationCode}' is not inbound.", 422);
        }

        $secret = $this->credentials->getPlaintext($integration['id'], 'webhook_hmac_secret');
        $signatureHeader = config('integrations.webhook.signature_header', 'X-Webhook-Signature');
        $providedSig = $headers[strtolower($signatureHeader)] ?? $headers['x-webhook-signature'] ?? null;

        if ($secret !== null && ! $this->signatureVerifier->verify($rawBody, $secret, $providedSig)) {
            throw new RuntimeException('Invalid webhook signature.', 401);
        }

        if ($secret === null && filter_var(config('integrations.webhook.require_secret', true), FILTER_VALIDATE_BOOLEAN)) {
            throw new RuntimeException('Webhook secret not configured for integration.', 503);
        }

        $webhookId = $this->webhooks->recordReceived([
            'integration_id'  => $integration['id'],
            'channel_id'      => $integration['channel_id'],
            'http_method'     => $httpMethod,
            'request_path'    => $requestPath,
            'request_headers' => $headers,
            'request_body'    => $body,
            'source_ip'       => $sourceIp,
            'status'          => 'received',
        ]);

        try {
            /** @var array<string, mixed> $config */
            $config  = is_array($integration['config']) ? $integration['config'] : [];
            $payload = $this->adapterPipeline->process($integration['id'], $body, $config);
            $envelope = $this->buildEnvelope($integration, $payload, $config);

            $result = $this->eventPublisher->publish($envelope);
            $this->webhooks->markStatus($webhookId, 'processed');

            $latencyMs = (int) ((microtime(true) - $started) * 1000);
            $this->webhooks->recordResponse([
                'webhook_request_id' => $webhookId,
                'http_status'        => 202,
                'response_body'      => [
                    'success'  => true,
                    'event_id' => $envelope['event_id'],
                    'entry_id' => $result->entryId,
                ],
                'latency_ms' => $latencyMs,
            ]);

            return [
                'webhook_request_id' => $webhookId,
                'event_id'           => $envelope['event_id'],
                'entry_id'           => $result->entryId,
            ];
        } catch (\Throwable $e) {
            $this->webhooks->markStatus($webhookId, 'failed');
            $this->webhooks->recordResponse([
                'webhook_request_id' => $webhookId,
                'http_status'        => 422,
                'response_body'      => ['success' => false, 'error' => $e->getMessage()],
                'latency_ms'         => (int) ((microtime(true) - $started) * 1000),
            ]);

            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $integration
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function buildEnvelope(array $integration, array $payload, array $config): array
    {
        /** @var array<string, mixed> $webhookConfig */
        $webhookConfig = $config['webhook'] ?? [];

        $eventTypeField = (string) ($webhookConfig['event_type_field'] ?? 'event_type');
        $eventIdField   = (string) ($webhookConfig['event_id_field'] ?? 'event_id');
        $occurredField  = (string) ($webhookConfig['occurred_at_field'] ?? 'occurred_at');

        $eventType = (string) ($payload[$eventTypeField] ?? $payload['event'] ?? '');
        if ($eventType === '') {
            throw new InvalidArgumentException('Webhook payload missing event_type.');
        }

        $eventId = (string) ($payload[$eventIdField] ?? '');
        if ($eventId === '' || ! Uuid::isValid($eventId)) {
            $eventId = Uuid::uuid4()->toString();
            $payload[$eventIdField] = $eventId;
        }

        $occurredAt = (string) ($payload[$occurredField] ?? now()->toIso8601String());
        $payload['event_id']    = $eventId;
        $payload['event']       = $eventType;
        $payload['event_type']  = $eventType;
        $payload['occurred_at'] = $occurredAt;

        return [
            'event_id'       => $eventId,
            'event_type'     => $eventType,
            'occurred_at'    => $occurredAt,
            'origin'         => (string) ($webhookConfig['origin'] ?? 'Webhook:'.$integration['code']),
            'payload'        => $payload,
            'channel_id'     => $integration['channel_id'],
            'integration_id' => $integration['id'],
        ];
    }
}
