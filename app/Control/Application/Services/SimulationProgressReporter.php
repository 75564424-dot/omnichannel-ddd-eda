<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

/**
 * Throttles HTTP progress callbacks from client silo workers to the control plane.
 */
final class SimulationProgressReporter
{
    private int $lastReported = 0;

    private float $lastReportAt = 0.0;

    public function __construct(
        private readonly SimulationRunControlPlaneClient $client,
        private readonly int $minIntervalSeconds = 2,
        private readonly int $stepEvents = 3,
    ) {}

    public function forRun(string $runId, int $plannedTotal): callable
    {
        $this->lastReported = 0;
        $this->lastReportAt = 0.0;

        return function (int $current, int $total) use ($runId, $plannedTotal): void {
            $now = microtime(true);
            $shouldReport = $current <= 1
                || $current >= $plannedTotal
                || $current - $this->lastReported >= $this->stepEvents
                || ($now - $this->lastReportAt) >= $this->minIntervalSeconds;

            if (! $shouldReport) {
                return;
            }

            $this->client->reportProgress($runId, $current, $total > 0 ? $total : $plannedTotal);
            $this->lastReported = $current;
            $this->lastReportAt = $now;
        };
    }
}
