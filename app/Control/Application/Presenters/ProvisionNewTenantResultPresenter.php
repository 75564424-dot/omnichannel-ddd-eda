<?php

declare(strict_types=1);

namespace App\Control\Application\Presenters;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetProvisionResult;

/**
 * Builds provisioning outcome messages and response payload shape.
 */
final class ProvisionNewTenantResultPresenter
{
    /**
     * @return array{tenant: TenantModel, message: string, show_deployment_guide: bool}
     */
    public function present(TenantModel $tenant, LocalFleetProvisionResult $fleetResult): array
    {
        $message = $fleetResult->provisioned
            ? 'Registro completado. Tenant e instancia aislada en '.$fleetResult->appUrl().' (login operador en esa URL).'
            : 'Registro SaaS completado. Despliegue la instancia dedicada con PLATFORM_CLIENT_SLUG='.$tenant->slug.'.';

        return [
            'tenant' => $tenant,
            'message' => $message,
            'show_deployment_guide' => ! $fleetResult->provisioned,
        ];
    }
}
