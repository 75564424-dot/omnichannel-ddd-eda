<?php

declare(strict_types=1);

namespace App\Middleware\Application\UseCases;

use App\Middleware\Application\Services\EventProcessingService;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use RuntimeException;

/**
 * Requeues a dead-letter entry back to message_queue for reprocessing (Plan_Resiliencia).
 */
final class RequeueDeadLetterUseCase
{
    public function __construct(
        private readonly DeadLetterRepositoryInterface $deadLetters,
        private readonly QueueEntryRepositoryInterface $queueEntries,
        private readonly EventProcessingService $processing,
    ) {}

    public function execute(int $deadLetterId): void
    {
        $entry = $this->deadLetters->findById($deadLetterId);
        if ($entry === null) {
            throw new RuntimeException("Dead letter #{$deadLetterId} not found.");
        }

        if ($entry->isResolved()) {
            throw new RuntimeException("Dead letter #{$deadLetterId} is already resolved.");
        }

        $queueEntry = $this->queueEntries->findByEventId($entry->eventId());
        if ($queueEntry !== null) {
            $this->queueEntries->resetForRequeue($entry->eventId());
        }

        $this->deadLetters->markRequeued($deadLetterId);

        $this->processing->dispatchAfterPublish(
            eventId: $entry->eventId(),
            eventType: $entry->eventType(),
            payload: $entry->payload(),
            origin: $entry->origin(),
        );
    }
}
