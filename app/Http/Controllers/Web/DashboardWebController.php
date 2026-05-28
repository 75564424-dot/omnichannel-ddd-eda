<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Dashboard\Application\UseCases\GetDashboardMetricCatalogUseCase;
use App\Dashboard\Application\UseCases\GetDynamicMetricSeriesUseCase;
use App\Dashboard\Application\UseCases\GetGlobalMetricsUseCase;
use App\Dashboard\Application\UseCases\GetMiddlewareBusMetricsUseCase;
use App\Dashboard\Application\UseCases\GetModulesCatalogUseCase;
use App\Dashboard\Application\UseCases\GetRecentEventFeedUseCase;
use App\Dashboard\Application\UseCases\GetSystemNodeStatusUseCase;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardWebController
{
    public function __construct(
        private readonly GetGlobalMetricsUseCase $getGlobalMetrics,
        private readonly GetRecentEventFeedUseCase $getEventFeed,
        private readonly GetSystemNodeStatusUseCase $getNodeStatus,
        private readonly GetMiddlewareBusMetricsUseCase $getMiddlewareMetrics,
        private readonly GetDashboardMetricCatalogUseCase $metricCatalog,
        private readonly GetDynamicMetricSeriesUseCase $dynamicSeries,
        private readonly GetModulesCatalogUseCase $modulesCatalog,
    ) {}

    public function index(): Response
    {
        $catalog = $this->metricCatalog->execute();
        $defaultMetricId = isset($catalog[0]['id']) ? (string) $catalog[0]['id'] : null;
        $initialSeries = [];

        if ($defaultMetricId !== null && $defaultMetricId !== '') {
            try {
                $initialSeries = $this->dynamicSeries->execute($defaultMetricId, 14);
            } catch (\InvalidArgumentException) {
                $initialSeries = [];
            }
        }

        return Inertia::render('Dashboard/Index', [
            'metrics'            => $this->getGlobalMetrics->execute()->toArray(),
            'metrics_catalog'    => $catalog,
            'initial_metric_id'  => $defaultMetricId,
            'initial_metric_series' => $initialSeries,
            'modules_catalog'    => $this->modulesCatalog->execute(),
            'event_envelope'     => config('dashboard.event_envelope_contract'),
            'feed'               => array_map(static fn ($e) => $e->toArray(), $this->getEventFeed->execute()),
            'nodes'              => $this->getNodeStatus->execute()->toArray(),
            'middlewareMetrics'  => $this->getMiddlewareMetrics->execute()->toArray(),
            'system_module_rows' => config('dashboard.ui.system_module_rows', []),
        ]);
    }
}
