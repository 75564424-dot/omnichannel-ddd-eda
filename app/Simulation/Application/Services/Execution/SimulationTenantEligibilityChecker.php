<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Execution;

use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Support\Str;

final class SimulationTenantEligibilityChecker
{
    public function __construct(
        private readonly SimulationFixtureResolver $fixtureResolver,
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

        if (! $this->fixtureResolver->hasSimulationSource($tenant)) {
            return 'No hay catálogo de módulos ni fixture de simulación para esta empresa.';
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
