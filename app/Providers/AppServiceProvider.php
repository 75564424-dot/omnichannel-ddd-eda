<?php

declare(strict_types=1);

namespace App\Providers;

use App\Dashboard\Interfaces\Providers\DashboardServiceProvider;
use App\Middleware\Interfaces\Providers\MiddlewareServiceProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Core platform host: observability dashboard + event bus middleware only.
 * External domains register via {@see \App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface}
 * and merge config (e.g. eventbus.subscriptions) from their own service providers.
 */
class AppServiceProvider extends ServiceProvider
{
    protected array $contextProviders = [
        DashboardServiceProvider::class,
        MiddlewareServiceProvider::class,
        \App\Integration\Interfaces\Providers\IntegrationServiceProvider::class,
    ];

    public function register(): void
    {
        foreach ($this->contextProviders as $provider) {
            $this->app->register($provider);
        }
    }

    public function boot(): void
    {
        //
    }
}
