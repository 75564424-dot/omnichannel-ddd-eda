<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Middleware\Application\UseCases\SyncConfiguredModulesToRegistryUseCase;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\ClientFixtureLoader;
use App\Shared\Platform\Services\ClientSimulationService;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * SaaS control plane — prepare + run client traffic simulation for a tenant.
 */
final class TenantSimulationAutomationService
{
    public function __construct(
        private readonly ClientFixtureLoader $fixtures,
        private readonly ClientSimulationService $simulation,
        private readonly TenantModuleCatalogService $moduleCatalog,
        private readonly SyncConfiguredModulesToRegistryUseCase $syncRegistry,
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

    public function canSimulateTenant(TenantModel $tenant): bool
    {
        if ($tenant->status !== 'active') {
            return false;
        }

        if (! $this->fixtures->exists($this->resolveFixtureSlug($tenant))) {
            return false;
        }

        if (config('platform.deployment_mode') === 'instance_per_client') {
            return $tenant->slug === Str::slug((string) config('platform.client_slug', ''));
        }

        return true;
    }

    public function simulationBlockReason(TenantModel $tenant): ?string
    {
        if ($tenant->status !== 'active') {
            return 'La empresa no está activa.';
        }

        if (! $this->fixtures->exists($this->resolveFixtureSlug($tenant))) {
            return 'No hay fixture de simulación para esta empresa.';
        }

        if (config('platform.deployment_mode') === 'instance_per_client') {
            $instanceSlug = Str::slug((string) config('platform.client_slug', ''));
            if ($tenant->slug !== $instanceSlug) {
                return "Esta instancia solo simula «{$instanceSlug}» (despliegue dedicado por cliente).";
            }
        }

        return null;
    }

    /**
     * @return array{
     *     fixture_slug: string,
     *     prepared: bool,
     *     published: int,
     *     queue_matches: int,
     *     planned_total: int,
     *     events_per_minute: int,
     *     duration_minutes: int,
     *     validation_errors: list<string>
     * }
     */
    /**
     * @param callable(int, int, list<string>): void|null $onProgress  Current, total, all event ids so far
     *
     * @return array{
     *     fixture_slug: string,
     *     prepared: bool,
     *     published: int,
     *     queue_matches: int,
     *     planned_total: int,
     *     events_per_minute: int,
     *     duration_minutes: int,
     *     validation_errors: list<string>,
     *     event_ids: list<string>
     * }
     */
    public function run(
        TenantModel $tenant,
        int $eventsPerMinute,
        int $durationMinutes,
        ?int $totalEvents = null,
        bool $skipPrepare = false,
        ?callable $onProgress = null,
    ): array {
        $reason = $this->simulationBlockReason($tenant);
        if ($reason !== null) {
            throw new RuntimeException($reason);
        }

        if ($eventsPerMinute < 1 || $eventsPerMinute > 600) {
            throw new RuntimeException('Eventos por minuto debe estar entre 1 y 600.');
        }

        if ($durationMinutes < 1 || $durationMinutes > 120) {
            throw new RuntimeException('Duración debe estar entre 1 y 120 minutos.');
        }

        $plannedTotal = $eventsPerMinute * $durationMinutes;
        if ($totalEvents !== null && $totalEvents > 0 && $totalEvents !== $plannedTotal) {
            throw new RuntimeException(
                "Total de eventos ({$totalEvents}) debe coincidir con {$eventsPerMinute}/min × {$durationMinutes} min (= {$plannedTotal}).",
            );
        }

        $fixtureSlug = $this->resolveFixtureSlug($tenant);

        if (! $skipPrepare) {
            $this->prepare($tenant, $fixtureSlug);
        }

        set_time_limit(max(120, $durationMinutes * 70));

        $eventIdsAccumulator = [];

        $result = $this->simulation->simulate(
            slug: $fixtureSlug,
            events: 0,
            applyFixture: false,
            skipValidate: false,
            skipSync: false,
            eventsPerMinute: $eventsPerMinute,
            durationMinutes: $durationMinutes,
            onPublished: $onProgress !== null
                ? function (int $current, int $total, string $eventId) use ($onProgress, &$eventIdsAccumulator): void {
                    $eventIdsAccumulator[] = $eventId;
                    $onProgress($current, $total, $eventIdsAccumulator);
                }
                : null,
        );

        if ($result['validation_errors'] !== []) {
            throw new RuntimeException('Validación de catálogo: '.implode('; ', $result['validation_errors']));
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        if ($onProgress === null) {
            $settings = is_array($tenant->settings) ? $tenant->settings : [];
            $settings['last_simulation'] = [
                'ran_at'            => now()->toDateTimeString(),
                'fixture_slug'      => $fixtureSlug,
                'events_per_minute' => $eventsPerMinute,
                'duration_minutes'  => $durationMinutes,
                'planned_total'     => $plannedTotal,
                'published'         => $result['published'],
                'queue_matches'     => $result['queue_matches'],
            ];
            $settings['simulation_prepared_at'] = now()->toIso8601String();
            $settings['simulation_fixture_slug'] = $fixtureSlug;
            $tenant->update(['settings' => $settings]);
        }

        return [
            'fixture_slug'      => $fixtureSlug,
            'prepared'          => ! $skipPrepare,
            'published'         => $result['published'],
            'queue_matches'     => $result['queue_matches'],
            'planned_total'     => $plannedTotal,
            'events_per_minute' => $eventsPerMinute,
            'duration_minutes'  => $durationMinutes,
            'validation_errors' => [],
            'event_ids'         => $result['event_ids'],
        ];
    }

    private function prepare(TenantModel $tenant, string $fixtureSlug): void
    {
        if ($this->moduleCatalog->canApplyToCurrentInstance($tenant)) {
            $this->moduleCatalog->applyToCurrentInstance($tenant);
        }

        $this->fixtures->applyToRuntimeConfig($fixtureSlug);
        $this->syncRegistry->execute();
    }
}
