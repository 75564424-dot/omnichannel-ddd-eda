<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Middleware\Application\Services\BusHealthService;
use App\Middleware\Infrastructure\Models\QueueEntryModel;
use App\Shared\Persistence\MessageQueueStatusMapper;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;

/**
 * Builds before/after snapshots and final report metrics for a simulation run.
 */
final class SimulationRunMetricsCollector
{
    public function __construct(
        private readonly BusHealthService $busHealth,
        private readonly TenantPresentationService $tenantPresentation,
    ) {}

    /** @return array<string, mixed> */
    public function captureEnvironmentBaseline(): array
    {
        $snapshot = $this->busHealth->getLatestSnapshot();

        return [
            'captured_at'        => now()->toDateTimeString(),
            'bus_status'         => $snapshot->busStatus->value(),
            'latency_ms'         => $snapshot->latencyMs->value(),
            'events_per_second'  => $snapshot->eventsPerSecond->value(),
            'error_rate_percent' => round($snapshot->errorRate->value(), 2),
            'dead_letters'       => $snapshot->deadLettersCount,
            'queue_pending'      => $this->countQueueByStatuses(['pending', 'PENDING', 'processing', 'PROCESSING']),
            'peak_memory_mb'     => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];
    }

    /**
     * @param list<string> $eventIds
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
        $queueStats = $this->queueStatsForEvents($eventIds);
        $timing     = $this->interEventTiming($eventIds, $run->events_per_minute);

        $planned   = max(1, (int) $run->planned_total);
        $published = (int) $run->published;
        $durationSec = ($startedAt && $finishedAt)
            ? max(1, $finishedAt->diffInSeconds($startedAt))
            : max(1, $run->duration_minutes * 60);

        $achievedPerMinute = round(($published / $durationSec) * 60, 2);
        $targetIntervalMs  = (int) round(60_000 / max(1, $run->events_per_minute));

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

    /** @return array<string, mixed> */
    public function presentationForListItem(SimulationRunModel $run): array
    {
        $planned = max(1, (int) $run->planned_total);
        $progress = min(100, (int) round(((int) $run->progress_current / $planned) * 100));

        return [
            'id'                => $run->id,
            'tenant_id'         => $run->tenant_id,
            'tenant_name'       => $run->tenant?->name ?? '—',
            'tenant_slug'       => $run->tenant?->slug ?? '—',
            'fixture_slug'      => $run->fixture_slug,
            'status'            => $run->status,
            'events_per_minute' => $run->events_per_minute,
            'duration_minutes'  => $run->duration_minutes,
            'planned_total'     => $run->planned_total,
            'published'         => $run->published,
            'progress_percent'  => $progress,
            'created_at'        => $run->created_at?->format('d/m/Y H:i:s'),
            'started_at'        => $run->started_at?->format('d/m/Y H:i:s'),
            'finished_at'       => $run->finished_at?->format('d/m/Y H:i:s'),
            'error_message'     => $run->error_message,
            'can_view_report'   => in_array($run->status, [
                SimulationRunModel::STATUS_COMPLETED,
                SimulationRunModel::STATUS_FAILED,
            ], true) && is_array($run->metrics) && $run->metrics !== [],
        ];
    }

    /** @return array<string, mixed> */
    public function presentationForRun(SimulationRunModel $run): array
    {
        $metrics = is_array($run->metrics) ? $run->metrics : [];

        return [
            'run' => [
                'id'                => $run->id,
                'status'            => $run->status,
                'tenant_id'         => $run->tenant_id,
                'tenant_name'       => $run->tenant?->name,
                'tenant_slug'       => $run->tenant?->slug,
                'fixture_slug'      => $run->fixture_slug,
                'events_per_minute' => $run->events_per_minute,
                'duration_minutes'  => $run->duration_minutes,
                'planned_total'     => $run->planned_total,
                'published'         => $run->published,
                'progress_current'  => $run->progress_current,
                'progress_percent'  => $run->planned_total > 0
                    ? min(100, (int) round(($run->progress_current / $run->planned_total) * 100))
                    : 0,
                'started_at'        => $run->started_at?->toDateTimeString(),
                'finished_at'       => $run->finished_at?->toDateTimeString(),
                'error_message'     => $run->error_message,
                'created_at'        => $run->created_at?->toDateTimeString(),
            ],
            'metrics' => $metrics,
        ];
    }

