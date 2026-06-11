<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Reset;

use App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore;
use App\Simulation\Application\Services\Orchestration\SimulationRunStaleGuard;
use App\Simulation\Application\Services\Orchestration\SimulationStaleRunReplacer;
use App\Simulation\Domain\ValueObjects\SimulationMessages;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;

final class SimulationRunsResetService
{
    public function __construct(
        private readonly SimulationRunStaleGuard $staleGuard,
        private readonly SimulationRunHandoffStore $handoffStore,
        private readonly SimulationStaleRunReplacer $staleRunReplacer,
        private readonly DatabaseManager $db,
        private readonly Application $application,
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

        if ($onlyStale || ! $this->db->getSchemaBuilder()->hasTable('simulation_runs')) {
            return [
                'stale_failed' => $staleFailed,
                'handoff_purged' => $handoffPurged,
                'rows_deleted' => null,
            ];
        }

        return [
            'stale_failed' => $staleFailed,
            'handoff_purged' => $handoffPurged,
            'rows_deleted' => (int) $this->db->table('simulation_runs')->delete(),
        ];
    }

    public function isControlPlaneHost(): bool
    {
        return config('platform.control_plane', false)
            || $this->application->environment('control-plane');
    }
}
