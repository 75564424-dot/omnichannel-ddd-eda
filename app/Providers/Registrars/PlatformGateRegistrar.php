<?php

declare(strict_types=1);

namespace App\Providers\Registrars;

use App\Http\Middleware\AuthenticatePlatformApi;
use App\Models\User;
use App\Shared\Identity\Contracts\PlatformAuthorizationServiceInterface;
use App\Shared\Identity\Policies\ManageUsersPolicy;
use App\Shared\Identity\Policies\PublishEventPolicy;
use App\Shared\Identity\Policies\SyncRegistryPolicy;
use Illuminate\Contracts\Auth\Access\Gate;

final class PlatformGateRegistrar
{
    public function __construct(
        private readonly Gate $gate,
        private readonly ManageUsersPolicy $manageUsersPolicy,
        private readonly PublishEventPolicy $publishEventPolicy,
        private readonly SyncRegistryPolicy $syncRegistryPolicy,
        private readonly PlatformAuthorizationServiceInterface $authorization,
    ) {}

    public function register(): void
    {
        $this->gate->define('platform.publish', fn (?User $user) => $this->allowsApiAbility($user, 'events:publish'));
        $this->gate->define('platform.sync-registry', fn (?User $user) => $this->allowsApiAbility($user, 'bus:admin'));
        $this->gate->define('platform.resolve-dead-letter', fn (?User $user) => $this->allowsApiAbility($user, 'bus:admin'));
        $this->gate->define('platform.manage-users', fn (?User $user) => $this->allowsManageUsers($user));
        $this->gate->define('platform.manage-integrations', fn (?User $user) => $this->allowsApiAbility($user, 'integrations:admin'));
    }

    private function allowsManageUsers(?User $user): bool
    {
        if (! config('platform_auth.web_auth_enabled', true)) {
            return true;
        }

        return $this->manageUsersPolicy->manage($user);
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
            'events:publish' => $this->publishEventPolicy->publish($user),
            'bus:admin'      => $this->syncRegistryPolicy->sync($user),
            default          => $user !== null && $this->authorization->userCan($user, $ability),
        };
    }
}
