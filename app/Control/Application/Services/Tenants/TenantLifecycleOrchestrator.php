<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Tenants;

use App\Control\Application\UseCases\Lifecycle\StartTenantServiceUseCase;
use App\Control\Application\UseCases\Lifecycle\SuspendTenantServiceUseCase;
use App\Control\Application\UseCases\Lifecycle\RestoreTenantServiceUseCase;
use App\Control\Domain\Policies\TenantLifecyclePolicy;
use App\Shared\Infrastructure\Models\TenantModel;

final class TenantLifecycleOrchestrator
{
    public function __construct(
        private readonly StartTenantServiceUseCase $startUseCase,
        private readonly SuspendTenantServiceUseCase $suspendUseCase,
        private readonly RestoreTenantServiceUseCase $restoreUseCase,
    ) {}

    public function start(TenantModel $tenant): void
    {
        $this->startUseCase->execute($tenant);
    }

    public function suspend(TenantModel $tenant): void
    {
        $this->suspendUseCase->execute($tenant);
    }

    public function restore(TenantModel $tenant): void
    {
        $this->restoreUseCase->execute($tenant);
    }

    /**
     * Get the lifecycle status of a tenant and the actions available on the UI.
     *
     * @return array{lifecycle: string, status: string, actions_available: list<string>}
     */
    public function lifecycleStatus(TenantModel $tenant): array
    {
        $lifecycle = TenantLifecyclePolicy::inferLifecycle($tenant);
        $status = $tenant->status;
        $actions = [];

        if (TenantLifecyclePolicy::canStart($status, $lifecycle) && $lifecycle !== 'running') {
            $actions[] = 'start';
        }

        if (TenantLifecyclePolicy::canSuspend($status, $lifecycle)) {
            $actions[] = 'suspend';
        }

        if (TenantLifecyclePolicy::canRestore($status, $lifecycle)) {
            $actions[] = 'restore';
        }

        return [
            'lifecycle' => $lifecycle,
            'status' => $status,
            'actions_available' => $actions,
        ];
    }
}
