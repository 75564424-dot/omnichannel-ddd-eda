<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\AuthenticatePlatformApi;
use App\Models\User;
use App\Shared\Identity\Contracts\PlatformAuthorizationServiceInterface;
use App\Shared\Identity\Policies\ManageUsersPolicy;
use App\Shared\Identity\Policies\PublishEventPolicy;
use App\Shared\Identity\Policies\ResolveDeadLetterPolicy;
use App\Shared\Identity\Policies\SyncRegistryPolicy;
use App\Shared\Identity\Services\PlatformAuthorizationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PlatformAuthorizationServiceInterface::class, PlatformAuthorizationService::class);
    }

    public function boot(): void
    {
        Gate::define('platform.publish', fn (?User $user) => $this->allowsApiAbility($user, 'events:publish'));
        Gate::define('platform.sync-registry', fn (?User $user) => $this->allowsApiAbility($user, 'bus:admin'));
        Gate::define('platform.resolve-dead-letter', fn (?User $user) => $this->allowsApiAbility($user, 'bus:admin'));
        Gate::define('platform.manage-users', fn (?User $user) => $this->allowsManageUsers($user));
        Gate::define('platform.manage-integrations', fn (?User $user) => $this->allowsApiAbility($user, 'integrations:admin'));
    }

    private function allowsManageUsers(?User $user): bool
    {
        if (! config('platform_auth.web_auth_enabled', true)) {
            return true;
        }

        return app(ManageUsersPolicy::class)->manage($user);
    }

    private function allowsApiAbility(?User $user, string $ability): bool
    {
        if (! config('security.api_auth_enabled', true)) {
            return true;
        }

        $principal = AuthenticatePlatformApi::principal(request());
        if ($principal !== null) {
            return $principal->hasAbility($ability);
        }

        return match ($ability) {
            'events:publish' => app(PublishEventPolicy::class)->publish($user),
            'bus:admin'        => app(SyncRegistryPolicy::class)->sync($user),
            default            => $user !== null && app(PlatformAuthorizationServiceInterface::class)->userCan($user, $ability),
        };
    }
}
