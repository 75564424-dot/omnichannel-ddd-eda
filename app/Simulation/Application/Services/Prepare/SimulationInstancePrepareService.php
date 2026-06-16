<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Prepare;

use App\Control\Application\Services\ClientDashboardModulesService;
use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Middleware\Application\UseCases\SyncConfiguredModulesToRegistryUseCase;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\ClientFixtureLoader;
use Illuminate\Support\Str;

final class SimulationInstancePrepareService
{
    public function __construct(
        private readonly ClientFixtureLoader $fixtures,
        private readonly TenantModuleCatalogService $catalogService,
        private readonly SyncConfiguredModulesToRegistryUseCase $syncRegistry,
        private readonly ClientDashboardModulesService $modules,
    ) {}

    /**
     * @return array{
     *     instance_slug: string,
     *     fixture_slug: string,
     *     producer_bindings: int,
     *     consumer_bindings: int,
     *     module_count: int,
     *     catalog_applied: bool
     * }
     */
    public function prepare(string $fixtureSlug, bool $applyFixture): array
    {
        $fixtureSlug = strtolower(trim($fixtureSlug));
        $instanceSlug = Str::slug((string) config('platform.client_slug', ''));

        if ($instanceSlug === '') {
            throw new \RuntimeException('PLATFORM_CLIENT_SLUG is not set.');
        }

        if ($applyFixture) {
            if (! $this->fixtures->exists($fixtureSlug)) {
                throw new \RuntimeException("Fixture [{$fixtureSlug}] not found.");
            }
            $this->fixtures->applyToFilesystem($fixtureSlug);
        }

        $tenant = TenantModel::query()->where('slug', $instanceSlug)->first();
        if ($tenant === null) {
            throw new \RuntimeException("Tenant not found for slug [{$instanceSlug}]. Run InstanceTenantSeeder.");
        }

        $catalogApplied = false;
        if ($this->catalogService->canApplyToCurrentInstance($tenant)) {
            $this->catalogService->applyToCurrentInstance($tenant);
            $catalogApplied = true;
        }

        $sync = $this->syncRegistry->execute();

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['simulation_prepared_at'] = now()->toIso8601String();
        $settings['simulation_fixture_slug'] = $fixtureSlug;
        $tenant->update(['settings' => $settings]);

        $catalog = $this->modules->presentationCatalog();

        return [
            'instance_slug' => $instanceSlug,
            'fixture_slug' => $fixtureSlug,
            'producer_bindings' => (int) ($sync['producer_bindings'] ?? 0),
            'consumer_bindings' => (int) ($sync['consumer_bindings'] ?? 0),
            'module_count' => count($catalog['available_producers'] ?? [])
                + count($catalog['available_subscribers'] ?? []),
            'catalog_applied' => $catalogApplied,
        ];
    }
}
