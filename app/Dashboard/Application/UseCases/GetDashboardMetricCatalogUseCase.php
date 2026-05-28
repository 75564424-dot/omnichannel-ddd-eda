<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Control\Application\Services\ClientDashboardMetricsCatalogService;

/**
 * Enabled chart metrics for the dashboard product (dropdown + lazy-loaded series).
 *
 * @return list<array{id: string, name: string, type: string, chart: string}>
 */
final class GetDashboardMetricCatalogUseCase
{
    public function __construct(
        private readonly ClientDashboardMetricsCatalogService $clientMetrics,
    ) {}

    public function execute(): array
    {
        if ($this->clientMetrics->hasConfiguredModules()) {
            return $this->clientMetrics->catalogEntries();
        }

        $out = [];
        foreach (config('dashboard.dynamic_metrics', []) as $spec) {
            if (! is_array($spec) || empty($spec['enabled'])) {
                continue;
            }
            $id = (string) ($spec['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $out[] = [
                'id'    => $id,
                'name'  => (string) ($spec['name'] ?? $id),
                'type'  => (string) ($spec['type'] ?? 'chart'),
                'chart' => (string) ($spec['chart'] ?? 'bar'),
            ];
        }

        return $out;
    }
}
