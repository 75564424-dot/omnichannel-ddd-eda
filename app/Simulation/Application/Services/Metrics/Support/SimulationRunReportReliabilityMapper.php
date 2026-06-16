<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Metrics\Support;

final class SimulationRunReportReliabilityMapper
{
    /**
     * @param array<string, int|float> $queueStats
     *
     * @return array<string, mixed>
     */
    public function mapLatency(array $queueStats, array $baselineAfter): array
    {
        return [
            'avg_processing_ms' => $queueStats['avg_processing_ms'],
            'p95_processing_ms' => $queueStats['p95_processing_ms'],
            'max_processing_ms' => $queueStats['max_processing_ms'],
            'bus_latency_ms_after' => $baselineAfter['latency_ms'] ?? 0,
        ];
    }

    /**
     * @param array<string, int|float> $queueStats
     *
     * @return array<string, mixed>
     */
    public function mapReliability(array $queueStats, int $published): array
    {
        $failed = $queueStats['failed'] + $queueStats['dead_lettered'];
        $processed = $queueStats['processed'];
        $denominator = max(1, $published);

        return [
            'error_rate_percent'   => round(($failed / $denominator) * 100, 2),
            'success_rate_percent' => round(($processed / $denominator) * 100, 2),
            'failed_count'         => $failed,
            'processed_count'      => $processed,
            'pending_count'        => $queueStats['pending'],
            'dead_lettered_count'  => $queueStats['dead_lettered'],
        ];
    }

    /**
     * @param array<string, mixed> $baselineBefore
     * @param array<string, mixed> $baselineAfter
     *
     * @return array<string, mixed>
     */
    public function mapResources(array $baselineBefore, array $baselineAfter): array
    {
        return [
            'peak_memory_mb_run' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'queue_depth_before' => $baselineBefore['queue_pending'] ?? 0,
            'queue_depth_after'  => $baselineAfter['queue_pending'] ?? 0,
            'dead_letters_before'=> $baselineBefore['dead_letters'] ?? 0,
            'dead_letters_after' => $baselineAfter['dead_letters'] ?? 0,
            'bus_status_before'    => $baselineBefore['bus_status'] ?? '—',
            'bus_status_after'     => $baselineAfter['bus_status'] ?? '—',
        ];
    }
}
