<?php

declare(strict_types=1);

namespace App\Integration\Application\Services;

use App\Integration\Domain\Repositories\IntegrationCredentialRepositoryInterface;
use App\Integration\Domain\Services\WebhookSignatureVerifier;
use RuntimeException;

final class WebhookSignatureGateService
{
    public function __construct(
        private readonly IntegrationCredentialRepositoryInterface $credentials,
        private readonly WebhookSignatureVerifier $signatureVerifier,
    ) {}

    /**
     * @param array<string, string|null> $headers
     */
    public function assertValid(string $integrationId, string $rawBody, array $headers): void
    {
        $secret = $this->credentials->getPlaintext($integrationId, 'webhook_hmac_secret');
        $signatureHeader = config('integrations.webhook.signature_header', 'X-Webhook-Signature');
        $providedSig = $headers[strtolower($signatureHeader)] ?? $headers['x-webhook-signature'] ?? null;

        if ($secret !== null && ! $this->signatureVerifier->verify($rawBody, $secret, $providedSig)) {
            throw new RuntimeException('Invalid webhook signature.', 401);
        }

        if ($secret === null && filter_var(config('integrations.webhook.require_secret', true), FILTER_VALIDATE_BOOLEAN)) {
            throw new RuntimeException('Webhook secret not configured for integration.', 503);
        }
    }
}
