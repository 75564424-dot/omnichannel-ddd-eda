<?php

declare(strict_types=1);

namespace App\Integration\Domain\Repositories;

interface IntegrationRepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listAll(): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findById(string $id): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function findActiveByCode(string $code): ?array;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): string;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): void;

    public function delete(string $id): void;
}
