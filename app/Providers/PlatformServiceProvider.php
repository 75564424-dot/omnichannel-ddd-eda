<?php

declare(strict_types=1);

namespace App\Providers;

use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use App\Shared\Platform\DatabaseInstanceTenantContext;
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
    }

    public function boot(): void
    {
        $context = $this->app->make(InstanceTenantContextInterface::class);
        Log::shareContext($context->logContext());
    }
}
