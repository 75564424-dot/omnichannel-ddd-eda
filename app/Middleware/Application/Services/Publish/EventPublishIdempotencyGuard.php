<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services\Publish;

use App\Middleware\Domain\Repositories\EventStoreRepositoryInterface;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;

final class EventPublishIdempotencyGuard
{
    public function __construct(
        private readonly EventStoreRepositoryInterface $eventStoreRepository,
        private readonly QueueEntryRepositoryInterface $queueEntryRepository,
    ) {}

    public function isAlreadyPublished(string $eventId): bool
    {
        return $this->eventStoreRepository->existsByEventId($eventId)
            || $this->queueEntryRepository->existsByEventId($eventId);
    }
}
