<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Repositories;

interface WorkflowRepositoryInterface
{
    /**
     * @return list<array{id: string, code: string, name: string, config: array<string, mixed>|null}>
     */
    public function findActiveByTriggerEventType(string $eventType): array;
}
