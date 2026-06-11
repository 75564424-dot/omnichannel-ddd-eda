<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Contracts;

/**
 * ACL toward Control BC client metrics catalog (tenant-scoped chart definitions).
 */
interface ClientDashboardMetricsPortInterface
{
    public function hasConfiguredModules(): bool;

    /**
     * @return list<array{id: string, name: string, type: string, chart: string}>
     */
    public function catalogEntries(): array;

    /**
     * @return array<string, mixed>|null
     */
    public function buildSeries(string $metricId, ?int $days): ?array;
}
