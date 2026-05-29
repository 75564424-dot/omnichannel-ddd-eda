<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Execution;

use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\ClientFixtureLoader;
use App\Shared\Platform\Services\TenantCatalogSampleEventBuilder;
use RuntimeException;

final class SimulationFixtureResolver
{
    public function __construct(
        private readonly ClientFixtureLoader $fixtures,
        private readonly TenantModuleCatalogService $moduleCatalog,
        private readonly TenantCatalogSampleEventBuilder $sampleEventBuilder,
    ) {}

    public function resolveFixtureSlug(TenantModel $tenant): string
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $fromSettings = $settings['simulation_fixture_slug'] ?? null;
        if (is_string($fromSettings) && $fromSettings !== '' && $this->fixtures->exists($fromSettings)) {
            return $fromSettings;
        }

        $map = config('platform.simulation.tenant_fixture_map', []);
        if (is_array($map) && isset($map[$tenant->slug]) && $this->fixtures->exists((string) $map[$tenant->slug])) {
            return (string) $map[$tenant->slug];
        }

        $default = (string) config('platform.simulation.fixture_slug', 'acmepos');

        return $this->fixtures->exists($default) ? $default : $tenant->slug;
    }

    public function hasSimulationSource(TenantModel $tenant): bool
    {
        if ($this->fixtures->exists($this->resolveFixtureSlug($tenant))) {
            return true;
        }

        return $this->sampleEventBuilder->fromCatalog($this->moduleCatalog->getCatalog($tenant)) !== [];
    }

    /**
     * @param array<string, mixed> $catalog
     *
     * @return list<array<string, mixed>>
     */
    public function resolveSampleTemplates(TenantModel $tenant, array $catalog): array
    {
        $fromCatalog = $this->sampleEventBuilder->fromCatalog($catalog);
        if ($fromCatalog !== []) {
            return $fromCatalog;
        }

        $fixtureSlug = $this->resolveFixtureSlug($tenant);
        if ($this->fixtures->exists($fixtureSlug)) {
            return $this->fixtures->loadSampleEvents($fixtureSlug);
        }

        throw new RuntimeException('No hay eventos de muestra para simular.');
    }
}
