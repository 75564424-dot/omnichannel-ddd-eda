<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Progress;


use App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore;
/**
 * Reports simulation progress: handoff file on every tick, HTTP to control plane throttled.
 */
final class SimulationProgressReporter
{
    private int $lastHttpReported = 0;

    private float $lastHttpReportAt = 0.0;

    public function __construct(
        private readonly SimulationRunControlPlaneClient $client,
        private readonly SimulationRunHandoffStore $handoffStore,
        private readonly int $httpMinIntervalSeconds = 2,
        private readonly int $httpStepEvents = 2,
    ) {}

    public function forRun(string $runId, int $plannedTotal): callable
    {
        $this->lastHttpReported = 0;
        $this->lastHttpReportAt = 0.0;

        return function (int $current, int $total) use ($runId, $plannedTotal): void {
            $effectiveTotal = $total > 0 ? $total : $plannedTotal;
            $this->handoffStore->updateProgress($runId, $current, $effectiveTotal);

            $now = microtime(true);
            $shouldHttp = $current <= 1
                || $current >= $plannedTotal
                || $current - $this->lastHttpReported >= $this->httpStepEvents
                || ($now - $this->lastHttpReportAt) >= $this->httpMinIntervalSeconds;

            if (! $shouldHttp) {
                return;
            }

            $this->client->reportProgress($runId, $current, $effectiveTotal);
            $this->lastHttpReported = $current;
            $this->lastHttpReportAt = $now;
        };
    }
}
