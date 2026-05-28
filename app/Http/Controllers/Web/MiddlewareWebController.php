<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Middleware\Application\UseCases\GetBusMetricsUseCase;
use App\Middleware\Application\UseCases\GetBusStatusUseCase;
use App\Middleware\Application\UseCases\GetDeadLetterQueueUseCase;
use App\Middleware\Application\UseCases\GetEventQueueUseCase;
use App\Middleware\Application\UseCases\GetTopologySnapshotUseCase;
use Inertia\Inertia;
use Inertia\Response;

final class MiddlewareWebController
{
    public function __construct(
        private readonly GetBusMetricsUseCase $getBusMetrics,
        private readonly GetEventQueueUseCase $getEventQueue,
        private readonly GetTopologySnapshotUseCase $getTopologySnapshot,
        private readonly GetDeadLetterQueueUseCase $getDeadLetters,
        private readonly GetBusStatusUseCase $getBusStatus,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Middleware/Index', [
            'metrics'     => $this->getBusMetrics->execute()->toArray(),
            'queue'       => array_map(static fn ($e) => $e->toArray(), $this->getEventQueue->execute(50)),
            'topology'    => $this->getTopologySnapshot->execute()->toArray(),
            'deadLetters' => array_map(static fn ($e) => $e->toArray(), $this->getDeadLetters->execute()),
            'busStatus'   => $this->getBusStatus->execute(),
        ]);
    }
}
