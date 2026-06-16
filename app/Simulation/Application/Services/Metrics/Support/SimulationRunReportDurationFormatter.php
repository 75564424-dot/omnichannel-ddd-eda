<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Metrics\Support;

final class SimulationRunReportDurationFormatter
{
    public function format(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds}s";
        }
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;

        return $s > 0 ? "{$m}m {$s}s" : "{$m}m";
    }
}
