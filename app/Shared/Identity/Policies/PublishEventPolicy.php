<?php

declare(strict_types=1);

namespace App\Shared\Identity\Policies;

use App\Models\User;
use App\Shared\Identity\Contracts\PlatformAuthorizationServiceInterface;

final class PublishEventPolicy
{
    public function __construct(
        private readonly PlatformAuthorizationServiceInterface $authorization,
    ) {}

    public function publish(?User $user): bool
    {
        return $user !== null && $this->authorization->userCan($user, 'events:publish');
    }
}
