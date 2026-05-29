<?php

declare(strict_types=1);

namespace App\Integration\Application\Services;

use App\Integration\Domain\Repositories\WebhookRequestRepositoryInterface;

final class WebhookIngressAuditor
{
    public function __construct(
        private readonly WebhookRequestRepositoryInterface $webhooks,
    ) {}

    /**
     * @param array<string, mixed> $integration
     * @param array<string, mixed> $body
     * @param array<string, string|null> $headers
     */
    public function recordReceived(
        array $integration,
        array $body,
        array $headers,
        string $httpMethod,
        string $requestPath,
        ?string $sourceIp,
    ): string {
        return $this->webhooks->recordReceived([
            'integration_id'  => $integration['id'],
            'channel_id'      => $integration['channel_id'],
            'http_method'     => $httpMethod,
            'request_path'    => $requestPath,
            'request_headers' => $headers,
            'request_body'    => $body,
            'source_ip'       => $sourceIp,
            'status'          => 'received',
        ]);
    }

    /**
     * @param array<string, mixed> $responseBody
     */
    public function recordSuccess(string $webhookId, int $httpStatus, array $responseBody, int $latencyMs): void
    {
        $this->webhooks->markStatus($webhookId, 'processed');
        $this->webhooks->recordResponse([
            'webhook_request_id' => $webhookId,
            'http_status'        => $httpStatus,
            'response_body'      => $responseBody,
            'latency_ms'         => $latencyMs,
        ]);
    }

    /**
     * @param array<string, mixed> $responseBody
     */
    public function recordFailure(string $webhookId, int $httpStatus, array $responseBody, int $latencyMs): void
    {
        $this->webhooks->markStatus($webhookId, 'failed');
        $this->webhooks->recordResponse([
            'webhook_request_id' => $webhookId,
            'http_status'        => $httpStatus,
            'response_body'      => $responseBody,
            'latency_ms'         => $latencyMs,
        ]);
    }
}
