<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Metrics\Support;

use App\Control\Infrastructure\Models\SimulationRunModel;

final class SimulationRunReportThroughputMapper
{
    /**
     * @param array{avg_interval_ms: int, max_interval_ms: int} $timing
     *
     * @return array<string, mixed>
     */
    public function map(
        SimulationRunModel $run,
        int $durationSec,
        int $published,
        array $timing,
    ): array {
        $achievedPerMinute = round(($published / $durationSec) * 60, 2);
        $targetIntervalMs = (int) round(60_000 / max(1, $run->events_per_minute));

        return [
            'target_events_per_minute'   => $run->events_per_minute,
            'achieved_events_per_minute' => $achievedPerMinute,
            'target_interval_ms'         => $targetIntervalMs,
            'avg_actual_interval_ms'     => $timing['avg_interval_ms'],
            'max_interval_ms'            => $timing['max_interval_ms'],
            'interval_drift_ms'          => $timing['avg_interval_ms'] - $targetIntervalMs,
        ];
    }
}
