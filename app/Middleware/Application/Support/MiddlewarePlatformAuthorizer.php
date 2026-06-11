<?php

declare(strict_types=1);

namespace App\Middleware\Application\Support;

use Illuminate\Contracts\Auth\Access\Gate;

final class MiddlewarePlatformAuthorizer
{
    public function __construct(
        private readonly Gate $gate,
    ) {}

    public function authorizePublish(): void
    {
        $this->gate->authorize('platform.publish');
    }

    public function authorizeResolveDeadLetter(): void
    {
        $this->gate->authorize('platform.resolve-dead-letter');
    }

    public function authorizeSyncRegistry(): void
    {
        $this->gate->authorize('platform.sync-registry');
    }
}
