<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Tenants;

use App\Control\Application\Presenters\ProvisionNewTenantResultPresenter;
use App\Control\Application\Services\Support\ProvisionNewTenantFleetFallbackHandler;
use App\Control\Application\Services\Support\ProvisionNewTenantInputMapper;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;

final class ProvisionNewTenantService
{
    public function __construct(
        private readonly TenantAdminService $admin,
        private readonly LocalFleetInstanceProvisioner $localFleet,
        private readonly TenantOperatorService $operators,
        private readonly ProvisionNewTenantInputMapper $inputMapper,
        private readonly ProvisionNewTenantFleetFallbackHandler $fleetFallback,
        private readonly ProvisionNewTenantResultPresenter $resultPresenter,
    ) {}

    /**
     * @param array<string, mixed> $validated
     *
     * @return array{tenant: TenantModel, message: string, show_deployment_guide: bool}
     */
    public function provision(array $validated): array
    {
        $mapped = $this->inputMapper->map($validated);

        $tenant = $this->admin->create(
            $validated['company_name'],
            $validated['slug'],
            $validated['plan'],
            $mapped['profile'],
            $mapped['modules'],
        );

        $this->operators->createOperator(
            $tenant,
            $validated['admin_name'],
            $validated['admin_email'],
            $validated['admin_password'],
            'platform_admin',
        );

        $fleetResult = $this->localFleet->provision($tenant->fresh(), [
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => $validated['admin_password'],
        ]);

        if (! $fleetResult->provisioned) {
            $this->fleetFallback->applyPendingDeploymentSettings($tenant, $validated);
        }

        return $this->resultPresenter->present($tenant, $fleetResult);
    }
}
