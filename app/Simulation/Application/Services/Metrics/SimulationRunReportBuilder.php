<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Metrics;

use App\Control\Application\Services\Tenants\TenantPresentationService;
use App\Control\Infrastructure\Models\SimulationRunModel;
use Carbon\CarbonInterface;

final class SimulationRunReportBuilder
{
    public function __construct(
        private readonly SimulationQueueMetricsAnalyzer $queueMetrics,
        private readonly TenantPresentationService $tenantPresentation,
    ) {}

    /**
     * @param list<string> $eventIds
     * @param array<string, mixed> $baselineBefore
     * @param array<string, mixed> $baselineAfter
     *
     * @return array<string, mixed>
     */
    public function buildReport(
        SimulationRunModel $run,
        array $eventIds,
        ?CarbonInterface $startedAt,
        ?CarbonInterface $finishedAt,
        array $baselineBefore,
        array $baselineAfter,
    ): array {
        $queueStats = $this->queueMetrics->queueStatsForEvents($eventIds);
        $timing = $this->queueMetrics->interEventTiming($eventIds, $run->events_per_minute);

        $planned = max(1, (int) $run->planned_total);
        $published = (int) $run->published;
        $durationSec = ($startedAt && $finishedAt)
            ? max(1, $finishedAt->diffInSeconds($startedAt))
            : max(1, $run->duration_minutes * 60);

        $achievedPerMinute = round(($published / $durationSec) * 60, 2);
        $targetIntervalMs = (int) round(60_000 / max(1, $run->events_per_minute));

        $failed = $queueStats['failed'] + $queueStats['dead_lettered'];
        $processed = $queueStats['processed'];
        $denominator = max(1, $published);

        return [
            'summary' => [
                'tenant_name'       => $run->tenant?->name ?? '—',
                'tenant_slug'       => $run->tenant?->slug ?? '—',
                'fixture_slug'      => $run->fixture_slug,
                'status'            => $run->status,
                'started_at'        => $startedAt?->toDateTimeString(),
                'finished_at'       => $finishedAt?->toDateTimeString(),
                'duration_seconds'  => $durationSec,
                'duration_human'    => $this->formatDuration($durationSec),
                'planned_total'     => $planned,
                'published'         => $published,
                'publish_rate_pct'  => round(($published / $planned) * 100, 1),
                'queue_matches'     => (int) $run->queue_matches,
            ],
            'throughput' => [
                'target_events_per_minute'   => $run->events_per_minute,
                'achieved_events_per_minute' => $achievedPerMinute,
                'target_interval_ms'         => $targetIntervalMs,
                'avg_actual_interval_ms'     => $timing['avg_interval_ms'],
                'max_interval_ms'            => $timing['max_interval_ms'],
                'interval_drift_ms'          => $timing['avg_interval_ms'] - $targetIntervalMs,
            ],
            'latency' => [
                'avg_processing_ms' => $queueStats['avg_processing_ms'],
                'p95_processing_ms' => $queueStats['p95_processing_ms'],
                'max_processing_ms' => $queueStats['max_processing_ms'],
                'bus_latency_ms_after' => $baselineAfter['latency_ms'] ?? 0,
            ],
            'reliability' => [
                'error_rate_percent'   => round(($failed / $denominator) * 100, 2),
                'success_rate_percent' => round(($processed / $denominator) * 100, 2),
                'failed_count'         => $failed,
                'processed_count'      => $processed,
                'pending_count'        => $queueStats['pending'],
                'dead_lettered_count'  => $queueStats['dead_lettered'],
            ],
            'resources' => [
                'peak_memory_mb_run' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'queue_depth_before' => $baselineBefore['queue_pending'] ?? 0,
                'queue_depth_after'  => $baselineAfter['queue_pending'] ?? 0,
                'dead_letters_before'=> $baselineBefore['dead_letters'] ?? 0,
                'dead_letters_after' => $baselineAfter['dead_letters'] ?? 0,
                'bus_status_before'    => $baselineBefore['bus_status'] ?? '—',
                'bus_status_after'     => $baselineAfter['bus_status'] ?? '—',
            ],
            'consumption' => $this->tenantPresentation->consumptionForTenant($run->tenant_id),
        ];
    }

    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds}s";
        }
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;

        return $s > 0 ? "{$m}m {$s}s" : "{$m}m";
    }
}
