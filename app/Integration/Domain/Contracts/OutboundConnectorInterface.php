<?php

declare(strict_types=1);

namespace App\Integration\Domain\Contracts;

/**
 * Outbound HTTP connector to external providers (Plan_Integraciones Fase 3).
 */
interface OutboundConnectorInterface
{
    /**
     * @param array<string, mixed> $payload
     * @return array{status: int, body: array<string, mixed>|null}
     */
    public function dispatch(string $endpoint, array $payload, array $config = [], ?string $bearerToken = null): array;
}
