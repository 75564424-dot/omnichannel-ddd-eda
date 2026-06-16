<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Orchestration;


use App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore;
use App\Simulation\Application\Services\Handoff\SimulationRunHandoffSync;
use App\Simulation\Application\Services\Prepare\SimulationDiagnosticsReader;
use App\Simulation\Application\Services\Progress\SimulationRunFailureHandler;
use App\Simulation\Application\Services\Worker\SimulationRunWorkerMonitor;
use App\Control\Infrastructure\Models\SimulationRunModel;

/**
 * Marks simulation runs that exceeded their configured window or never reported progress.
 */
final class SimulationRunStaleGuard
{
    public function __construct(
        private readonly SimulationRunFailureHandler $failureHandler,
        private readonly SimulationRunHandoffStore $handoffStore,
        private readonly SimulationRunHandoffSync $handoffSync,
        private readonly SimulationRunWorkerMonitor $workerMonitor,
        private readonly SimulationDiagnosticsReader $diagnosticsReader,
    ) {}

    public function failExpiredRuns(): int
    {
        if (! config('platform.control_plane', false)) {
            return 0;
        }

        $this->handoffSync->syncActiveRuns();

        $failed = 0;
        $runs = SimulationRunModel::query()
            ->whereIn('status', [SimulationRunModel::STATUS_PENDING, SimulationRunModel::STATUS_RUNNING])
            ->get();

        foreach ($runs as $run) {
            if ($this->failRunIfExpired($run) !== null) {
                $failed++;
            }
        }

        return $failed;
    }

    public function failRunIfExpired(SimulationRunModel $run): ?SimulationRunModel
    {
        if (! config('platform.control_plane', false)) {
            return null;
        }

        if (! in_array($run->status, [
            SimulationRunModel::STATUS_PENDING,
            SimulationRunModel::STATUS_RUNNING,
        ], true)) {
            return null;
        }

        $this->handoffSync->syncRun($run);
        $run = $run->fresh(['tenant']);
        if ($run === null) {
            return null;
        }

        $reason = $this->staleReason($run);
        if ($reason === null) {
            return null;
        }

        $this->failureHandler->handle($run, $reason, [
            'source'     => 'stale_guard',
            'worker_log' => $this->diagnosticsReader->excerpt($run->id),
        ]);

        return $run->fresh(['tenant']);
    }

    private function staleReason(SimulationRunModel $run): ?string
    {
        $startedAt = $run->started_at ?? $run->created_at;
        if (! $startedAt instanceof \Carbon\CarbonInterface) {
            return null;
        }

        $handoffProgress = $this->workerMonitor->handoffProgress($run->id);
        $effectiveProgress = max((int) $run->progress_current, $handoffProgress);
        $maxWallMinutes = $this->workerMonitor->maxWallClockMinutes($run);
        $elapsedMinutes = $startedAt->diffInMinutes(now(), absolute: true);

        if ($this->handoffHasTerminal($run->id)) {
            return null;
        }

        $stalledProgressSeconds = max(90, (int) config('platform.simulation.stalled_progress_seconds', 120));

        if ($run->status === SimulationRunModel::STATUS_RUNNING
            && $effectiveProgress > 0
            && $effectiveProgress < (int) $run->planned_total) {
            $secondsSinceProgress = $this->workerMonitor->secondsSinceLastProgress($run->id);
            if ($secondsSinceProgress !== null
                && $secondsSinceProgress >= $stalledProgressSeconds
                && ! $this->workerMonitor->logRecentlyUpdated($run->id, $stalledProgressSeconds)) {
                return 'El worker se detuvo con progreso parcial ('
                    .$effectiveProgress.'/'.$run->planned_total
                    .', sin actividad '.$secondsSinceProgress.' s). Revise storage/logs/simulation-worker-'
                    .$run->id.'.log';
            }
        }

        if ($this->workerMonitor->isLikelyAlive($run)) {
            if ($elapsedMinutes <= $maxWallMinutes) {
                return null;
            }

            return 'Tiempo máximo de ejecución del worker superado (~'.$maxWallMinutes
                .' min para '.$run->planned_total.' eventos). Revise storage/logs/simulation-*-'.$run->id.'.log';
        }

        $startupGraceMinutes = max(1, (int) config('platform.simulation.startup_grace_minutes', 3));
        $noProgressTimeoutMinutes = max(
            $startupGraceMinutes + 1,
            (int) config('platform.simulation.no_progress_timeout_minutes', 5),
        );

        if ($run->status === SimulationRunModel::STATUS_RUNNING
            && $effectiveProgress === 0
            && $elapsedMinutes >= $noProgressTimeoutMinutes
            && $elapsedMinutes < $maxWallMinutes) {
            $handoff = $this->handoffStore->read($run->id);
            $phase = is_string($handoff['phase'] ?? null) ? $handoff['phase'] : 'sin handoff';

            return 'Sin progreso durante '.$noProgressTimeoutMinutes
                .' min (fase: '.$phase.'). Revise storage/logs/simulation-dispatch-'
                .$run->id.'.log y simulation-worker-'.$run->id.'.log';
        }

        if ($run->status === SimulationRunModel::STATUS_RUNNING
            && $effectiveProgress === 0
            && $elapsedMinutes >= $startupGraceMinutes
            && $elapsedMinutes < $noProgressTimeoutMinutes
            && ! $this->workerMonitor->isLikelyAlive($run)) {
            $handoff = $this->handoffStore->read($run->id);
            $phase = is_string($handoff['phase'] ?? null) ? $handoff['phase'] : 'sin handoff';

            return 'El worker del silo cliente no arrancó o murió al inicio (fase: '.$phase.'). '
                .'Revise storage/logs/simulation-dispatch-'.$run->id.'.log y simulation-worker-'.$run->id.'.log';
        }

        if ($run->status === SimulationRunModel::STATUS_RUNNING
            && $elapsedMinutes >= $maxWallMinutes) {
            return 'Tiempo máximo de simulación superado (~'.$maxWallMinutes.' min, '
                .$effectiveProgress.'/'.$run->planned_total.' eventos).';
        }

        $createdAt = $run->created_at;
        if ($run->status === SimulationRunModel::STATUS_PENDING
            && $createdAt instanceof \Carbon\CarbonInterface
            && $createdAt->lte(now()->subMinutes(5))) {
            return 'La simulación quedó pendiente demasiado tiempo sin worker activo.';
        }

        return null;
    }

    private function handoffHasTerminal(string $runId): bool
    {
        $handoff = $this->handoffStore->readForSync($runId);
        if ($handoff === null) {
            return false;
        }

        $terminal = (string) ($handoff['terminal_status'] ?? '');

        return $terminal === 'completed' || $terminal === 'failed' || $terminal === 'cancelled';
    }
}
