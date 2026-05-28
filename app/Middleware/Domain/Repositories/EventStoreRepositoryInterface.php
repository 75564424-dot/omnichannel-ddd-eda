<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Repositories;

use App\Middleware\Domain\Entities\StoredEvent;

interface EventStoreRepositoryInterface
{
    public function append(StoredEvent $event): int;

    public function existsByEventId(string $eventId): bool;
}
