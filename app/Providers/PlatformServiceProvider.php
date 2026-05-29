<?php

declare(strict_types=1);

namespace App\Providers;

use App\Providers\Registrars\LocalFleetBindingsRegistrar;
use App\Providers\Registrars\PlatformServiceBindingsRegistrar;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
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

        PlatformServiceBindingsRegistrar::register($this->app);
        LocalFleetBindingsRegistrar::register($this->app);
    }

    public function boot(): void
    {
        $context = $this->app->make(InstanceTenantContextInterface::class);
        Log::shareContext($context->logContext());
    }
}
