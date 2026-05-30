<?php

declare(strict_types=1);

namespace App\Providers\Registrars;

use App\Shared\Platform\LocalFleet\LocalFleetEnvBuilder;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;
use App\Shared\Platform\LocalFleet\LocalFleetOrphanPruner;
use App\Shared\Platform\LocalFleet\LocalFleetRegistry;
use App\Shared\Platform\LocalFleet\LocalFleetSyncService;
use App\Shared\Platform\LocalFleet\LocalFleetTenantMirror;
use App\Shared\Platform\LocalFleet\LocalFleetProcessSupervisor;
use Illuminate\Contracts\Foundation\Application;

final class LocalFleetBindingsRegistrar
{
    public static function register(Application $app): void
    {
        $app->singleton(LocalFleetRegistry::class, function () {
            $config = config('platform.local_fleet', []);

            return new LocalFleetRegistry(
                base_path((string) ($config['registry_path'] ?? 'deploy/local-instances/fleet-registry.json')),
                (int) ($config['port_range_start'] ?? 8001),
            );
        });

        $app->singleton(LocalFleetEnvBuilder::class, fn () => new LocalFleetEnvBuilder);

        $app->singleton(LocalFleetTenantMirror::class, fn ($app) => new LocalFleetTenantMirror(
            $app->make(LocalFleetEnvBuilder::class),
        ));

        $app->singleton(LocalFleetSyncService::class);
        $app->singleton(LocalFleetOrphanPruner::class);
        $app->singleton(LocalFleetProcessSupervisor::class, fn () => new LocalFleetProcessSupervisor());

        $app->singleton(LocalFleetInstanceProvisioner::class, function ($app) {
            $config = config('platform.local_fleet', []);

            return new LocalFleetInstanceProvisioner(
                $app->make(LocalFleetRegistry::class),
                $app->make(LocalFleetEnvBuilder::class),
                $app->make(LocalFleetTenantMirror::class),
                $app->make(\App\Shared\Platform\Services\InstanceDeploymentService::class),
                (bool) ($config['auto_provision'] ?? false),
                (string) ($config['default_admin_password'] ?? 'client-local-dev'),
                (string) ($config['control_plane_slug'] ?? 'platform'),
            );
        });
    }
}
