<?php

declare(strict_types=1);

namespace App\Providers\Registrars;

use App\Simulation\Application\Services\Orchestration\LocalFleetSimulationRunner;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use App\Shared\Platform\DatabaseInstanceTenantContext;
use App\Shared\Platform\LocalFleet\LocalFleetEnvBuilder;
use App\Shared\Platform\LocalFleet\LocalFleetOrphanPruner;
use App\Shared\Platform\LocalFleet\LocalFleetSyncService;
use App\Shared\Platform\Services\ClientInstanceBootstrapService;
use App\Shared\Platform\Services\ControlPlaneFleetBootstrapService;
use App\Shared\Platform\Services\DashboardDemoEventsEmitter;
use App\Shared\Platform\Services\DemoIdentityResetService;
use App\Shared\Platform\Services\OperationalDataResetService;
use Illuminate\Contracts\Foundation\Application;

final class PlatformServiceBindingsRegistrar
{
    public static function register(Application $app): void
    {
        $app->singleton(
            InstanceTenantContextInterface::class,
            DatabaseInstanceTenantContext::class,
        );

        $app->singleton(LocalFleetSimulationRunner::class);
        $app->singleton(ControlPlaneFleetBootstrapService::class);
        $app->singleton(OperationalDataResetService::class);
        $app->singleton(DemoIdentityResetService::class);
        $app->singleton(ClientInstanceBootstrapService::class);
        $app->singleton(DashboardDemoEventsEmitter::class);
    }
}
