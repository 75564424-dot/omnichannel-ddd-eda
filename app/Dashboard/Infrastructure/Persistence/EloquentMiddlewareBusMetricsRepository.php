<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Persistence;

use App\Dashboard\Domain\ReadModels\MiddlewareBusMetrics;
use App\Dashboard\Domain\Repositories\MiddlewareBusMetricsRepositoryInterface;
use App\Dashboard\Domain\ValueObjects\StreamStatus;
use App\Dashboard\Infrastructure\Models\MiddlewareBusMetricsModel;
use App\Shared\Persistence\BusStatusMetricMapper;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;

final class EloquentMiddlewareBusMetricsRepository implements MiddlewareBusMetricsRepositoryInterface
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceTenant,
    ) {}

    private const SCOPE = 'bus';

    private const SOURCE = 'dashboard';

    public function saveSnapshot(int $latencyMs, int $eps, int $queueSize, string $streamStatus): void
    {
        $recordedAt = now();
        $dimensions = ['source' => self::SOURCE, 'stream_status' => $streamStatus];

        $rows = [
            ['metric_key' => 'latency_ms', 'metric_value' => $latencyMs],
            ['metric_key' => 'events_per_second', 'metric_value' => $eps],
            ['metric_key' => 'queue_size', 'metric_value' => $queueSize],
            ['metric_key' => 'stream_status', 'metric_value' => BusStatusMetricMapper::toNumeric($streamStatus), 'dimensions' => $dimensions],
        ];

        foreach ($rows as $row) {
            MiddlewareBusMetricsModel::create([
                'tenant_id'     => $this->instanceTenant->tenantId(),
                'metric_scope'  => self::SCOPE,
                'metric_key'    => $row['metric_key'],
                'metric_value'  => $row['metric_value'],
                'dimensions'    => $row['dimensions'] ?? $dimensions,
                'recorded_at'   => $recordedAt,
            ]);
        }
    }

    public function getLatest(): ?MiddlewareBusMetrics
    {
        $latestAt = MiddlewareBusMetricsModel::query()
            ->where('metric_scope', self::SCOPE)
            ->where('dimensions->source', self::SOURCE)
            ->max('recorded_at');

        if ($latestAt === null) {
            return null;
        }

        $metrics = MiddlewareBusMetricsModel::query()
            ->where('metric_scope', self::SCOPE)
            ->where('recorded_at', $latestAt)
            ->where('dimensions->source', self::SOURCE)
            ->get()
            ->keyBy('metric_key');

        if ($metrics->isEmpty()) {
            return null;
        }

        $statusRow = $metrics->get('stream_status');
        $streamStatus = ($statusRow?->dimensions['stream_status'] ?? null)
            ?: BusStatusMetricMapper::fromNumeric((float) ($statusRow?->metric_value ?? 0));

        return new MiddlewareBusMetrics(
            latencyMs:         (int) ($metrics->get('latency_ms')?->metric_value ?? 0),
            processingRateEps: (int) ($metrics->get('events_per_second')?->metric_value ?? 0),
            queueSize:         (int) ($metrics->get('queue_size')?->metric_value ?? 0),
            streamStatus:      new StreamStatus($streamStatus),
            recordedAt:        $latestAt instanceof \DateTimeInterface
                ? $latestAt->format('Y-m-d H:i:s')
                : (string) $latestAt,
        );
    }
}
