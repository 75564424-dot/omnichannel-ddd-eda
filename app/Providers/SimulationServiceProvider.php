<?php

declare(strict_types=1);

namespace App\Providers;

use App\Control\Application\Services\ClientSiloSimulationExecutor;
use App\Control\Application\Services\SimulationDiagnosticsReader;
use App\Control\Application\Services\SimulationRunCompletionService;
use App\Control\Application\Services\SimulationRunHandoffSync;
use App\Control\Application\Services\SimulationStaleRunReplacer;
use App\Control\Application\Services\SimulationTenantSettingsSync;
use App\Control\Application\Services\SimulationWorkerEnvironmentFactory;
use App\Control\Application\Services\SimulationWorkerLauncher;
use App\Middleware\Application\Services\SimulationPublishScope;
use Illuminate\Support\ServiceProvider;

final class SimulationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SimulationPublishScope::class);
        $this->app->singleton(SimulationWorkerLauncher::class);
        $this->app->singleton(SimulationWorkerEnvironmentFactory::class);
        $this->app->singleton(SimulationDiagnosticsReader::class);
        $this->app->singleton(SimulationTenantSettingsSync::class);
        $this->app->singleton(SimulationStaleRunReplacer::class);
        $this->app->singleton(ClientSiloSimulationExecutor::class);
        $this->app->singleton(SimulationRunCompletionService::class);
        $this->app->singleton(SimulationRunHandoffSync::class);
    }
}
