<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Repositories;

use App\Middleware\Domain\Entities\QueueEntry;

interface QueueEntryRepositoryInterface
{
    public function save(QueueEntry $entry): int;

    public function findByEventId(string $eventId): ?QueueEntry;

    public function existsByEventId(string $eventId): bool;

    /** @return QueueEntry[] */
    public function getRecent(int $limit = 50): array;

    /** @return QueueEntry[] */
    public function getPaginated(int $page, int $limit): array;

    public function countAll(): int;

    public function countByStatus(string $status, int $lastSeconds = 60): int;

    public function computeAverageProcessingTimeMs(int $lastN = 100): int;

    public function countTotal(int $lastSeconds = 60): int;

    public function markDeadLettered(string $eventId): void;

    public function resetForRequeue(string $eventId): void;
}
