<?php

declare(strict_types=1);

namespace App\Console\Commands\Simulation;

use App\Simulation\Application\Services\Reset\SimulationRunsResetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

final class ResetSimulationRunsCommand extends Command
{
    protected $signature = 'platform:simulation:reset
                            {--fail-stale : Mark pending/running runs as failed before delete}
                            {--only-stale : Only fail stale runs, do not delete history}';

    protected $description = 'Clear simulation run history (control plane) and optionally fail stuck runs.';

    public function handle(SimulationRunsResetService $resetService): int
    {
        if (! $resetService->isControlPlaneHost()) {
            $this->error('Run on control plane (--env=control-plane).');

            return self::FAILURE;
        }

        if (! Schema::hasTable('simulation_runs')) {
            $this->warn('Table simulation_runs does not exist.');

            return self::SUCCESS;
        }

        $result = $resetService->reset(
            (bool) $this->option('fail-stale'),
            (bool) $this->option('only-stale'),
        );

        if ($result['stale_failed'] > 0) {
            $this->info('Simulaciones colgadas marcadas como fallidas: '.$result['stale_failed']);
        }

        if ($result['handoff_purged'] > 0) {
            $this->line("  · handoff files purged: {$result['handoff_purged']}");
        }

        if ($this->option('only-stale')) {
            return self::SUCCESS;
        }

        if ($result['rows_deleted'] !== null) {
            $this->info("Historial de simulaciones eliminado: {$result['rows_deleted']} fila(s).");
        }

        return self::SUCCESS;
    }
}

