<?php

declare(strict_types=1);

namespace App\Providers;

use App\Providers\Registrars\PlatformGateRegistrar;
use App\Shared\Identity\Contracts\PlatformAuthorizationServiceInterface;
use App\Shared\Identity\Services\PlatformAuthorizationService;
use Illuminate\Support\ServiceProvider;

final class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PlatformAuthorizationServiceInterface::class, PlatformAuthorizationService::class);
        $this->app->singleton(PlatformGateRegistrar::class);
    }

    public function boot(): void
    {
        $this->app->make(PlatformGateRegistrar::class)->register();
    }
}
