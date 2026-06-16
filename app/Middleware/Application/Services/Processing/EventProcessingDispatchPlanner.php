<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services\Processing;

use App\Simulation\Application\Services\Runtime\SimulationPublishScope;
use App\Middleware\Domain\Repositories\OutboxRepositoryInterface;
use App\Middleware\Infrastructure\Jobs\ProcessEventJob;
use App\Middleware\Infrastructure\Jobs\RelayOutboxJob;

final class EventProcessingDispatchPlanner
{
    public function __construct(
        private readonly SimulationPublishScope $simulationScope,
        private readonly OutboxRepositoryInterface $outbox,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function dispatchAfterPublish(
        string $eventId,
        string $eventType,
        array $payload,
        string $origin,
        EventProcessingAttemptExecutor $executor,
    ): void {
        if ($this->simulationScope->shouldDeferProcessing()) {
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

        $executor->executeAttempt($eventId, $eventType, $payload, $origin, 1);
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
}
