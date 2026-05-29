<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Tenants;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;
use App\Shared\Platform\Services\InstanceDeploymentService;

final class ProvisionNewTenantService
{
    public function __construct(
        private readonly TenantAdminService $admin,
        private readonly LocalFleetInstanceProvisioner $localFleet,
        private readonly InstanceDeploymentService $deployment,
        private readonly TenantOperatorService $operators,
    ) {}

    /**
     * @param array<string, mixed> $validated
     * @return array{tenant: TenantModel, message: string, show_deployment_guide: bool}
     */
    public function provision(array $validated): array
    {
        $profile = array_filter([
            'legal_name' => $validated['legal_name'] ?? null,
            'tax_id' => $validated['tax_id'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'country' => $validated['country'] ?? null,
            'city' => $validated['city'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'billing_email' => $validated['billing_email'] ?? null,
            'website' => $validated['website'] ?? null,
            'timezone' => $validated['timezone'] ?? 'UTC',
            'notes' => $validated['notes'] ?? null,
        ], static fn ($v) => $v !== null && $v !== '');

        $modules = array_values(array_unique($validated['modules']));
        if (! in_array('middleware', $modules, true)) {
            $modules[] = 'middleware';
        }

        $tenant = $this->admin->create(
            $validated['company_name'],
            $validated['slug'],
            $validated['plan'],
            $profile,
            $modules,
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
            $settings = is_array($tenant->settings) ? $tenant->settings : [];
            $settings['primary_admin_email'] = $validated['admin_email'];
            $settings['app_url'] = $this->deployment->presentationForTenant($tenant)['recommended_app_url'];
            $settings['deployment'] = [
                'mode' => 'instance_per_client',
                'status' => 'pending_dedicated_instance',
                'required_client_slug' => $tenant->slug,
                'provisioned_at' => now()->toIso8601String(),
            ];
            $tenant->update(['settings' => $settings]);
        }

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