    /**
     * @param list<string> $eventIds
     *
     * @return array<string, int|float>
     */
    private function queueStatsForEvents(array $eventIds): array
    {
        if ($eventIds === [] || ! Schema::hasTable('message_queue')) {
            return [
                'processed'          => 0,
                'failed'             => 0,
                'pending'            => 0,
                'dead_lettered'      => 0,
                'avg_processing_ms'  => 0,
                'p95_processing_ms'  => 0,
                'max_processing_ms'  => 0,
            ];
        }

        $rows = QueueEntryModel::query()
            ->whereIn('event_uuid', $eventIds)
            ->get(['status', 'processing_time_ms']);

        $latencies = $rows->pluck('processing_time_ms')->filter(fn ($v) => $v !== null)->map(fn ($v) => (int) $v)->sort()->values();

        $processed = 0;
        $failed = 0;
        $pending = 0;
        $dead = 0;

        foreach ($rows as $row) {
            $normalized = MessageQueueStatusMapper::fromDb((string) $row->status);
            match ($normalized) {
                'PROCESADO', 'SUCCESS' => $processed++,
                'FALLIDO', 'FAILED' => $failed++,
                'dead_lettered' => $dead++,
                default => $pending++,
            };
        }

        return [
            'processed'         => $processed,
            'failed'            => $failed,
            'pending'           => $pending,
            'dead_lettered'     => $dead,
            'avg_processing_ms' => $latencies->isEmpty() ? 0 : (int) round($latencies->avg()),
            'p95_processing_ms' => $this->percentile($latencies->all(), 95),
            'max_processing_ms' => $latencies->isEmpty() ? 0 : (int) $latencies->max(),
        ];
    }

    /**
     * @param list<string> $eventIds
     *
     * @return array{avg_interval_ms: int, max_interval_ms: int}
     */
    private function interEventTiming(array $eventIds, int $targetPerMinute): array
    {
        if (count($eventIds) < 2 || ! Schema::hasTable('message_queue')) {
            $targetMs = (int) round(60_000 / max(1, $targetPerMinute));

            return ['avg_interval_ms' => $targetMs, 'max_interval_ms' => $targetMs];
        }

        $times = QueueEntryModel::query()
            ->whereIn('event_uuid', $eventIds)
            ->orderBy('published_at')
            ->pluck('published_at')
            ->map(fn ($t) => $t?->getTimestamp() ?? 0)
            ->filter(fn ($t) => $t > 0)
            ->values()
            ->all();

        $gaps = [];
        for ($i = 1, $iMax = count($times); $i < $iMax; $i++) {
            $gaps[] = ($times[$i] - $times[$i - 1]) * 1000;
        }

        if ($gaps === []) {
            $targetMs = (int) round(60_000 / max(1, $targetPerMinute));

            return ['avg_interval_ms' => $targetMs, 'max_interval_ms' => $targetMs];
        }

        return [
            'avg_interval_ms' => (int) round(array_sum($gaps) / count($gaps)),
            'max_interval_ms' => (int) max($gaps),
        ];
    }

    /** @param list<int> $values */
    private function percentile(array $values, int $percentile): int
    {
        if ($values === []) {
            return 0;
        }
        sort($values);
        $index = (int) ceil(($percentile / 100) * count($values)) - 1;

        return $values[max(0, min($index, count($values) - 1))];
    }

    /** @param list<string> $statuses */
    private function countQueueByStatuses(array $statuses): int
    {
        if (! Schema::hasTable('message_queue')) {
            return 0;
        }

        return (int) QueueEntryModel::query()->whereIn('status', $statuses)->count();
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
