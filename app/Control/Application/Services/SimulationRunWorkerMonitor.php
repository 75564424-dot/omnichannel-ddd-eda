<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Control\Infrastructure\Models\SimulationRunModel;

/**
 * Detects whether a client-silo simulation worker is still alive (log/handoff activity).
 */
final class SimulationRunWorkerMonitor
{
    public function __construct(
        private readonly SimulationRunHandoffStore $handoffStore,
    ) {}

    public function logPath(string $runId): string
    {
        return storage_path('logs/simulation-worker-'.$runId.'.log');
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

        $handoff = $this->handoffStore->read($run->id);
        if ($handoff === null) {
            return false;
        }

        $phase = (string) ($handoff['phase'] ?? '');
        if ($phase !== '' && $phase !== 'starting') {
            return true;
        }

        $progressAt = $handoff['progress_at'] ?? null;
        if (is_string($progressAt) && $progressAt !== '') {
            try {
                return now()->diffInSeconds(\Carbon\Carbon::parse($progressAt), absolute: true) <= 120;
            } catch (\Throwable) {
                return false;
            }
        }

        return $this->handoffProgress($run->id) > 0;
    }
}
