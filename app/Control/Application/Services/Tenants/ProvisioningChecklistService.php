<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Tenants;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;

final class ProvisioningChecklistService
{
    public function __construct(
        private readonly LocalFleetInstanceProvisioner $localFleet,
    ) {}

    /** @return list<array<string, mixed>> */
    public function checklist(): array
    {
        $tenantCount = TenantModel::query()->count();
        $hasAdmin = User::query()->where('platform_role', 'platform_admin')->exists();

        return [
            ['key' => 'tenant', 'label' => 'Crear tenant', 'done' => $tenantCount > 0, 'detail' => "{$tenantCount} en registro"],
            ['key' => 'schemas', 'label' => 'Schemas / migraciones', 'done' => true, 'detail' => 'Base local migrada'],
            ['key' => 'modules', 'label' => 'Catálogo comercial', 'done' => true, 'detail' => 'config/saas_catalog.php'],
            ['key' => 'admin', 'label' => 'Admin principal instancia', 'done' => $hasAdmin, 'detail' => $hasAdmin ? 'platform_admin existe' : 'pendiente'],
            ['key' => 'api_keys', 'label' => 'API keys M2M', 'done' => (string) config('security.api_keys') !== '', 'detail' => 'PLATFORM_API_KEYS o artisan platform:issue-api-token'],
            ['key' => 'infra', 'label' => 'Instancia aislada local', 'done' => $this->localFleetInfraDone(), 'detail' => $this->localFleetInfraDetail()],
        ];
    }

    private function localFleetInfraDone(): bool
    {
        if ($this->localFleet->isEnabled()) {
            return $this->localFleet->allClientTenantsProvisioned();
        }

        return env('DOCKER_APP_ROLE') !== null;
    }

    private function localFleetInfraDetail(): string
    {
        if ($this->localFleet->isEnabled()) {
            return $this->localFleet->allClientTenantsProvisioned()
                ? 'Fleet local: todos los tenants con silo'
                : 'Pendiente: php artisan platform:fleet:sync-local';
        }

        return env('DOCKER_APP_ROLE') ? 'Docker role: '.env('DOCKER_APP_ROLE') : 'Entorno local artisan';
    }
}
