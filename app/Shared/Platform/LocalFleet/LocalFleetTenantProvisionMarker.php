<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use App\Shared\Infrastructure\Models\TenantModel;

final class LocalFleetTenantProvisionMarker
{
    /**
     * @param array<string, mixed> $localInstance
     */
    public function markProvisioned(TenantModel $tenant, array $localInstance, string $adminEmail): void
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['primary_admin_email'] = $adminEmail;
        $settings['app_url'] = $localInstance['app_url'];
        $settings['deployment'] = [
            'mode'                 => 'instance_per_client',
            'status'               => 'active_on_instance',
            'lifecycle'            => 'provisioned',
            'required_client_slug' => $tenant->slug,
            'local_instance'       => $localInstance,
            'provisioned_at'       => now()->toIso8601String(),
        ];

        $tenant->update(['settings' => $settings]);
    }
}
