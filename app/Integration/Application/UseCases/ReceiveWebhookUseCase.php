<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Application\Services\AdapterPipeline;
use App\Integration\Application\Services\WebhookEventEnvelopeBuilder;
use App\Integration\Application\Services\WebhookInboundIntegrationResolver;
use App\Integration\Application\Services\WebhookIngressAuditor;
use App\Integration\Application\Services\WebhookSignatureGateService;
use App\Integration\Domain\Contracts\ExternalEventPublisherInterface;

/**
 * Webhook ingress: verify signature → adapters → publish → audit (Plan_Integraciones Fase 1).
 */
final class ReceiveWebhookUseCase
{
    public function __construct(
        private readonly WebhookInboundIntegrationResolver $integrationResolver,
        private readonly WebhookSignatureGateService $signatureGate,
        private readonly WebhookIngressAuditor $auditor,
        private readonly AdapterPipeline $adapterPipeline,
        private readonly WebhookEventEnvelopeBuilder $envelopeBuilder,
        private readonly ExternalEventPublisherInterface $eventPublisher,
    ) {}

    /**
     * @param array<string, mixed> $body
     * @param array<string, string|null> $headers
     *
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
        $integration = $this->integrationResolver->resolve($integrationCode);

        $this->signatureGate->assertValid($integration['id'], $rawBody, $headers);

        $webhookId = $this->auditor->recordReceived(
            $integration,
            $body,
            $headers,
            $httpMethod,
            $requestPath,
            $sourceIp,
        );

        try {
            /** @var array<string, mixed> $config */
            $config  = is_array($integration['config']) ? $integration['config'] : [];
            $payload = $this->adapterPipeline->process($integration['id'], $body, $config);
            $envelope = $this->envelopeBuilder->build($integration, $payload, $config);

            $result = $this->eventPublisher->publish($envelope);
            $this->auditor->recordSuccess($webhookId, 202, [
                'success'  => true,
                'event_id' => $envelope['event_id'],
                'entry_id' => $result->entryId,
            ], $this->latencyMs($started));

            return [
                'webhook_request_id' => $webhookId,
                'event_id'           => $envelope['event_id'],
                'entry_id'           => $result->entryId,
            ];
        } catch (\Throwable $e) {
            $this->auditor->recordFailure($webhookId, 422, [
                'success' => false,
                'error'   => $e->getMessage(),
            ], $this->latencyMs($started));

            throw $e;
        }
    }

    private function latencyMs(float $started): int
    {
        return (int) ((microtime(true) - $started) * 1000);
    }
}
