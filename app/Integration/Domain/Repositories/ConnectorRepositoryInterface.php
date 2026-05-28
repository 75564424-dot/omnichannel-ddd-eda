<?php

declare(strict_types=1);

namespace App\Integration\Domain\Repositories;

interface ConnectorRepositoryInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function findById(string $connectorId): ?array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listForIntegration(string $integrationId): array;
}
