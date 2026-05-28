<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Control\Application\Services\ClientDashboardModulesService;
use App\Control\Application\Services\TenantModuleCatalogService;
use App\Middleware\Application\UseCases\SyncConfiguredModulesToRegistryUseCase;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\ClientFixtureLoader;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Prepara la instancia actual para simulación (catálogo + registry) sin publicar eventos.
 */
final class PrepareSimulationCommand extends Command
{
    protected $signature = 'platform:simulation:prepare
                            {--slug=acmepos : Fixture slug to validate against}
                            {--apply-fixture : Copy fixture into config/modules before sync}';

    protected $description = 'Prepare Acme instance: sync module registry and mark simulation ready (no events published)';

    public function handle(
        ClientFixtureLoader $fixtures,
        TenantModuleCatalogService $catalogService,
        SyncConfiguredModulesToRegistryUseCase $syncRegistry,
        ClientDashboardModulesService $modules,
    ): int {
        $fixtureSlug = strtolower(trim((string) $this->option('slug')));
        $instanceSlug = Str::slug((string) config('platform.client_slug', ''));

        if ($instanceSlug === '') {
            $this->error('PLATFORM_CLIENT_SLUG is not set.');

            return self::FAILURE;
        }

        if ($this->option('apply-fixture')) {
            if (! $fixtures->exists($fixtureSlug)) {
                $this->error("Fixture [{$fixtureSlug}] not found.");

                return self::FAILURE;
            }
            $fixtures->applyToFilesystem($fixtureSlug);
            $this->warn('Fixture applied to config/. Run config:clear if cached.');
        }

        $tenant = TenantModel::query()->where('slug', $instanceSlug)->first();
        if ($tenant === null) {
            $this->error("Tenant not found for slug [{$instanceSlug}]. Run InstanceTenantSeeder.");

            return self::FAILURE;
        }

        if ($catalogService->canApplyToCurrentInstance($tenant)) {
            $catalogService->applyToCurrentInstance($tenant);
            $this->info('modules_config.json updated from tenant catalog.');
        }

        $sync = $syncRegistry->execute();
        $this->info(sprintf(
            'Registry sync — producers: %d, consumers: %d',
            $sync['producer_bindings'] ?? 0,
            $sync['consumer_bindings'] ?? 0,
        ));

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['simulation_prepared_at'] = now()->toIso8601String();
        $settings['simulation_fixture_slug'] = $fixtureSlug;
        $tenant->update(['settings' => $settings]);

        $moduleCount = count($modules->presentationCatalog()['available_producers'] ?? [])
            + count($modules->presentationCatalog()['available_subscribers'] ?? []);

        $this->info("Simulation prepared for [{$instanceSlug}] ({$moduleCount} modules in catalog).");
        $this->line('When ready (10 evt/min, 1 min): php artisan platform:simulate-client '.$fixtureSlug.' --per-minute=10 --duration-minutes=1');
        $this->line('Burst smoke (10 at once): php artisan platform:simulate-client '.$fixtureSlug.' --events=10');

        return self::SUCCESS;
    }
}
