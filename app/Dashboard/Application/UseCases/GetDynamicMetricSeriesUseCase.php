<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Control\Application\Services\ClientDashboardMetricsCatalogService;
use App\Dashboard\Application\Services\DynamicMetricSeriesBuilder;
use InvalidArgumentException;

/**
 * Resolves chart payloads from dashboard_config.json metric definitions (no module-specific code paths).
 */
final class GetDynamicMetricSeriesUseCase
{
    public function __construct(
        private readonly ClientDashboardMetricsCatalogService $clientMetrics,
        private readonly DynamicMetricSeriesBuilder $seriesBuilder,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(string $metricId, ?int $days = null): array
    {
        $clientSeries = $this->clientMetrics->buildSeries($metricId, $days);
        if ($clientSeries !== null) {
            return $clientSeries;
        }

        $spec = $this->findSpec($metricId);
        if ($spec === null) {
            throw new InvalidArgumentException("Unknown or disabled metric: {$metricId}");
        }

        $days = max(1, min(90, $days ?? (int) ($spec['days_default'] ?? 14)));

        return $this->seriesBuilder->buildFromSpec($spec, $days);
    }

    /** @return array<string, mixed>|null */
    private function findSpec(string $metricId): ?array
    {
        foreach (config('dashboard.dynamic_metrics', []) as $spec) {
            if (is_array($spec) && ($spec['id'] ?? '') === $metricId && ! empty($spec['enabled'])) {
                return $spec;
            }
        }

        return null;
    }
}
