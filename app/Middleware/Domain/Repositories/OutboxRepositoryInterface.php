<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Repositories;

interface OutboxRepositoryInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function enqueue(string $eventId, string $eventType, string $origin, array $payload): int;

    /**
     * @return list<array{id: int, event_uuid: string, event_type: string, origin: string, payload: array<string, mixed>}>
     */
    public function claimPending(int $limit = 50): array;

    public function markPublished(int $id): void;

    public function markFailed(int $id): void;
}
