<?php

declare(strict_types=1);

namespace App\Providers\Registrars;

use App\Dashboard\Interfaces\Providers\DashboardServiceProvider;
use App\Integration\Interfaces\Providers\IntegrationServiceProvider;
use App\Middleware\Interfaces\Providers\MiddlewareServiceProvider;
use Illuminate\Contracts\Foundation\Application;

/**
 * Registers core bounded-context service providers from the composition root.
 */
final class BoundedContextProviderRegistrar
{
    /** @return list<class-string> */
    public static function providerClasses(): array
    {
        return [
            DashboardServiceProvider::class,
            MiddlewareServiceProvider::class,
            IntegrationServiceProvider::class,
        ];
    }

    public static function register(Application $app): void
    {
        foreach (self::providerClasses() as $provider) {
            $app->register($provider);
        }
    }
}
