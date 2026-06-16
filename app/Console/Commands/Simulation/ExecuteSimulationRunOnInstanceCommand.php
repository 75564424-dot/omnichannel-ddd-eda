<?php

declare(strict_types=1);

namespace App\Console\Commands\Simulation;

use App\Simulation\Application\Services\Execution\ExecuteSimulationRunOnInstanceService;
use App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore;
use Illuminate\Console\Command;

final class ExecuteSimulationRunOnInstanceCommand extends Command
{
    protected $signature = 'platform:simulation:execute-run {runId : Simulation run UUID on control plane}';

    protected $description = 'Execute a control-plane simulation run inside this client silo (local fleet).';

    public function handle(
        ExecuteSimulationRunOnInstanceService $executor,
        SimulationRunHandoffStore $handoffStore,
    ): int {
        $runId = (string) $this->argument('runId');

        if ($handoffStore->read($runId) !== null) {
            $this->info("Using local handoff spec for run {$runId}.");
        }

        $dbPath = (string) config('database.connections.'.config('database.default').'.database', '');
        $tenantSlug = (string) config('platform.client_slug', '');
        $this->line("Worker silo DB: {$dbPath} (tenant «{$tenantSlug}»)");

        $result = $executor->execute($runId);

        if ($result->warningMessage !== null) {
            $this->warn($result->warningMessage);
        }

        if ($result->success) {
            if ($result->infoMessage !== null) {
                $this->info($result->infoMessage);
            }

            return self::SUCCESS;
        }

        if ($result->errorMessage !== null) {
            $this->error($result->errorMessage);
        }

        return self::FAILURE;
    }
}

