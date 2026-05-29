<?php

declare(strict_types=1);

namespace App\Observability\Application\Services\Prometheus;

final class PrometheusTextRenderer
{
    public function render(PrometheusGaugeSnapshot $gauges, string $clientSlug): string
    {
        $labels = sprintf('{client="%s"}', $this->escapeLabel($clientSlug));

        $lines = [
            '# HELP bus_events_published_total Events published to message_queue in the last hour.',
            '# TYPE bus_events_published_total gauge',
            'bus_events_published_total'.$labels.' '.$gauges->publishedTotal,
            '',
            '# HELP bus_processing_latency_ms Average queue processing latency in milliseconds.',
            '# TYPE bus_processing_latency_ms gauge',
            'bus_processing_latency_ms'.$labels.' '.$gauges->processingLatencyMs,
            '',
            '# HELP bus_error_rate_percent Error rate percentage in the last metrics window.',
            '# TYPE bus_error_rate_percent gauge',
            'bus_error_rate_percent'.$labels.' '.$gauges->errorRatePercent,
            '',
            '# HELP bus_stream_status Bus stream status (0=STOPPED,1=ACTIVE,2=DEGRADED,3=HI-LOAD).',
            '# TYPE bus_stream_status gauge',
            'bus_stream_status'.$labels.' '.$gauges->streamStatus,
            '',
            '# HELP bus_dlq_unresolved Unresolved dead-letter queue entries.',
            '# TYPE bus_dlq_unresolved gauge',
            'bus_dlq_unresolved'.$labels.' '.$gauges->dlqUnresolved,
            '',
            '# HELP feed_projection_lag_ms Average feed projection lag (received_at - occurred_at) in ms.',
            '# TYPE feed_projection_lag_ms gauge',
            'feed_projection_lag_ms'.$labels.' '.$gauges->feedProjectionLagMs,
            '',
            '# HELP sse_stream_connections_active Active dashboard SSE stream connections.',
            '# TYPE sse_stream_connections_active gauge',
            'sse_stream_connections_active'.$labels.' '.$gauges->sseActiveConnections,
            '',
            '# HELP database_usage_percent Estimated database storage usage vs configured limit.',
            '# TYPE database_usage_percent gauge',
            'database_usage_percent'.$labels.' '.$gauges->databaseUsagePercent,
            '',
            '# HELP queue_jobs_pending Pending Laravel queue jobs across monitored queues.',
            '# TYPE queue_jobs_pending gauge',
            'queue_jobs_pending'.$labels.' '.$gauges->queueJobsPending,
            '',
            '# HELP canary_last_success_age_seconds Seconds since last successful canary publish (-1 if never).',
            '# TYPE canary_last_success_age_seconds gauge',
            'canary_last_success_age_seconds'.$labels.' '.$gauges->canaryLastSuccessAgeSeconds,
        ];

        return implode("\n", $lines)."\n";
    }

    private function escapeLabel(string $value): string
    {
        return str_replace(['\\', '"', "\n"], ['\\\\', '\\"', '\\n'], $value);
    }
}
