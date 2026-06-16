<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Worker;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use App\Shared\Platform\DatabaseInstanceTenantContext;

/**
 * Binds the instance tenant row before a detached simulation worker publishes events.
 */
final class SimulationWorkerTenantBootstrap
{
    public function __construct(
        private readonly InstanceTenantContextInterface $tenantContext,
    ) {}

    public function bindForTenantSlug(string $tenantSlug): void
    {
        $tenant = TenantModel::query()->where('slug', $tenantSlug)->first(['id', 'slug']);

        if ($tenant === null) {
            return;
        }

        if ($this->tenantContext instanceof DatabaseInstanceTenantContext) {
            $this->tenantContext->bindInstanceTenantId((string) $tenant->id);
        }
    }
}
