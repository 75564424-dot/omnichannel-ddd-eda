<?php

declare(strict_types=1);

namespace App\Shared\Identity\Services;

use App\Models\User;
use App\Shared\Identity\Contracts\PlatformAuthorizationServiceInterface;
use App\Shared\Identity\Domain\PlatformRole;

final class PlatformAuthorizationService implements PlatformAuthorizationServiceInterface
{
    public function abilitiesForUser(User $user): array
    {
        $role = $this->roleForUser($user);

        /** @var array<string, list<string>> $matrix */
        $matrix = config('platform_roles.roles', []);

        return $matrix[$role->value] ?? [];
    }

    public function userCan(User $user, string $ability): bool
    {
        return in_array($ability, $this->abilitiesForUser($user), true);
    }

    public function roleForUser(User $user): PlatformRole
    {
        $raw = $user->getAttribute('platform_role');
        $role = PlatformRole::tryFromString(is_string($raw) ? $raw : null);

        if ($role !== null) {
            return $role;
        }

        $default = (string) config('platform_roles.default_role', PlatformRole::PlatformAdmin->value);

        return PlatformRole::tryFrom($default) ?? PlatformRole::PlatformAdmin;
    }
}
