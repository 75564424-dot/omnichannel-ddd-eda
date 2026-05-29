<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Orchestration;


use App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore;
use App\Simulation\Application\Services\Metrics\SimulationRunMetricsCollector;
use App\Simulation\Application\Services\Worker\SimulationWorkerEnvironmentFactory;
use App\Simulation\Application\Services\Worker\SimulationWorkerLauncher;
use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;
use App\Shared\Platform\LocalFleet\LocalFleetRegistry;
use App\Shared\Platform\LocalFleet\LocalFleetTenantMirror;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Dispatches tenant simulations to an isolated client silo (local fleet).
 */
final class LocalFleetSimulationRunner
{
    public function __construct(
        private readonly LocalFleetRegistry $registry,
        private readonly LocalFleetInstanceProvisioner $provisioner,
        private readonly LocalFleetTenantMirror $tenantMirror,
        private readonly TenantModuleCatalogService $moduleCatalog,
        private readonly SimulationRunHandoffStore $handoffStore,
        private readonly SimulationWorkerLauncher $workerLauncher,
        private readonly SimulationWorkerEnvironmentFactory $workerEnvironment,
    ) {}

    public function shouldRunOnClientSilo(TenantModel $tenant): bool
    {
        return $this->provisioner->isEnabled()
            && config('platform.control_plane', false)
            && $this->registry->isProvisioned($tenant->slug);
    }

    public function isClientSiloProcess(): bool
    {
        return ! config('platform.control_plane', false);
    }

    public function dispatchToClientSilo(string $runId): void
    {
        $run = SimulationRunModel::query()->with('tenant')->find($runId);
        if ($run === null || $run->tenant === null) {
            return;
        }

        $tenant = $run->tenant;
        $instance = $this->registry->findBySlug($tenant->slug);
        if ($instance === null) {
            throw new \RuntimeException('Instancia local no encontrada para «'.$tenant->slug.'».');
        }

        $this->tenantMirror->mirror($tenant->fresh());

        $this->handoffStore->write(
            $run,
            $tenant,
            $this->moduleCatalog->getCatalog($tenant),
        );

        $baseline = app(SimulationRunMetricsCollector::class)->captureEnvironmentBaseline();

        SimulationRunModel::query()->where('id', $runId)->update([
            'status'     => SimulationRunModel::STATUS_RUNNING,
            'started_at' => now(),
            'metrics'    => ['resources' => ['baseline_before' => $baseline]],
        ]);

        $envId = (string) ($instance['id'] ?? 'client-'.$tenant->slug);
        $envFile = base_path('.env.'.$envId);
        if (! is_file($envFile)) {
            throw new \RuntimeException("Falta el archivo de entorno del silo: .env.{$envId}");
        }

        $php = (new PhpExecutableFinder)->find(false) ?: 'php';
        $command = [
            $php,
            base_path('artisan'),
            'platform:simulation:execute-run',
            $runId,
            '--env='.$envId,
            '--no-ansi',
        ];

        $this->workerLauncher->launch(
            $runId,
            $command,
            $this->workerEnvironment->forClientSilo($envId, $tenant->slug),
            $envId,
            $tenant->slug,
        );
    }
}
