<?php

declare(strict_types=1);

namespace App\Control\Infrastructure\Jobs;

use App\Control\Application\Services\SimulationRunOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class RunTenantSimulationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 7200;

    public function __construct(
        public readonly string $simulationRunId,
    ) {}

    public function handle(SimulationRunOrchestrator $orchestrator): void
    {
        $orchestrator->executeRun($this->simulationRunId);
    }
}
