<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Execution;

use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Middleware\Application\UseCases\SyncConfiguredModulesToRegistryUseCase;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\ClientFixtureLoader;
use App\Shared\Platform\Services\TenantCatalogRuntimeConfigurator;
use App\Shared\Platform\Services\TenantCatalogSampleEventBuilder;
use App\Simulation\Application\Services\Prepare\SimulationTenantSettingsSync;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * SaaS control plane — prepare + run client traffic simulation for a tenant.
 */
final class TenantSimulationAutomationService
{
    public function __construct(
        private readonly ClientFixtureLoader $fixtures,
        private readonly TenantModuleCatalogService $moduleCatalog,
        private readonly SyncConfiguredModulesToRegistryUseCase $syncRegistry,
        private readonly TenantCatalogSampleEventBuilder $sampleEventBuilder,
        private readonly TenantCatalogRuntimeConfigurator $catalogConfigurator,
        private readonly ClientSiloSimulationExecutor $clientSiloExecutor,
        private readonly SimulationTenantSettingsSync $tenantSettingsSync,
        private readonly SimulationFixtureResolver $fixtureResolver,
        private readonly SimulationTenantEligibilityChecker $eligibilityChecker,
    ) {}

    public function resolveFixtureSlug(TenantModel $tenant): string
    {
        return $this->fixtureResolver->resolveFixtureSlug($tenant);
    }

    public function canSimulateTenant(TenantModel $tenant): bool
    {
        return $this->eligibilityChecker->canSimulateTenant($tenant);
    }

    public function simulationBlockReason(TenantModel $tenant): ?string
    {
        return $this->eligibilityChecker->simulationBlockReason($tenant);
    }

    /**
     * @param callable(int, int): void|null $onProgress
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
        $this->assertRates($eventsPerMinute, $durationMinutes);
        $plannedTotal = $this->resolvePlannedTotal($eventsPerMinute, $durationMinutes, $totalEvents);

        $fixtureSlug = $this->fixtureResolver->resolveFixtureSlug($tenant);
        $catalog = $this->moduleCatalog->getCatalog($tenant);
        $templates = $this->fixtureResolver->resolveSampleTemplates($tenant, $catalog);

        if (! $skipPrepare) {
            $this->prepare($tenant, $fixtureSlug, $catalog);
        }

        $result = $this->clientSiloExecutor->execute(
            fixtureSlug: $fixtureSlug,
            templates: $templates,
            eventsPerMinute: $eventsPerMinute,
            durationMinutes: $durationMinutes,
            skipSync: false,
            onProgress: $onProgress,
        );

        if ($onProgress === null) {
            $this->persistLastSimulation($tenant, $fixtureSlug, $eventsPerMinute, $durationMinutes, $plannedTotal, $result);
        }

        return $this->buildResult($fixtureSlug, ! $skipPrepare, $eventsPerMinute, $durationMinutes, $plannedTotal, $result);
    }

    /**
     * @param array<string, mixed> $modulesCatalog
     * @param callable(int, int): void|null $onProgress
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
    public function runOnClientSilo(
        string $tenantSlug,
        array $modulesCatalog,
        int $eventsPerMinute,
        int $durationMinutes,
        ?int $totalEvents = null,
        bool $skipPrepare = false,
        ?callable $onProgress = null,
    ): array {
        $instanceSlug = Str::slug((string) config('platform.client_slug', ''));
        if ($instanceSlug === '' || $instanceSlug !== Str::slug($tenantSlug)) {
            throw new RuntimeException('El silo cliente no coincide con el tenant de la simulación.');
        }

        $this->assertRates($eventsPerMinute, $durationMinutes);
        $plannedTotal = $this->resolvePlannedTotal($eventsPerMinute, $durationMinutes, $totalEvents);

        $templates = $this->sampleEventBuilder->fromCatalog($modulesCatalog);
        if ($templates === []) {
            throw new RuntimeException('El catálogo del tenant no define tipos de evento en productores.');
        }

        if (! $skipPrepare) {
            $this->catalogConfigurator->apply($modulesCatalog);
            $this->syncRegistry->execute();
        }

        $result = $this->clientSiloExecutor->execute(
            fixtureSlug: 'tenant-catalog',
            templates: $templates,
            eventsPerMinute: $eventsPerMinute,
            durationMinutes: $durationMinutes,
            skipSync: $skipPrepare,
            onProgress: $onProgress,
        );

        return $this->buildResult('tenant-catalog', ! $skipPrepare, $eventsPerMinute, $durationMinutes, $plannedTotal, $result);
    }

    private function assertRates(int $eventsPerMinute, int $durationMinutes): void
    {
        if ($eventsPerMinute < 1 || $eventsPerMinute > 600) {
            throw new RuntimeException('Eventos por minuto debe estar entre 1 y 600.');
        }

        if ($durationMinutes < 1 || $durationMinutes > 120) {
            throw new RuntimeException('Duración debe estar entre 1 y 120 minutos.');
        }
    }

    private function resolvePlannedTotal(int $eventsPerMinute, int $durationMinutes, ?int $totalEvents): int
    {
        $plannedTotal = $eventsPerMinute * $durationMinutes;
        if ($totalEvents !== null && $totalEvents > 0 && $totalEvents !== $plannedTotal) {
            throw new RuntimeException(
                "Total de eventos ({$totalEvents}) debe coincidir con {$eventsPerMinute}/min × {$durationMinutes} min (= {$plannedTotal}).",
            );
        }

        return $plannedTotal;
    }

    /**
     * @param array<string, mixed> $catalog
     */
    private function prepare(TenantModel $tenant, string $fixtureSlug, array $catalog): void
    {
        if ($this->moduleCatalog->canApplyToCurrentInstance($tenant)) {
            $this->moduleCatalog->applyToCurrentInstance($tenant);
            $this->catalogConfigurator->apply($this->moduleCatalog->getCatalog($tenant));
        } elseif ($this->sampleEventBuilder->fromCatalog($catalog) !== []) {
            $this->catalogConfigurator->apply($catalog);
        } else {
            $this->fixtures->applyToRuntimeConfig($fixtureSlug);
        }

        $this->syncRegistry->execute();
    }

    /**
     * @param array{published: int, queue_matches: int, event_ids: list<string>} $result
     */
    private function persistLastSimulation(
        TenantModel $tenant,
        string $fixtureSlug,
        int $eventsPerMinute,
        int $durationMinutes,
        int $plannedTotal,
        array $result,
    ): void {
        $this->tenantSettingsSync->recordInlineSummary(
            $tenant,
            $fixtureSlug,
            $eventsPerMinute,
            $durationMinutes,
            $plannedTotal,
            [
                'published'     => $result['published'],
                'queue_matches' => $result['queue_matches'],
            ],
        );
        $this->tenantSettingsSync->recordPrepared($tenant, $fixtureSlug);
    }

    /**
     * @param array{published: int, queue_matches: int, event_ids: list<string>, validation_errors: list<string>} $result
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
    private function buildResult(
        string $fixtureSlug,
        bool $prepared,
        int $eventsPerMinute,
        int $durationMinutes,
        int $plannedTotal,
        array $result,
    ): array {
        return [
            'fixture_slug'      => $fixtureSlug,
            'prepared'          => $prepared,
            'published'         => $result['published'],
            'queue_matches'     => $result['queue_matches'],
            'planned_total'     => $plannedTotal,
            'events_per_minute' => $eventsPerMinute,
            'duration_minutes'  => $durationMinutes,
            'validation_errors' => $result['validation_errors'],
            'event_ids'         => $result['event_ids'],
        ];
    }
}
