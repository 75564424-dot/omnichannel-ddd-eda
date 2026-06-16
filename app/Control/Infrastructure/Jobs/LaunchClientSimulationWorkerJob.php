<?php

declare(strict_types=1);

namespace App\Control\Infrastructure\Jobs;

use App\Simulation\Application\Services\Orchestration\LocalFleetSimulationRunner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs the client-silo simulation worker to completion after the control-plane HTTP response is sent.
 *
 * Spawns a detached subprocess (popen on Windows) so the worker outlives this job without blocking serve.
 */
final class LaunchClientSimulationWorkerJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 7200;

    public function __construct(
        public readonly string $simulationRunId,
    ) {}

    public function handle(LocalFleetSimulationRunner $runner): void
    {
        $runner->launchWorker($this->simulationRunId);
    }
}
