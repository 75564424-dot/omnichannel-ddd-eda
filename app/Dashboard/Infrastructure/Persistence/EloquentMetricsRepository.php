<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Persistence;

use App\Dashboard\Domain\Repositories\MetricsRepositoryInterface;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Support\Facades\DB;

/**
 * Legacy retail KPI repository — backed by observability_metrics for compatibility.
 */
final class EloquentMetricsRepository implements MetricsRepositoryInterface
{
    private const SCOPE = 'system';

    /** @var list<string> */
    private static array $keys = ['stock_total', 'ventas_recientes', 'ordenes_activas', 'skus_criticos'];

    public function __construct(
        private readonly InstanceTenantContextInterface $instanceTenant,
    ) {}

    public function getValue(string $key): int
    {
        $value = DB::table('observability_metrics')
            ->where('metric_scope', self::SCOPE)
            ->where('metric_key', $key)
            ->orderByDesc('recorded_at')
            ->value('metric_value');

        return (int) ($value ?? 0);
    }

    public function increment(string $key, int $by = 1): void
    {
        $current = $this->getValue($key);
        $this->set($key, max(0, $current + $by));
    }

    public function decrement(string $key, int $by = 1): void
    {
        $current = $this->getValue($key);
        $this->set($key, max(0, $current - $by));
    }

    public function set(string $key, int $value): void
    {
        DB::table('observability_metrics')->insert([
            'tenant_id'     => $this->instanceTenant->tenantId(),
            'metric_scope'  => self::SCOPE,
            'metric_key'    => $key,
            'metric_value'  => $value,
            'dimensions'    => null,
            'recorded_at'   => now(),
        ]);
    }

    public function getLastUpdated(string $key): string
    {
        $at = DB::table('observability_metrics')
            ->where('metric_scope', self::SCOPE)
            ->where('metric_key', $key)
            ->orderByDesc('recorded_at')
            ->value('recorded_at');

        return $at !== null
            ? now()->parse($at)->timezone(config('app.timezone'))->toIso8601String()
            : now()->timezone(config('app.timezone'))->toIso8601String();
    }
}
