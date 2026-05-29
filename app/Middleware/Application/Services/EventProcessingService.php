<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use App\Middleware\Domain\Entities\DeadLetterEntry;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;
use App\Middleware\Domain\Repositories\OutboxRepositoryInterface;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use App\Middleware\Domain\Repositories\RetryAttemptRepositoryInterface;
use App\Middleware\Infrastructure\Jobs\ProcessEventJob;
use App\Middleware\Infrastructure\Jobs\RelayOutboxJob;
use App\Middleware\Infrastructure\Resilience\ConnectorCircuitBreaker;
use App\Shared\Contracts\EventBus\EventBusPort;
use App\Shared\Logging\PlatformStructuredLogger;
use Throwable;

/**
 * Orchestrates event dispatch with retry recording, outbox relay, and DLQ (Plan_Resiliencia + Plan_Middleware).
 */
final class EventProcessingService
{
    public function __construct(
        private readonly QueueEntryRepositoryInterface $queueEntries,
        private readonly RetryAttemptRepositoryInterface $retries,
        private readonly DeadLetterRepositoryInterface $deadLetters,
        private readonly ConnectorCircuitBreaker $circuitBreaker,
        private readonly EventBusPort $eventBus,
        private readonly OutboxRepositoryInterface $outbox,
        private readonly WorkflowEngine $workflowEngine,
        private readonly EventLogService $eventLogs,
        private readonly PlatformStructuredLogger $logger,
    ) {}

    public function dispatchAfterPublish(
        string $eventId,
        string $eventType,
        array $payload,
        string $origin,
    ): void {
        if ($this->shouldDeferProcessing()) {
            return;
        }

        if ($this->isOutboxEnabled()) {
            $this->outbox->enqueue($eventId, $eventType, $origin, $payload);
            RelayOutboxJob::dispatch()
                ->onQueue((string) config('eventbus.queues.middleware', 'middleware'));

            return;
        }

        if ($this->isAsyncProcessing()) {
            ProcessEventJob::dispatch($eventId, $eventType, $payload, $origin)
                ->onQueue((string) config('eventbus.queues.middleware', 'middleware'));

            return;
        }

        $this->executeAttempt($eventId, $eventType, $payload, $origin, 1);
    }

    /**
     * Processes a queued event after simulation publish (scope deferral ended).
     */
    public function processQueuedEvent(string $eventId): void
    {
        $entry = $this->queueEntries->findByEventId($eventId);
        if ($entry === null) {
            return;
        }

        if ($entry->status()->isProcessed()) {
            return;
        }

        $this->executeAttempt(
            $eventId,
            $entry->eventType(),
            $entry->payload(),
            $entry->origin(),
            1,
        );
    }

    /**
     * Publishes to runtime bus without retry wrapper (outbox relay path).
     *
     * @param array<string, mixed> $payload
     */
    public function publishToBus(
        string $eventId,
        string $eventType,
        array $payload,
        string $origin,
    ): void {
        $this->eventBus->publish($eventType, $payload);
        $this->workflowEngine->triggerForEvent($eventId, $eventType, $payload);
    }

    public function executeAttempt(
        string $eventId,
        string $eventType,
        array $payload,
        string $origin,
        int $attemptNumber,
    ): void {
        $connectorKey = 'event:'.$eventType;
        if ($this->circuitBreaker->isOpen($connectorKey)) {
            throw new \RuntimeException("Circuit breaker open for {$eventType}");
        }

        $entry   = $this->queueEntries->findByEventId($eventId);
        $queueId = $entry?->id() ?? 0;

        try {
            $this->retries->recordAttempt($queueId, $eventId, $attemptNumber, 'executing');

            $this->publishToBus($eventId, $eventType, $payload, $origin);

            $this->retries->recordAttempt($queueId, $eventId, $attemptNumber, 'completed');
            $this->circuitBreaker->recordSuccess($connectorKey);
        } catch (Throwable $e) {
            $this->retries->recordAttempt($queueId, $eventId, $attemptNumber, 'failed', $e->getMessage());
            $this->circuitBreaker->recordFailure($connectorKey);

            $this->eventLogs->recordFailed(
                eventId: $eventId,
                eventType: $eventType,
                origin: $origin,
                payload: $payload,
                reason: $e->getMessage(),
                correlationId: $entry?->correlationId(),
            );

            if ($entry !== null) {
                $entry->markFailed();
                $this->queueEntries->save($entry);
            }

            throw $e;
        }
    }

    public function finalizeDeadLetter(
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

    private function isAsyncProcessing(): bool
    {
        return filter_var(
            config('eventbus.resilience.async_processing', false),
            FILTER_VALIDATE_BOOLEAN,
        );
    }

    private function isOutboxEnabled(): bool
    {
        return filter_var(
            config('eventbus.outbox.enabled', false),
            FILTER_VALIDATE_BOOLEAN,
        );
    }

    private function shouldDeferProcessing(): bool
    {
        return app(SimulationPublishScope::class)->shouldDeferProcessing();
    }
}
