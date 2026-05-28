<?php

declare(strict_types=1);

namespace App\Observability\Application\Services;

use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Records SLI snapshots into observability_metrics (Plan_Observabilidad).
 */
final class SliMetricsRecorder
{
    private const SCOPE = 'sli';

    public function __construct(
        private readonly InstanceTenantContextInterface $instanceTenant,
    ) {}

    public function record(string $metricKey, float $value, array $dimensions = []): void
    {
        if (! Schema::hasTable('observability_metrics')) {
            return;
        }

        DB::table('observability_metrics')->insert([
            'tenant_id'     => $this->instanceTenant->tenantId(),
            'metric_scope'  => self::SCOPE,
            'metric_key'    => $metricKey,
            'metric_value'  => $value,
            'dimensions'    => json_encode(array_merge(['source' => 'observability_sli'], $dimensions), JSON_THROW_ON_ERROR),
            'recorded_at'   => now(),
        ]);
    }
}
