<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Control\Infrastructure\Models\SimulationRunModel;

/**
 * Merges worker progress written to the local handoff file into control-plane simulation_runs rows.
 */
final class SimulationRunHandoffProgressSync
{
    public function __construct(
        private readonly SimulationRunHandoffStore $handoffStore,
    ) {}

    public function syncRun(SimulationRunModel $run): SimulationRunModel
    {
        if (! in_array($run->status, [
            SimulationRunModel::STATUS_PENDING,
            SimulationRunModel::STATUS_RUNNING,
        ], true)) {
            return $run;
        }

        $handoff = $this->handoffStore->read($run->id);
        if ($handoff === null) {
            return $run;
        }

        $current = (int) ($handoff['progress_current'] ?? 0);
        if ($current <= (int) $run->progress_current) {
            return $run;
        }

        $run->update([
            'status'           => SimulationRunModel::STATUS_RUNNING,
            'progress_current' => $current,
            'published'        => $current,
            'started_at'       => $run->started_at ?? now(),
        ]);

        return $run->fresh(['tenant']) ?? $run;
    }

    public function syncActiveRuns(): void
    {
        $runs = SimulationRunModel::query()
            ->whereIn('status', [SimulationRunModel::STATUS_PENDING, SimulationRunModel::STATUS_RUNNING])
            ->get();

        foreach ($runs as $run) {
            $this->syncRun($run);
        }
    }
}
