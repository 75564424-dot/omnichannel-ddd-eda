<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Control\Application\Services\SimulationRunMetricsCollector;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;
use App\Shared\Platform\LocalFleet\LocalFleetRegistry;
use App\Shared\Platform\LocalFleet\LocalFleetTenantMirror;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Runs tenant simulations in an isolated client silo (separate SQLite + portal metrics).
 */
final class LocalFleetSimulationRunner
{
    public function __construct(
        private readonly LocalFleetRegistry $registry,
        private readonly LocalFleetInstanceProvisioner $provisioner,
        private readonly LocalFleetTenantMirror $tenantMirror,
        private readonly TenantModuleCatalogService $moduleCatalog,
        private readonly SimulationRunHandoffStore $handoffStore,
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

        $logPath = storage_path('logs/simulation-worker-'.$runId.'.log');
        $clientSlug = $tenant->slug;
        $workerEnv = $this->workerEnvironment($envId, $clientSlug);
        file_put_contents(
            $logPath,
            '['.now()->toDateTimeString().'] Starting: '.implode(' ', $command).PHP_EOL
            .'  APP_ENV='.$workerEnv['APP_ENV']
            .' PLATFORM_CONTROL_PLANE='.$workerEnv['PLATFORM_CONTROL_PLANE']
            .' PLATFORM_CLIENT_SLUG='.$workerEnv['PLATFORM_CLIENT_SLUG'].PHP_EOL,
            FILE_APPEND,
        );

        if (PHP_OS_FAMILY === 'Windows') {
            $quoted = array_map(static fn (string $part): string => '"'.str_replace('"', '""', $part).'"', $command);
            $shell = 'cmd /C start /B "" '
                .'set "APP_ENV='.$envId.'"&& '
                .'set "PLATFORM_CONTROL_PLANE=false"&& '
                .'set "PLATFORM_CLIENT_SLUG='.$clientSlug.'"&& '
                .implode(' ', $quoted)
                .' >> "'.str_replace('"', '""', $logPath).'" 2>&1';
            $process = Process::fromShellCommandline($shell, base_path(), $workerEnv);
            $process->run();

            return;
        }

        $process = new Process($command, base_path(), $workerEnv);
        $process->setTimeout(null);
        $process->start(function (string $type, string $buffer) use ($logPath): void {
            file_put_contents($logPath, '['.$type.'] '.$buffer, FILE_APPEND);
        });
    }

    /**
     * Overrides inherited APP_ENV from `artisan serve` so the worker always boots the client silo.
     *
     * @return array<string, string>
     */
    private function workerEnvironment(string $envId, string $clientSlug): array
    {
        $env = [];
        foreach (array_merge($_ENV, $_SERVER) as $key => $value) {
            if (! is_string($key) || ! is_string($value) || $value === '') {
                continue;
            }
            $env[$key] = $value;
        }

        $env['APP_ENV'] = $envId;
        $env['PLATFORM_CONTROL_PLANE'] = 'false';
        $env['PLATFORM_CLIENT_SLUG'] = $clientSlug;

        return $env;
    }
}
