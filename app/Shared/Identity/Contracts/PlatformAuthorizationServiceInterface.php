<?php

declare(strict_types=1);

namespace App\Shared\Identity\Contracts;

use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;

interface PlatformAuthorizationServiceInterface
{
    /**
     * @return list<string>
     */
    public function abilitiesForUser(User $user): array;

    public function userCan(User $user, string $ability): bool;

    public function roleForUser(User $user): PlatformRole;
}
