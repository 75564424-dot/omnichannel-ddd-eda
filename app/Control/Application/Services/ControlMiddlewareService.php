<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Dashboard\Application\UseCases\GetSystemNodeStatusUseCase;
use App\Middleware\Application\UseCases\GetBusMetricsUseCase;
use App\Middleware\Application\UseCases\GetDeadLetterQueueUseCase;
use App\Middleware\Application\UseCases\GetEventQueueUseCase;

final class ControlMiddlewareService
{
    public function __construct(
        private readonly GetBusMetricsUseCase $busMetrics,
        private readonly GetEventQueueUseCase $eventQueue,
        private readonly GetDeadLetterQueueUseCase $deadLetters,
        private readonly GetSystemNodeStatusUseCase $nodeStatus,
    ) {}

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        $metrics = $this->busMetrics->execute()->toArray();
        $nodes = $this->nodeStatus->execute()->toArray();

        return [
            'broker' => [
                'driver'  => (string) config('eventbus.driver', 'laravel'),
                'status'  => $metrics['bus_status'] ?? 'UNKNOWN',
            ],
            'metrics' => $metrics,
            'queues' => [
                'connection' => (string) config('queue.default'),
                'depth'      => $this->eventQueue->countAll(),
                'recent'     => array_map(
                    static fn ($e) => $e->toArray(),
                    $this->eventQueue->execute(15),
                ),
            ],
            'dead_letters' => array_map(
                static fn ($e) => $e->toArray(),
                $this->deadLetters->execute(),
            ),
            'nodes' => $nodes['status_by_node'] ?? [],
            'nodes_updated_at' => $nodes['last_updated'] ?? null,
        ];
    }
}
