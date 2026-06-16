<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services\Processing;

use App\Middleware\Application\Services\EventLogService;
use App\Middleware\Domain\Entities\DeadLetterEntry;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use App\Shared\Logging\PlatformStructuredLogger;
use Throwable;

final class EventDeadLetterFinalizer
{
    public function __construct(
        private readonly DeadLetterRepositoryInterface $deadLetters,
        private readonly QueueEntryRepositoryInterface $queueEntries,
        private readonly EventLogService $eventLogs,
        private readonly PlatformStructuredLogger $logger,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function finalize(
        string $eventId,
        string $eventType,
        array $payload,
        string $origin,
        ?Throwable $exception,
    ): void {
        $reason = $exception?->getMessage() ?? 'Processing exhausted retries';

        $this->deadLetters->save(DeadLetterEntry::fromFailedJob(
            eventId: $eventId,
            eventType: $eventType,
            origin: $origin,
            payload: $payload,
            failureReason: $reason,
        ));

        $this->queueEntries->markDeadLettered($eventId);

        $this->eventLogs->recordFailed($eventId, $eventType, $origin, $payload, $reason);

        $this->logger->warning('Event moved to dead letter queue', [
            'event_uuid' => $eventId,
            'event_type' => $eventType,
            'reason'     => $reason,
        ]);
    }
}
