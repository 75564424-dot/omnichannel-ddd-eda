<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Worker;


use App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore;
use App\Control\Infrastructure\Models\SimulationRunModel;

/**
 * Detects whether a client-silo simulation worker is still alive (log/handoff activity).
 */
final class SimulationRunWorkerMonitor
{
    public function __construct(
        private readonly SimulationRunHandoffStore $handoffStore,
        private readonly SimulationWorkerLauncher $workerLauncher,
    ) {}

    public function logPath(string $runId): string
    {
        return $this->workerLauncher->workerLogPath($runId);
    }

    public function dispatchLogPath(string $runId): string
    {
        return $this->workerLauncher->dispatchLogPath($runId);
    }

    public function logRecentlyUpdated(string $runId, int $withinSeconds = 90): bool
    {
        $path = $this->logPath($runId);
        if (! is_file($path)) {
            return false;
        }

        return (time() - (int) filemtime($path)) <= $withinSeconds;
    }

    public function handoffProgress(string $runId): int
    {
        $handoff = $this->handoffStore->read($runId);

        return (int) ($handoff['progress_current'] ?? 0);
    }

    public function maxWallClockMinutes(SimulationRunModel $run): int
    {
        $planned = max(1, (int) $run->planned_total);
        $perMinute = max(1, (int) $run->events_per_minute);
        $durationMinutes = max(1, (int) $run->duration_minutes);

        $publishWindow = (int) ceil($planned / $perMinute);
        $processingEstimate = (int) ceil(($planned * 12) / 60);

        return max(8, $publishWindow + $processingEstimate + $durationMinutes + 2);
    }

    public function isLikelyAlive(SimulationRunModel $run): bool
    {
        if ($this->logRecentlyUpdated($run->id)) {
            return true;
        }

        $handoff = $this->handoffStore->readForSync($run->id);
        if ($handoff === null) {
            return false;
        }

        $terminal = (string) ($handoff['terminal_status'] ?? '');
        if ($terminal === 'completed' || $terminal === 'failed' || $terminal === 'cancelled') {
            return false;
        }

        $phase = (string) ($handoff['phase'] ?? '');
        if (in_array($phase, ['publishing', 'simulating', 'starting'], true)) {
            return $this->handoffProgressRecentlyUpdated($handoff, 120)
                || $this->logRecentlyUpdated($run->id, 120);
        }

        if ($phase === 'dispatched') {
            return $this->logRecentlyUpdated($run->id, 120);
        }

        return $this->handoffProgressRecentlyUpdated($handoff, 120);
    }

    public function secondsSinceLastProgress(string $runId): ?int
    {
        $handoff = $this->handoffStore->readForSync($runId);
        if ($handoff === null) {
            return null;
        }

        $progressAt = $handoff['progress_at'] ?? null;
        if (! is_string($progressAt) || $progressAt === '') {
            return null;
        }

        try {
            return (int) now()->diffInSeconds(\Carbon\Carbon::parse($progressAt), absolute: true);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $handoff
     */
    private function handoffProgressRecentlyUpdated(array $handoff, int $withinSeconds): bool
    {
        $progressAt = $handoff['progress_at'] ?? null;
        if (! is_string($progressAt) || $progressAt === '') {
            return false;
        }

        try {
            return now()->diffInSeconds(\Carbon\Carbon::parse($progressAt), absolute: true) <= $withinSeconds;
        } catch (\Throwable) {
            return false;
        }
    }
}
