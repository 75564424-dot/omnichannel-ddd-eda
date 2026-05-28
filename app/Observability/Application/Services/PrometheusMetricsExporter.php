<?php

declare(strict_types=1);

namespace App\Observability\Application\Services;

use App\Dashboard\Infrastructure\Models\EventFeedEntryModel;
use App\Middleware\Application\Services\BusHealthService;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use App\Monitoring\Application\Services\CanaryPublishService;
use App\Monitoring\Application\Services\DatabaseCapacityChecker;
use App\Monitoring\Application\Services\QueueDepthChecker;
use App\Shared\Persistence\BusStatusMetricMapper;

/**
 * Exports Prometheus text format from live read models (Plan_Observabilidad + Plan_Monitoreo).
 */
final class PrometheusMetricsExporter
{
    public function __construct(
        private readonly QueueEntryRepositoryInterface $queue,
        private readonly DeadLetterRepositoryInterface $deadLetters,
        private readonly StreamConnectionTracker $streamConnections,
        private readonly BusHealthService $busHealth,
        private readonly DatabaseCapacityChecker $databaseCapacity,
        private readonly QueueDepthChecker $queueDepth,
        private readonly CanaryPublishService $canary,
    ) {}

    public function export(): string
    {
        $clientSlug = (string) config('platform.client_slug', 'default');
        $labels     = sprintf('{client="%s"}', $this->escapeLabel($clientSlug));

        $snapshot = $this->busHealth->getLatestSnapshot();

        $publishedTotal    = $this->queue->countTotal(3600);
        $processingLatency = $this->queue->computeAverageProcessingTimeMs();
        $dlqUnresolved     = $this->deadLetters->countUnresolved();
        $feedLagMs         = $this->computeFeedProjectionLagMs();
        $sseActive         = $this->streamConnections->activeCount();
        $errorRate         = $snapshot->errorRate->value();
        $streamStatus      = BusStatusMetricMapper::toNumeric($snapshot->busStatus->value());
        $dbUsagePercent    = $this->databaseCapacity->usagePercent();
        $queuePending      = $this->queueDepth->totalPending();
        $canaryAge         = $this->canary->lastSuccessAgeSeconds();

        $lines = [
            '# HELP bus_events_published_total Events published to message_queue in the last hour.',
            '# TYPE bus_events_published_total gauge',
            'bus_events_published_total'.$labels.' '.$publishedTotal,
            '',
            '# HELP bus_processing_latency_ms Average queue processing latency in milliseconds.',
            '# TYPE bus_processing_latency_ms gauge',
            'bus_processing_latency_ms'.$labels.' '.$processingLatency,
            '',
            '# HELP bus_error_rate_percent Error rate percentage in the last metrics window.',
            '# TYPE bus_error_rate_percent gauge',
            'bus_error_rate_percent'.$labels.' '.$errorRate,
            '',
            '# HELP bus_stream_status Bus stream status (0=STOPPED,1=ACTIVE,2=DEGRADED,3=HI-LOAD).',
            '# TYPE bus_stream_status gauge',
            'bus_stream_status'.$labels.' '.$streamStatus,
            '',
            '# HELP bus_dlq_unresolved Unresolved dead-letter queue entries.',
            '# TYPE bus_dlq_unresolved gauge',
            'bus_dlq_unresolved'.$labels.' '.$dlqUnresolved,
            '',
            '# HELP feed_projection_lag_ms Average feed projection lag (received_at - occurred_at) in ms.',
            '# TYPE feed_projection_lag_ms gauge',
            'feed_projection_lag_ms'.$labels.' '.$feedLagMs,
            '',
            '# HELP sse_stream_connections_active Active dashboard SSE stream connections.',
            '# TYPE sse_stream_connections_active gauge',
            'sse_stream_connections_active'.$labels.' '.$sseActive,
            '',
            '# HELP database_usage_percent Estimated database storage usage vs configured limit.',
            '# TYPE database_usage_percent gauge',
            'database_usage_percent'.$labels.' '.$dbUsagePercent,
            '',
            '# HELP queue_jobs_pending Pending Laravel queue jobs across monitored queues.',
            '# TYPE queue_jobs_pending gauge',
            'queue_jobs_pending'.$labels.' '.$queuePending,
            '',
            '# HELP canary_last_success_age_seconds Seconds since last successful canary publish (-1 if never).',
            '# TYPE canary_last_success_age_seconds gauge',
            'canary_last_success_age_seconds'.$labels.' '.$canaryAge,
        ];

        return implode("\n", $lines)."\n";
    }

    private function computeFeedProjectionLagMs(): int
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('event_feed_projections')) {
            return 0;
        }

        $entries = EventFeedEntryModel::orderByDesc('id')
            ->limit(100)
            ->get(['occurred_at', 'received_at']);

        if ($entries->isEmpty()) {
            return 0;
        }

        $totalMs = $entries->sum(function ($e) {
            return max(0, ($e->received_at->getTimestamp() - $e->occurred_at->getTimestamp()) * 1000);
        });

        return (int) round($totalMs / $entries->count());
    }

    private function escapeLabel(string $value): string
    {
        return str_replace(['\\', '"', "\n"], ['\\\\', '\\"', '\\n'], $value);
    }
}
