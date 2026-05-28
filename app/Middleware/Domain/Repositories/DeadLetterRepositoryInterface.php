<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Repositories;

use App\Middleware\Domain\Entities\DeadLetterEntry;

interface DeadLetterRepositoryInterface
{
    public function save(DeadLetterEntry $entry): void;

    public function existsByEventId(string $eventId): bool;

    /** @return DeadLetterEntry[] */
    public function findUnresolved(): array;

    public function countUnresolved(): int;

    public function markResolved(int $id): void;

    public function findById(int $id): ?DeadLetterEntry;

    public function markRequeued(int $id): void;

    /** Syncs failed_jobs from Laravel into bus_dead_letters */
    public function syncFromFailedJobs(): int;
}
