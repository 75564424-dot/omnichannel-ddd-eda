<?php

declare(strict_types=1);

namespace App\Middleware\Interfaces\Http\Controllers;

use App\Middleware\Application\Services\BusMetricsService;
use App\Middleware\Application\UseCases\GetBusMetricsUseCase;
use Illuminate\Http\JsonResponse;

/**
 * Exposes computed Event Bus metrics.
 * Returns the latest snapshot and optionally triggers a fresh computation.
 */
final class BusMetricsController
{
    public function __construct(
        private readonly GetBusMetricsUseCase $getBusMetrics,
        private readonly BusMetricsService    $metricsService,
    ) {}

    /**
     * GET /api/middleware/metrics
     * Returns the most recent metrics snapshot.
     */
    public function index(): JsonResponse
    {
        $metrics = $this->getBusMetrics->execute();

        return response()->json([
            'success' => true,
            'data'    => $metrics->toArray(),
        ]);
    }

    /**
     * POST /api/middleware/metrics/refresh
     * Recomputes and stores a fresh metrics snapshot.
     */
    public function refresh(): JsonResponse
    {
        $snapshot = $this->metricsService->computeAndSnapshot();

        return response()->json([
            'success' => true,
            'data'    => [
                'latency_ms'        => $snapshot->latencyMs->value(),
                'events_per_second' => $snapshot->eventsPerSecond->value(),
                'error_rate'        => $snapshot->errorRate->value(),
                'dead_letters'      => $snapshot->deadLettersCount,
                'bus_status'        => $snapshot->busStatus->value(),
                'recorded_at'       => $snapshot->recordedAt,
            ],
        ]);
    }
}
