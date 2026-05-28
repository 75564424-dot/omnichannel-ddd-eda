<?php

declare(strict_types=1);

namespace App\Integration\Domain\Repositories;

interface AdapterRepositoryInterface
{
    /**
     * @return list<array{id: string, adapter_type: string, config: array<string, mixed>|null, priority: int}>
     */
    public function listEnabledForIntegration(string $integrationId): array;
}
