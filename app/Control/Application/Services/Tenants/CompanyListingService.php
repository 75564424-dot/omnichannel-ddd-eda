<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Tenants;

use App\Simulation\Application\Services\Execution\TenantSimulationAutomationService;
use App\Shared\Infrastructure\Models\TenantModel;

final class CompanyListingService
{
    public function __construct(
        private readonly TenantPresentationService $presentation,
        private readonly TenantSimulationAutomationService $simulationAutomation,
    ) {}

    /** @return list<array<string, mixed>> */
    public function tenantsForIndex(): array
    {
        return array_map(function (array $row) {
            $tenant = TenantModel::query()->find($row['id']);
            if ($tenant === null) {
                return $row;
            }

            return array_merge($row, [
                'can_simulate' => $this->simulationAutomation->canSimulateTenant($tenant),
                'simulate_block_reason' => $this->simulationAutomation->simulationBlockReason($tenant),
                'fixture_slug' => $this->simulationAutomation->resolveFixtureSlug($tenant),
            ]);
        }, $this->presentation->listTenants());
    }

    /** @return array<string, mixed> */
    public function simulationDefaults(): array
    {
        $defaults = config('platform.simulation.defaults', []);

        return [
            'events_per_minute' => (int) ($defaults['events_per_minute'] ?? 10),
            'duration_minutes' => (int) ($defaults['duration_minutes'] ?? 1),
        ];
    }
}
