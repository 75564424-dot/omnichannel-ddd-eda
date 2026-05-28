<?php

declare(strict_types=1);

namespace App\Dashboard\Interfaces\Http\Controllers;

use App\Dashboard\Application\UseCases\GetConfiguredDailySeriesUseCase;
use App\Dashboard\Application\UseCases\GetGlobalMetricsUseCase;
use App\Dashboard\Application\UseCases\GetMiddlewareBusMetricsUseCase;
use App\Dashboard\Application\UseCases\GetRecentEventFeedUseCase;
use App\Dashboard\Application\UseCases\GetSystemNodeStatusUseCase;
use Illuminate\Http\JsonResponse;

/**
 * Aggregated snapshot endpoint: returns all dashboard data in one request.
 * Used by the frontend for the initial page load.
 */
final class DashboardController
{
    public function __construct(
        private readonly GetGlobalMetricsUseCase      $getGlobalMetrics,
        private readonly GetRecentEventFeedUseCase    $getRecentFeed,
        private readonly GetSystemNodeStatusUseCase   $getNodeStatus,
        private readonly GetMiddlewareBusMetricsUseCase $getBusMetrics,
        private readonly GetConfiguredDailySeriesUseCase $configuredDailySeries,
    ) {}

    public function snapshot(): JsonResponse
    {
        return response()->json([
            'metrics'          => $this->getGlobalMetrics->execute()->toArray(),
            'feed'             => array_map(fn ($e) => $e->toArray(), $this->getRecentFeed->execute(20)),
            'nodes'            => $this->getNodeStatus->execute()->toArray(),
            'bus'              => $this->getBusMetrics->execute()->toArray(),
            'primary_daily_series' => $this->configuredDailySeries->execute(14),
        ]);
    }
}
