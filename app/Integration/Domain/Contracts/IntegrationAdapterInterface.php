<?php

declare(strict_types=1);

namespace App\Integration\Domain\Contracts;

/**
 * Strategy for transforming inbound integration payloads (Plan_Integraciones).
 */
interface IntegrationAdapterInterface
{
    public function type(): string;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function transform(array $payload, array $config = []): array;
}
