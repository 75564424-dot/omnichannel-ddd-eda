<?php

declare(strict_types=1);

namespace App\Providers\Registrars;

use App\Simulation\Application\Services\Execution\ClientSiloSimulationExecutor;
use App\Simulation\Application\Services\Execution\ExecuteSimulationRunOnInstanceService;
use App\Simulation\Application\Services\Prepare\SimulationDiagnosticsReader;
use App\Simulation\Application\Services\Prepare\SimulationInstancePrepareService;
use App\Simulation\Application\Services\Progress\SimulationRunCompletionService;
use App\Simulation\Application\Services\Handoff\SimulationRunHandoffSync;
use App\Simulation\Application\Services\Progress\SimulationRunInternalApiService;
use App\Simulation\Application\Services\Orchestration\SimulationRunQueryService;
use App\Simulation\Application\Services\Reset\SimulationRunsResetService;
use App\Simulation\Application\Services\Orchestration\SimulationStaleRunReplacer;
use App\Simulation\Application\Services\Prepare\SimulationTenantSettingsSync;
use App\Simulation\Application\Services\Worker\SimulationWorkerEnvironmentFactory;
use App\Simulation\Application\Services\Worker\SimulationWorkerLauncher;
use App\Simulation\Application\Services\Worker\SimulationWorkerTenantBootstrap;
use App\Simulation\Application\Services\Runtime\SimulationPublishScope;
use App\Shared\Platform\LocalInstanceEnvironmentLoader;
use Illuminate\Contracts\Foundation\Application;

final class SimulationServiceBindingsRegistrar
{
    /** @return list<class-string> */
    public static function singletonClasses(): array
    {
        return [
            SimulationPublishScope::class,
            SimulationWorkerLauncher::class,
            LocalInstanceEnvironmentLoader::class,
            SimulationWorkerEnvironmentFactory::class,
            SimulationWorkerTenantBootstrap::class,
            SimulationDiagnosticsReader::class,
            SimulationTenantSettingsSync::class,
            SimulationStaleRunReplacer::class,
            ClientSiloSimulationExecutor::class,
            SimulationRunCompletionService::class,
            SimulationRunHandoffSync::class,
            ExecuteSimulationRunOnInstanceService::class,
            SimulationRunsResetService::class,
            SimulationInstancePrepareService::class,
            SimulationRunQueryService::class,
            SimulationRunInternalApiService::class,
        ];
    }

    public static function register(Application $app): void
    {
        foreach (self::singletonClasses() as $class) {
            $app->singleton($class);
        }
    }
}
