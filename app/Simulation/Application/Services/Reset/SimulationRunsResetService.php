<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Reset;


use App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore;
use App\Simulation\Application\Services\Orchestration\SimulationRunStaleGuard;
use App\Simulation\Application\Services\Orchestration\SimulationStaleRunReplacer;
use App\Simulation\Domain\ValueObjects\SimulationMessages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class SimulationRunsResetService
{
    public function __construct(
        private readonly SimulationRunStaleGuard $staleGuard,
        private readonly SimulationRunHandoffStore $handoffStore,
        private readonly SimulationStaleRunReplacer $staleRunReplacer,
    ) {}

    /**
     * @return array{stale_failed: int, handoff_purged: int, rows_deleted: int|null}
     */
    public function reset(bool $failStale, bool $onlyStale): array
    {
        $staleFailed = 0;
        if ($failStale || $onlyStale) {
            $staleFailed = $this->staleGuard->failExpiredRuns()
                + $this->staleRunReplacer->replaceAllActive(SimulationMessages::MANUAL_RESET);
        }

        $handoffPurged = $this->handoffStore->purgeAll();

        if ($onlyStale || ! Schema::hasTable('simulation_runs')) {
            return [
                'stale_failed' => $staleFailed,
                'handoff_purged' => $handoffPurged,
                'rows_deleted' => null,
            ];
        }

        return [
            'stale_failed' => $staleFailed,
            'handoff_purged' => $handoffPurged,
            'rows_deleted' => (int) DB::table('simulation_runs')->delete(),
        ];
    }

    public function isControlPlaneHost(): bool
    {
        return config('platform.control_plane', false)
            || app()->environment('control-plane');
    }
}
