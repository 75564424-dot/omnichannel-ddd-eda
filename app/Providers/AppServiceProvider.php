<?php

declare(strict_types=1);

namespace App\Providers;

use App\Providers\Registrars\BoundedContextProviderRegistrar;
use App\Providers\Registrars\SqliteConcurrencyConfigurator;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\ServiceProvider;

/**
 * Core platform host: observability dashboard + event bus middleware only.
 * External domains register via {@see \App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface}
 * and merge config (e.g. eventbus.subscriptions) from their own service providers.
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        BoundedContextProviderRegistrar::register($this->app);
    }

    public function boot(): void
    {
        $xsrfCookie = (string) config('session.xsrf_cookie', 'XSRF-TOKEN');
        EncryptCookies::except([$xsrfCookie, 'XSRF-TOKEN']);
        (new SqliteConcurrencyConfigurator())->configure();
    }
}
