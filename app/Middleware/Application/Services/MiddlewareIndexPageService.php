<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use App\Middleware\Application\UseCases\GetBusMetricsUseCase;
use App\Middleware\Application\UseCases\GetBusStatusUseCase;
use App\Middleware\Application\UseCases\GetDeadLetterQueueUseCase;
use App\Middleware\Application\UseCases\GetEventQueueUseCase;
use App\Middleware\Application\UseCases\GetTopologySnapshotUseCase;

final class MiddlewareIndexPageService
{
    public function __construct(
        private readonly GetBusMetricsUseCase $getBusMetrics,
        private readonly GetEventQueueUseCase $getEventQueue,
        private readonly GetTopologySnapshotUseCase $getTopologySnapshot,
        private readonly GetDeadLetterQueueUseCase $getDeadLetters,
        private readonly GetBusStatusUseCase $getBusStatus,
    ) {}

    /** @return array<string, mixed> */
    public function buildProps(int $queueLimit = 50): array
    {
        return [
            'metrics'     => $this->getBusMetrics->execute()->toArray(),
            'queue'       => array_map(static fn ($e) => $e->toArray(), $this->getEventQueue->execute($queueLimit)),
            'topology'    => $this->getTopologySnapshot->execute()->toArray(),
            'deadLetters' => array_map(static fn ($e) => $e->toArray(), $this->getDeadLetters->execute()),
            'busStatus'   => $this->getBusStatus->execute(),
        ];
    }
}
