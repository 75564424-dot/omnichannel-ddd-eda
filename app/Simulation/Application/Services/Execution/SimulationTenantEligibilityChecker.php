<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Execution;

use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Dashboard\Application\Services\ModuleActivationGateService;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;
use App\Shared\Platform\Services\TenantCatalogSampleEventBuilder;
use Illuminate\Support\Str;

final class SimulationTenantEligibilityChecker
{
    public function __construct(
        private readonly TenantModuleCatalogService $moduleCatalog,
        private readonly TenantCatalogSampleEventBuilder $sampleEventBuilder,
        private readonly LocalFleetInstanceProvisioner $fleetProvisioner,
        private readonly ModuleActivationGateService $activationGate,
    ) {}

    public function canSimulateTenant(TenantModel $tenant): bool
    {
        return $this->simulationBlockReason($tenant) === null;
    }

    public function simulationBlockReason(TenantModel $tenant): ?string
    {
        if ($tenant->status !== 'active') {
            return 'La empresa no está activa.';
        }

        $catalog = $this->moduleCatalog->storedCatalog($tenant);
        if ($catalog === null) {
            return 'No hay catálogo de módulos configurado explícitamente para esta empresa.';
        }

        $producers = $catalog['producers'] ?? [];
        if (! is_array($producers) || $producers === []) {
            return 'El catálogo no define productores; solo middleware no es suficiente para simular.';
        }

        if ($this->sampleEventBuilder->fromCatalog($catalog) === []) {
            return 'Ningún productor define tipos de evento emitidos (event_types_emitted).';
        }

        if (! config('platform.control_plane', false)) {
            $activationBlock = $this->activationGate->simulationBlockReason($catalog);
            if ($activationBlock !== null) {
                return $activationBlock;
            }
        }

        if ($this->fleetProvisioner->isEnabled()) {
            $settings = is_array($tenant->settings) ? $tenant->settings : [];
            $localInstance = $settings['deployment']['local_instance'] ?? null;
            if (! is_array($localInstance) || empty($localInstance['app_url'])) {
                return 'La empresa no tiene silo moderno provisionado (sin local_instance.app_url).';
            }

            if (! $this->fleetProvisioner->isProvisioned($tenant)) {
                return 'La empresa no está registrada en la flota local.';
            }
        }

        if ($this->restrictsSimulationToCurrentClientSilo()) {
            $instanceSlug = Str::slug((string) config('platform.client_slug', ''));
            if ($tenant->slug !== $instanceSlug) {
                return "Esta instancia solo simula «{$instanceSlug}» (despliegue dedicado por cliente).";
            }
        }

        return null;
    }

    private function restrictsSimulationToCurrentClientSilo(): bool
    {
        return config('platform.deployment_mode') === 'instance_per_client'
            && ! config('platform.control_plane', false);
    }
}
