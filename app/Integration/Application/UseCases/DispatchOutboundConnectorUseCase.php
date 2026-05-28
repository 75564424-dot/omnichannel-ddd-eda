<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Domain\Repositories\ConnectorRepositoryInterface;
use App\Integration\Domain\Repositories\IntegrationCredentialRepositoryInterface;
use App\Integration\Domain\Repositories\IntegrationRepositoryInterface;
use App\Integration\Infrastructure\Connectors\HttpOutboundConnector;
use RuntimeException;

/**
 * Dispatches payload to an outbound HTTP connector (Plan_Integraciones Fase 3).
 */
final class DispatchOutboundConnectorUseCase
{
    public function __construct(
        private readonly IntegrationRepositoryInterface $integrations,
        private readonly ConnectorRepositoryInterface $connectors,
        private readonly IntegrationCredentialRepositoryInterface $credentials,
        private readonly HttpOutboundConnector $httpConnector,
    ) {}

    /**
     * @param array<string, mixed> $payload
     * @return array{status: int, body: array<string, mixed>|null}
     */
    public function execute(string $integrationId, string $connectorId, array $payload): array
    {
        $integration = $this->integrations->findById($integrationId);
        if ($integration === null) {
            throw new RuntimeException('Integration not found.', 404);
        }

        if (($integration['direction'] ?? '') !== 'outbound') {
            throw new RuntimeException('Integration is not outbound.', 422);
        }

        $connector = $this->connectors->findById($connectorId);
        if ($connector === null || $connector['integration_id'] !== $integrationId) {
            throw new RuntimeException('Connector not found.', 404);
        }

        $endpoint = (string) ($connector['endpoint'] ?? '');
        /** @var array<string, mixed> $config */
        $config = is_array($connector['config']) ? $connector['config'] : [];

        $token = $this->credentials->getPlaintext($integrationId, 'api_bearer_token');

        return $this->httpConnector->dispatch($endpoint, $payload, $config, $token);
    }
}
