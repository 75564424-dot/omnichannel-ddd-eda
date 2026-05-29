<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services\Processing;

use App\Middleware\Application\Services\EventLogService;
use App\Middleware\Application\Services\WorkflowEngine;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use App\Middleware\Domain\Repositories\RetryAttemptRepositoryInterface;
use App\Middleware\Infrastructure\Resilience\ConnectorCircuitBreaker;
use App\Shared\Contracts\EventBus\EventBusPort;
use Throwable;

final class EventProcessingAttemptExecutor
{
    public function __construct(
        private readonly QueueEntryRepositoryInterface $queueEntries,
        private readonly RetryAttemptRepositoryInterface $retries,
        private readonly ConnectorCircuitBreaker $circuitBreaker,
        private readonly EventBusPort $eventBus,
        private readonly WorkflowEngine $workflowEngine,
        private readonly EventLogService $eventLogs,
    ) {}

    /**
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

    /**
     * @param array<string, mixed> $payload
     */
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
}
