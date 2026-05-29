<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Prepare;

use App\Control\Application\Services\ClientDashboardMetricsCatalogService;
use App\Control\Application\Services\ClientDashboardModulesService;

/**
 * Estado de preparación para simulación E2E (sin ejecutar eventos aún).
 */
final class InstanceSimulationReadinessService
{
    public function __construct(
        private readonly ClientDashboardModulesService $modules,
        private readonly ClientDashboardMetricsCatalogService $metrics,
    ) {}

    /** @return array<string, mixed> */
    public function sharedForInertia(): array
    {
        $slug = strtolower(trim((string) config('platform.client_slug', '')));
        $simConfig   = config('platform.simulation', []);
        $fixtureSlug = is_array($simConfig) ? (string) ($simConfig['fixture_slug'] ?? 'acmepos') : 'acmepos';

        $catalog = $this->modules->presentationCatalog();
        $hasModules = $this->metrics->hasConfiguredModules();
        $visibleCount = count($catalog['producers'] ?? []) + count($catalog['subscribers'] ?? []);

        $tenantSettings = $this->modules->resolveTenant()?->settings;
        $settings = is_array($tenantSettings) ? $tenantSettings : [];
        $preparedAt = $settings['simulation_prepared_at'] ?? null;

        return [
            'enabled'            => is_array($simConfig) ? (bool) ($simConfig['enabled'] ?? true) : true,
            'fixture_slug'       => $fixtureSlug,
            'instance_slug'      => $slug,
            'has_modules'        => $hasModules,
            'visible_modules'    => $visibleCount,
            'prepared'           => is_string($preparedAt) && $preparedAt !== '',
            'prepared_at'        => is_string($preparedAt) ? $preparedAt : null,
            'prepare_command'    => "php artisan platform:simulation:prepare --slug={$fixtureSlug}",
            'run_command_hint'   => "php artisan platform:simulate-client {$fixtureSlug} --per-minute=10 --duration-minutes=1",
        ];
    }
}
