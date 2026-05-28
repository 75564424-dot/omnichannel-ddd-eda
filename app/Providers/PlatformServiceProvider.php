<?php

declare(strict_types=1);

namespace App\Providers;

use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use App\Shared\Platform\DatabaseInstanceTenantContext;
use App\Shared\Platform\LocalFleet\LocalFleetEnvBuilder;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;
use App\Shared\Platform\LocalFleet\LocalFleetRegistry;
use App\Shared\Platform\LocalFleet\LocalFleetTenantMirror;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

final class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__, 2).'/config/platform.php',
            'platform',
        );

        $this->app->singleton(
            InstanceTenantContextInterface::class,
            DatabaseInstanceTenantContext::class,
        );

        $this->app->singleton(LocalFleetRegistry::class, function () {
            $config = config('platform.local_fleet', []);

            return new LocalFleetRegistry(
                base_path((string) ($config['registry_path'] ?? 'deploy/local-instances/fleet-registry.json')),
                (int) ($config['port_range_start'] ?? 8001),
            );
        });

        $this->app->singleton(LocalFleetEnvBuilder::class, fn () => new LocalFleetEnvBuilder);

        $this->app->singleton(LocalFleetTenantMirror::class, fn ($app) => new LocalFleetTenantMirror(
            $app->make(LocalFleetEnvBuilder::class),
        ));

        $this->app->singleton(LocalFleetInstanceProvisioner::class, function ($app) {
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

    public function boot(): void
    {
        $context = $this->app->make(InstanceTenantContextInterface::class);
        Log::shareContext($context->logContext());
    }
}
