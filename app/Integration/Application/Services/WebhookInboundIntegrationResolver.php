<?php

declare(strict_types=1);

namespace App\Integration\Application\Services;

use App\Integration\Domain\Repositories\IntegrationRepositoryInterface;
use RuntimeException;

final class WebhookInboundIntegrationResolver
{
    public function __construct(
        private readonly IntegrationRepositoryInterface $integrations,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function resolve(string $integrationCode): array
    {
        $integration = $this->integrations->findActiveByCode($integrationCode);
        if ($integration === null) {
            throw new RuntimeException("Integration '{$integrationCode}' not found or inactive.", 404);
        }

        if (($integration['direction'] ?? '') !== 'inbound') {
            throw new RuntimeException("Integration '{$integrationCode}' is not inbound.", 422);
        }

        return $integration;
    }
}
