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
    ) {}

    public function failExpiredRuns(): int
    {
        if (! config('platform.control_plane', false)) {
            return 0;
        }

        $failed = 0;
        $runs = SimulationRunModel::query()
            ->whereIn('status', [SimulationRunModel::STATUS_PENDING, SimulationRunModel::STATUS_RUNNING])
            ->get();

        foreach ($runs as $run) {
            $reason = $this->staleReason($run);
            if ($reason === null) {
                continue;
            }

            $this->failureHandler->handle($run, $reason, ['source' => 'stale_guard']);
            $failed++;
        }

        return $failed;
    }

    private function staleReason(SimulationRunModel $run): ?string
    {
        $durationMinutes = max(1, (int) $run->duration_minutes);
        $graceMinutes    = 3;
        $startedAt = $run->started_at ?? $run->created_at;
        if (! $startedAt instanceof \Carbon\CarbonInterface) {
            return null;
        }

        if ($startedAt->lte(now()->subMinutes($durationMinutes + $graceMinutes))) {
            return 'Tiempo máximo de simulación superado ('
                .$durationMinutes.' min + '.$graceMinutes.' min de gracia).';
        }

        if ($run->status === SimulationRunModel::STATUS_RUNNING
            && (int) $run->progress_current === 0
            && $startedAt->lte(now()->subMinutes(2))) {
            return 'Sin progreso: el worker del silo cliente no arrancó o no pudo contactar al control plane (:8000).';
        }

        $createdAt = $run->created_at;
        if ($run->status === SimulationRunModel::STATUS_PENDING
            && $createdAt instanceof \Carbon\CarbonInterface
            && $createdAt->lte(now()->subMinutes(5))) {
            return 'La simulación quedó pendiente demasiado tiempo sin worker activo.';
        }

        return null;
    }
}
