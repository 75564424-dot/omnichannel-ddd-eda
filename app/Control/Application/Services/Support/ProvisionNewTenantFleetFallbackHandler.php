<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Support;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\InstanceDeploymentService;

/**
 * Persists pending-deployment settings when local fleet provisioning is unavailable.
 */
final class ProvisionNewTenantFleetFallbackHandler
{
    public function __construct(
        private readonly InstanceDeploymentService $deployment,
    ) {}

    /**
     * @param array<string, mixed> $validated
     */
    public function applyPendingDeploymentSettings(TenantModel $tenant, array $validated): void
    {
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
}
