<?php

declare(strict_types=1);

namespace App\Integration\Application\Support;

use Illuminate\Contracts\Auth\Access\Gate;

final class IntegrationManagementAuthorizer
{
    public function __construct(
        private readonly Gate $gate,
    ) {}

    public function authorizeManageIntegrations(): void
    {
        $this->gate->authorize('platform.manage-integrations');
    }
}
