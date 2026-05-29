<?php

declare(strict_types=1);

namespace App\Providers\Registrars;

use App\Http\Middleware\AuthenticatePlatformApi;
use App\Models\User;
use App\Shared\Identity\Contracts\PlatformAuthorizationServiceInterface;
use App\Shared\Identity\Policies\ManageUsersPolicy;
use App\Shared\Identity\Policies\PublishEventPolicy;
use App\Shared\Identity\Policies\ResolveDeadLetterPolicy;
use App\Shared\Identity\Policies\SyncRegistryPolicy;
use Illuminate\Support\Facades\Gate;

final class PlatformGateRegistrar
{
    public static function register(): void
    {
        Gate::define('platform.publish', fn (?User $user) => self::allowsApiAbility($user, 'events:publish'));
        Gate::define('platform.sync-registry', fn (?User $user) => self::allowsApiAbility($user, 'bus:admin'));
        Gate::define('platform.resolve-dead-letter', fn (?User $user) => self::allowsApiAbility($user, 'bus:admin'));
        Gate::define('platform.manage-users', fn (?User $user) => self::allowsManageUsers($user));
        Gate::define('platform.manage-integrations', fn (?User $user) => self::allowsApiAbility($user, 'integrations:admin'));
    }

    private static function allowsManageUsers(?User $user): bool
    {
        if (! config('platform_auth.web_auth_enabled', true)) {
            return true;
        }

        return app(ManageUsersPolicy::class)->manage($user);
    }

    private static function allowsApiAbility(?User $user, string $ability): bool
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
            'bus:admin' => app(SyncRegistryPolicy::class)->sync($user),
            default => $user !== null && app(PlatformAuthorizationServiceInterface::class)->userCan($user, $ability),
        };
    }
}
