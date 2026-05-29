<?php

declare(strict_types=1);

namespace App\Providers;

use App\Dashboard\Interfaces\Providers\DashboardServiceProvider;
use App\Middleware\Interfaces\Providers\MiddlewareServiceProvider;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        $xsrfCookie = (string) config('session.xsrf_cookie', 'XSRF-TOKEN');
        EncryptCookies::except([$xsrfCookie, 'XSRF-TOKEN']);
        $this->configureSqliteConcurrency();
    }

    private function configureSqliteConcurrency(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        try {
            $pdo = DB::connection()->getPdo();
            $pdo->exec('PRAGMA busy_timeout = 10000');
            $pdo->exec('PRAGMA journal_mode = WAL');
        } catch (Throwable) {
            // Non-fatal: some test environments use in-memory sqlite without WAL support.
        }
    }
}
