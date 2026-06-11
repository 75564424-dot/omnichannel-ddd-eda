<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Application\Services\WebhookInboundIntegrationResolver;
use App\Integration\Application\Services\WebhookIngressAuditor;
use App\Integration\Application\Services\WebhookIngressProcessor;
use App\Integration\Application\Services\WebhookSignatureGateService;

/**
 * Webhook ingress: verify signature → adapters → publish → audit (Plan_Integraciones Fase 1).
 */
final class ReceiveWebhookUseCase
{
    public function __construct(
        private readonly WebhookInboundIntegrationResolver $integrationResolver,
        private readonly WebhookSignatureGateService $signatureGate,
        private readonly WebhookIngressAuditor $auditor,
        private readonly WebhookIngressProcessor $ingressProcessor,
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
            $published = $this->ingressProcessor->process($integration, $body);
            $this->auditor->recordSuccess($webhookId, 202, [
                'success'  => true,
                'event_id' => $published['event_id'],
                'entry_id' => $published['entry_id'],
            ], $this->latencyMs($started));

            return [
                'webhook_request_id' => $webhookId,
                'event_id'           => $published['event_id'],
                'entry_id'           => $published['entry_id'],
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
