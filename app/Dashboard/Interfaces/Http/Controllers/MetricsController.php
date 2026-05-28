<?php

declare(strict_types=1);

namespace App\Dashboard\Interfaces\Http\Controllers;

use App\Dashboard\Application\UseCases\GetConfiguredDailySeriesUseCase;
use App\Dashboard\Application\UseCases\GetDashboardMetricCatalogUseCase;
use App\Dashboard\Application\UseCases\GetDynamicMetricSeriesUseCase;
use App\Dashboard\Application\UseCases\GetEventFlowDiagramDataUseCase;
use App\Dashboard\Application\UseCases\GetGlobalMetricsUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class MetricsController
{
    public function __construct(
        private readonly GetGlobalMetricsUseCase         $getGlobalMetrics,
        private readonly GetEventFlowDiagramDataUseCase  $getFlowDiagram,
        private readonly GetConfiguredDailySeriesUseCase $configuredDailySeries,
        private readonly GetDashboardMetricCatalogUseCase $metricCatalog,
        private readonly GetDynamicMetricSeriesUseCase   $dynamicSeries,
    ) {}

    public function global(): JsonResponse
    {
        return response()->json($this->getGlobalMetrics->execute()->toArray());
    }

    /** Configurable chart list + generic event contract metadata for integrators. */
    public function catalog(): JsonResponse
    {
        return response()->json([
            'metrics'        => $this->metricCatalog->execute(),
            'event_envelope' => config('dashboard.event_envelope_contract'),
        ]);
    }

    public function metricSeries(Request $request, string $metricId): JsonResponse
    {
        $days = $request->query('days');
        $days = $days !== null && $days !== '' ? (int) $days : null;

        try {
            return response()->json($this->dynamicSeries->execute($metricId, $days));
        } catch (InvalidArgumentException) {
            return response()->json(['message' => 'Metric not found or disabled'], 404);
        }
    }

    public function flowDiagram(): JsonResponse
    {
        return response()->json($this->getFlowDiagram->execute());
    }

    /** Daily sum from dashboard.daily_series config (declarative; no domain coupling). */
    public function configuredDailySeries(Request $request): JsonResponse
    {
        $days = max(1, min(90, (int) $request->query('days', 14)));

        return response()->json([
            'data' => $this->configuredDailySeries->execute($days),
            'days' => $days,
        ]);
    }
}
