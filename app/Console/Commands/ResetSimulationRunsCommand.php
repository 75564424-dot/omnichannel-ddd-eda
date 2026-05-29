<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Control\Application\Services\SimulationMessages;
use App\Control\Application\Services\SimulationRunHandoffStore;
use App\Control\Application\Services\SimulationRunStaleGuard;
use App\Control\Application\Services\SimulationStaleRunReplacer;
use App\Control\Infrastructure\Models\SimulationRunModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ResetSimulationRunsCommand extends Command
{
    protected $signature = 'platform:simulation:reset
                            {--fail-stale : Mark pending/running runs as failed before delete}
                            {--only-stale : Only fail stale runs, do not delete history}';

    protected $description = 'Clear simulation run history (control plane) and optionally fail stuck runs.';

    public function handle(
        SimulationRunStaleGuard $staleGuard,
        SimulationRunHandoffStore $handoffStore,
        SimulationStaleRunReplacer $staleRunReplacer,
    ): int {
        if (! config('platform.control_plane', false)) {
            $this->error('Run on control plane (--env=control-plane).');

            return self::FAILURE;
        }

        if (! Schema::hasTable('simulation_runs')) {
            $this->warn('Table simulation_runs does not exist.');

            return self::SUCCESS;
        }

        if ($this->option('fail-stale') || $this->option('only-stale')) {
            $stale = $staleGuard->failExpiredRuns();
            $manual = $staleRunReplacer->replaceAllActive(SimulationMessages::MANUAL_RESET);
            $this->info('Simulaciones colgadas marcadas como fallidas: '.($stale + $manual));
        }

        $purged = $handoffStore->purgeAll();
        if ($purged > 0) {
            $this->line("  · handoff files purged: {$purged}");
        }

        if ($this->option('only-stale')) {
            return self::SUCCESS;
        }

        $deleted = (int) DB::table('simulation_runs')->delete();
        $this->info("Historial de simulaciones eliminado: {$deleted} fila(s).");

        return self::SUCCESS;
    }
}
