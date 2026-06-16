<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Adapters;

use App\Control\Application\Services\ClientDashboardMetricsCatalogService;
use App\Dashboard\Application\Contracts\ClientDashboardMetricsPortInterface;

final class ControlClientDashboardMetricsAdapter implements ClientDashboardMetricsPortInterface
{
    public function __construct(
        private readonly ClientDashboardMetricsCatalogService $catalog,
    ) {}

    public function hasConfiguredModules(): bool
    {
        return $this->catalog->hasConfiguredModules();
    }

    public function catalogEntries(): array
    {
        return $this->catalog->catalogEntries();
    }

    public function buildSeries(string $metricId, ?int $days): ?array
    {
        return $this->catalog->buildSeries($metricId, $days);
    }
}
