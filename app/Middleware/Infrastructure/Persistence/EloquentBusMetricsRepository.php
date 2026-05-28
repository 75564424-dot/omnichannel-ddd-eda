<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Persistence;

use App\Middleware\Domain\ReadModels\BusMetricsSnapshot;
use App\Middleware\Domain\Repositories\BusMetricsRepositoryInterface;
use App\Middleware\Domain\ValueObjects\BusStatus;
use App\Middleware\Domain\ValueObjects\ErrorRate;
use App\Middleware\Domain\ValueObjects\LatencyMs;
use App\Middleware\Domain\ValueObjects\ThroughputEps;
use App\Middleware\Infrastructure\Models\BusMetricsModel;
use App\Shared\Persistence\BusStatusMetricMapper;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;

final class EloquentBusMetricsRepository implements BusMetricsRepositoryInterface
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceTenant,
    ) {}

    private const SCOPE = 'bus';

    private const SOURCE = 'middleware';

    public function saveSnapshot(BusMetricsSnapshot $snapshot): void
    {
        $recordedAt = $snapshot->recordedAt;
        $dimensions = ['source' => self::SOURCE];

        $rows = [
            ['metric_key' => 'latency_ms', 'metric_value' => $snapshot->latencyMs->value()],
            ['metric_key' => 'events_per_second', 'metric_value' => $snapshot->eventsPerSecond->value()],
            ['metric_key' => 'error_rate', 'metric_value' => $snapshot->errorRate->value()],
            ['metric_key' => 'dead_letters_count', 'metric_value' => $snapshot->deadLettersCount],
            ['metric_key' => 'stream_status', 'metric_value' => BusStatusMetricMapper::toNumeric($snapshot->busStatus->value()), 'dimensions' => array_merge($dimensions, ['bus_status' => $snapshot->busStatus->value()])],
        ];

        foreach ($rows as $row) {
            BusMetricsModel::create([
                'tenant_id'     => $this->instanceTenant->tenantId(),
                'metric_scope'  => self::SCOPE,
                'metric_key'    => $row['metric_key'],
                'metric_value'  => $row['metric_value'],
                'dimensions'    => $row['dimensions'] ?? $dimensions,
                'recorded_at'   => $recordedAt,
            ]);
        }
    }

    public function getLatest(): ?BusMetricsSnapshot
    {
        $latestAt = BusMetricsModel::query()
            ->where('metric_scope', self::SCOPE)
            ->where('dimensions->source', self::SOURCE)
            ->max('recorded_at');

        if ($latestAt === null) {
            return null;
        }

        $metrics = BusMetricsModel::query()
            ->where('metric_scope', self::SCOPE)
            ->where('recorded_at', $latestAt)
            ->where('dimensions->source', self::SOURCE)
            ->get()
            ->keyBy('metric_key');

        if ($metrics->isEmpty()) {
            return null;
        }

        $statusRow = $metrics->get('stream_status');
        $busStatus = $statusRow !== null
            ? BusStatusMetricMapper::fromNumeric((float) $statusRow->metric_value)
            : 'STOPPED';

        if ($statusRow?->dimensions['bus_status'] ?? null) {
            $busStatus = (string) $statusRow->dimensions['bus_status'];
        }

        return new BusMetricsSnapshot(
            latencyMs:        LatencyMs::of((int) ($metrics->get('latency_ms')?->metric_value ?? 0)),
            eventsPerSecond:  ThroughputEps::of((int) ($metrics->get('events_per_second')?->metric_value ?? 0)),
            errorRate:        new ErrorRate((float) ($metrics->get('error_rate')?->metric_value ?? 0)),
            deadLettersCount: (int) ($metrics->get('dead_letters_count')?->metric_value ?? 0),
            busStatus:        new BusStatus($busStatus),
            recordedAt:       (string) $latestAt,
        );
    }
}
