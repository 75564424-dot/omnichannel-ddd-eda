<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Control\Infrastructure\Models\SimulationRunModel;
use Illuminate\Support\Str;

/**
 * Marks simulation runs that exceeded their configured window or never reported progress.
 */
final class SimulationRunStaleGuard
{
    public function __construct(
        private readonly SimulationRunFailureHandler $failureHandler,
        private readonly SimulationRunHandoffStore $handoffStore,
        private readonly SimulationRunHandoffProgressSync $handoffProgressSync,
        private readonly SimulationRunWorkerMonitor $workerMonitor,
    ) {}

    public function failExpiredRuns(): int
    {
        if (! config('platform.control_plane', false)) {
            return 0;
        }

        $this->handoffProgressSync->syncActiveRuns();

        $failed = 0;
        $runs = SimulationRunModel::query()
            ->whereIn('status', [SimulationRunModel::STATUS_PENDING, SimulationRunModel::STATUS_RUNNING])
            ->get();

        foreach ($runs as $run) {
            $reason = $this->staleReason($run->fresh());
            if ($reason === null) {
                continue;
            }

            $this->failureHandler->handle($run, $reason, $this->failureContext($run));
            $failed++;
        }

        return $failed;
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

        if ($this->workerMonitor->isLikelyAlive($run)) {
            if ($elapsedMinutes <= $maxWallMinutes) {
                return null;
            }

            return 'Tiempo máximo de ejecución del worker superado (~'.$maxWallMinutes
                .' min para '.$run->planned_total.' eventos). Revise el log del worker.';
        }

        if ($run->status === SimulationRunModel::STATUS_RUNNING
            && $effectiveProgress > 0
            && $elapsedMinutes <= $maxWallMinutes) {
            return null;
        }

        $startupGraceMinutes = 3;
        if ($run->status === SimulationRunModel::STATUS_RUNNING
            && $effectiveProgress === 0
            && $elapsedMinutes >= $startupGraceMinutes
            && $elapsedMinutes < $maxWallMinutes) {
            $handoff = $this->handoffStore->read($run->id);
            $phase = is_string($handoff['phase'] ?? null) ? $handoff['phase'] : 'sin handoff';

            return 'El worker del silo cliente no arrancó o murió al inicio (fase: '.$phase.'). '
                .'Log: storage/logs/simulation-worker-'.$run->id.'.log';
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

    /**
     * @return array<string, mixed>
     */
    private function failureContext(SimulationRunModel $run): array
    {
        $logPath = $this->workerMonitor->logPath($run->id);
        $excerpt = is_file($logPath)
            ? \Illuminate\Support\Str::limit((string) file_get_contents($logPath), 4000)
            : null;

        return [
            'source'     => 'stale_guard',
            'worker_log' => $excerpt,
        ];
    }
}
