<?php

declare(strict_types=1);

namespace App\Dashboard\Interfaces\Http\Controllers\Web;

use App\Dashboard\Application\UseCases\GetDashboardMetricCatalogUseCase;
use App\Dashboard\Application\UseCases\GetDynamicMetricSeriesUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class ClientDashboardMetricsWebController
{
    public function __construct(
        private readonly GetDashboardMetricCatalogUseCase $metricCatalog,
        private readonly GetDynamicMetricSeriesUseCase $dynamicSeries,
    ) {}

    public function catalog(): JsonResponse
    {
        return response()->json([
            'metrics'        => $this->metricCatalog->execute(),
            'event_envelope' => config('dashboard.event_envelope_contract'),
        ]);
    }

    public function series(Request $request, string $metric): JsonResponse
    {
        $metricId = rawurldecode($metric);
        $days = $request->query('days');
        $days = $days !== null && $days !== '' ? (int) $days : null;

        try {
            return response()->json($this->dynamicSeries->execute($metricId, $days));
        } catch (InvalidArgumentException) {
            return response()->json(['message' => 'Metric not found or disabled'], 404);
        }
    }
}
