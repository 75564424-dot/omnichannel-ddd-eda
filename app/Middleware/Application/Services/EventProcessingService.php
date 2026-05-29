<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use App\Middleware\Application\Services\Processing\EventDeadLetterFinalizer;
use App\Middleware\Application\Services\Processing\EventProcessingAttemptExecutor;
use App\Middleware\Application\Services\Processing\EventProcessingDispatchPlanner;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use Throwable;

/**
 * Orchestrates event dispatch with retry recording, outbox relay, and DLQ (Plan_Resiliencia + Plan_Middleware).
 */
final class EventProcessingService
{
    public function __construct(
        private readonly QueueEntryRepositoryInterface $queueEntries,
        private readonly EventProcessingDispatchPlanner $dispatchPlanner,
        private readonly EventProcessingAttemptExecutor $attemptExecutor,
        private readonly EventDeadLetterFinalizer $deadLetterFinalizer,
    ) {}

    public function dispatchAfterPublish(
        string $eventId,
        string $eventType,
        array $payload,
        string $origin,
    ): void {
        $this->dispatchPlanner->dispatchAfterPublish(
            $eventId,
            $eventType,
            $payload,
            $origin,
            $this->attemptExecutor,
        );
    }

    public function processQueuedEvent(string $eventId): void
    {
        $entry = $this->queueEntries->findByEventId($eventId);
        if ($entry === null || $entry->status()->isProcessed()) {
            return;
        }

        $this->attemptExecutor->executeAttempt(
            $eventId,
            $entry->eventType(),
            $entry->payload(),
            $entry->origin(),
            1,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function publishToBus(
        string $eventId,
        string $eventType,
        array $payload,
        string $origin,
    ): void {
        $this->attemptExecutor->publishToBus($eventId, $eventType, $payload, $origin);
    }

    public function executeAttempt(
        string $eventId,
        string $eventType,
        array $payload,
        string $origin,
        int $attemptNumber,
    ): void {
        $this->attemptExecutor->executeAttempt($eventId, $eventType, $payload, $origin, $attemptNumber);
    }

    public function finalizeDeadLetter(
        string $eventId,
        string $eventType,
        array $payload,
        string $origin,
        ?Throwable $exception,
    ): void {
        $this->deadLetterFinalizer->finalize($eventId, $eventType, $payload, $origin, $exception);
    }
}
