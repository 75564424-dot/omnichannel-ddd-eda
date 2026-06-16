<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetEnvBuilder;
use App\Shared\Platform\LocalFleet\LocalFleetRegistry;
use App\Simulation\Application\Services\Reset\SimulationRunsResetService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Resets local fleet runtime artifacts for reproducible audits (control plane + client silos).
 */
final class LocalEnvironmentResetService
{
    public function __construct(
        private readonly SimulationRunsResetService $simulationReset,
        private readonly OperationalDataResetService $operationalReset,
        private readonly LocalFleetRegistry $fleetRegistry,
        private readonly LocalFleetEnvBuilder $envBuilder,
    ) {}

    /**
     * @return list<string>
     */
    public function reset(bool $purgeTenants, bool $keepSimulationHistory): array
    {
        $lines = [];

        if (! $this->simulationReset->isControlPlaneHost()) {
            throw new \RuntimeException('Run on control plane (--env=control-plane).');
        }

        if ($purgeTenants) {
            $lines = array_merge($lines, $this->purgeClientTenants());
        }

        if (! $keepSimulationHistory) {
            $sim = $this->simulationReset->reset(failStale: true, onlyStale: false);
            if (($sim['stale_failed'] ?? 0) > 0) {
                $lines[] = 'Simulaciones colgadas marcadas como fallidas: '.$sim['stale_failed'];
            }
            if (($sim['rows_deleted'] ?? 0) > 0) {
                $lines[] = 'Filas simulation_runs eliminadas: '.$sim['rows_deleted'];
            }
        } else {
            $sim = $this->simulationReset->reset(failStale: true, onlyStale: true);
            if (($sim['stale_failed'] ?? 0) > 0) {
                $lines[] = 'Simulaciones colgadas marcadas como fallidas: '.$sim['stale_failed'];
            }
        }

        $lines = array_merge($lines, $this->purgeSimulationRuntimeFiles($sim['handoff_purged'] ?? 0));
        $lines = array_merge($lines, $this->clearTenantLastSimulationMarkers());
        $lines = array_merge($lines, $this->operationalReset->reset(withQueues: true, withCache: true, withSessions: false));

        foreach ($this->fleetRegistry->clientInstances() as $instance) {
            $slug = Str::slug((string) ($instance['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }
            $lines = array_merge($lines, $this->resetClientSiloOperationalData($slug));
        }

        return $lines;
    }

    /** @return list<string> */
    private function clearTenantLastSimulationMarkers(): array
    {
        $cleared = 0;
        foreach (TenantModel::query()->get(['id', 'settings']) as $tenant) {
            $settings = is_array($tenant->settings) ? $tenant->settings : [];
            if (! array_key_exists('last_simulation', $settings)) {
                continue;
            }
            unset($settings['last_simulation']);
            $tenant->update(['settings' => $settings]);
            $cleared++;
        }

        return $cleared > 0 ? ["Marcadores last_simulation eliminados en {$cleared} tenant(s)."] : [];
    }

    /**
     * @return list<string>
     */
    private function purgeClientTenants(): array
    {
        $lines = [];
        $controlPlaneSlug = Str::slug((string) config('platform.client_slug', 'platform'));

        $slugs = collect($this->fleetRegistry->clientInstances())
            ->pluck('slug')
            ->merge(
                TenantModel::query()
                    ->pluck('slug')
                    ->map(fn ($slug) => Str::slug((string) $slug)),
            )
            ->map(fn ($slug) => Str::slug((string) $slug))
            ->filter(fn (string $slug) => $slug !== '' && $slug !== $controlPlaneSlug)
            ->unique()
            ->values();

        $purgedTenantIds = [];

        foreach (TenantModel::withTrashed()->get() as $tenant) {
            if (Str::slug($tenant->slug) === $controlPlaneSlug) {
                continue;
            }

            $purgedTenantIds[] = $tenant->id;
        }

        if ($purgedTenantIds !== [] && Schema::hasTable('client_incident_reports')) {
            $reports = (int) DB::table('client_incident_reports')
                ->whereIn('tenant_id', $purgedTenantIds)
                ->delete();
            if ($reports > 0) {
                $lines[] = "Reportes de incidentes eliminados: {$reports}";
            }
        }

        foreach (TenantModel::withTrashed()->get() as $tenant) {
            if (Str::slug($tenant->slug) === $controlPlaneSlug) {
                continue;
            }

            User::query()->where('tenant_id', $tenant->id)->delete();
            $slug = Str::slug($tenant->slug);
            $tenant->forceDelete();
            $lines[] = "Tenant CP eliminado (hard): {$slug}";
        }

        foreach ($slugs as $slug) {
            $lines = array_merge($lines, $this->removeClientArtifacts($slug));
        }

        $lines = array_merge($lines, $this->removeLooseClientArtifacts($controlPlaneSlug));

        $this->fleetRegistry->replaceInstances([]);
        $lines[] = 'fleet-registry.json: instancias cliente vaciadas.';

        return $lines;
    }

    /** @return list<string> */
    private function removeClientArtifacts(string $slug): array
    {
        $lines = [];
        $slug = Str::slug($slug);
        if ($slug === '') {
            return $lines;
        }

        $envPath = base_path($this->envBuilder->envFileName($slug));
        if (is_file($envPath)) {
            $lines = array_merge($lines, $this->safeUnlink($envPath));
        }

        $dbPath = base_path('database/instances/'.$slug.'.sqlite');
        foreach ([$dbPath, $dbPath.'-shm', $dbPath.'-wal'] as $path) {
            if (is_file($path)) {
                $lines = array_merge($lines, $this->safeUnlink($path));
            }
        }

        $modulesDir = config_path('modules/instances/'.$slug);
        if (is_dir($modulesDir)) {
            foreach (glob($modulesDir.'/*') ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($modulesDir);
            $lines[] = "Eliminado {$modulesDir}";
        }

        return $lines;
    }

    /** @return list<string> */
    private function removeLooseClientArtifacts(string $controlPlaneSlug): array
    {
        $lines = [];

        foreach (glob(base_path('.env.client-*')) ?: [] as $envPath) {
            if (is_file($envPath)) {
                $lines = array_merge($lines, $this->safeUnlink($envPath, basename($envPath)));
            }
        }

        foreach (glob(base_path('database/instances/*.sqlite*')) ?: [] as $dbPath) {
            $base = basename($dbPath);
            if (Str::startsWith($base, $controlPlaneSlug.'.sqlite')) {
                continue;
            }
            if (is_file($dbPath)) {
                $lines = array_merge($lines, $this->safeUnlink($dbPath, 'database/instances/'.$base));
            }
        }

        $instancesModulesRoot = config_path('modules/instances');
        if (is_dir($instancesModulesRoot)) {
            foreach (glob($instancesModulesRoot.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
                foreach (glob($dir.'/*') ?: [] as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                if (is_dir($dir)) {
                    rmdir($dir);
                    $lines[] = 'Eliminado '.str_replace(base_path().DIRECTORY_SEPARATOR, '', $dir);
                }
            }
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function purgeSimulationRuntimeFiles(int $handoffPurged): array
    {
        $lines = [];
        if ($handoffPurged > 0) {
            $lines[] = "Handoff purgados: {$handoffPurged}";
        }

        foreach (glob(storage_path('logs/simulation-*.log')) ?: [] as $file) {
            if (is_file($file) && unlink($file)) {
                $lines[] = 'Eliminado log: '.basename($file);
            }
        }

        $launcherDir = storage_path('app/simulation-launchers');
        foreach (glob($launcherDir.DIRECTORY_SEPARATOR.'*.bat') ?: [] as $file) {
            if (is_file($file) && unlink($file)) {
                $lines[] = 'Eliminado launcher: '.basename($file);
            }
        }

        $pulse = storage_path('app/simulation-pulse.json');
        if (is_file($pulse)) {
            unlink($pulse);
            $lines[] = 'Eliminado simulation-pulse.json';
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function resetClientSiloOperationalData(string $slug): array
    {
        $dbPath = $this->envBuilder->ensureSqliteFile($slug);
        if (! is_file($dbPath)) {
            return ["Silo {$slug}: sin SQLite (omitido)."];
        }

        Config::set('database.connections.local_reset_silo', [
            'driver'                  => 'sqlite',
            'database'                => $dbPath,
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]);

        $connection = DB::connection('local_reset_silo');
        $lines = ["Silo {$slug}: limpiando tablas operativas…"];

        Schema::connection('local_reset_silo')->disableForeignKeyConstraints();

        try {
            foreach ($this->operationalTableNames() as $table) {
                if (! Schema::connection('local_reset_silo')->hasTable($table)) {
                    continue;
                }
                $connection->table($table)->delete();
            }
            foreach (['jobs', 'failed_jobs', 'cache', 'cache_locks'] as $table) {
                if (Schema::connection('local_reset_silo')->hasTable($table)) {
                    $connection->table($table)->delete();
                }
            }
        } finally {
            Schema::connection('local_reset_silo')->enableForeignKeyConstraints();
            DB::purge('local_reset_silo');
        }

        $lines[] = "Silo {$slug}: tablas operativas vaciadas.";

        return $lines;
    }

    /** @return list<string> */
    private function safeUnlink(string $path, ?string $label = null): array
    {
        $label ??= str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);

        set_error_handler(static fn (): bool => true);
        try {
            $deleted = @unlink($path);
        } finally {
            restore_error_handler();
        }

        if ($deleted) {
            return ["Eliminado {$label}"];
        }

        if (is_file($path)) {
            return ["No se pudo eliminar {$label} (detenga npm run instances:serve e intente de nuevo)"];
        }

        return [];
    }

    /** @return list<string> */
    private function operationalTableNames(): array
    {
        return [
            'event_feed_projections',
            'observability_metrics',
            'channel_status_snapshots',
            'dead_letter_queue',
            'message_queue',
            'outbox_messages',
            'event_logs',
            'event_store',
            'registered_modules',
            'retries',
            'audit_logs',
            'trace_logs',
            'webhook_responses',
            'webhook_requests',
            'notifications',
            'transactions',
            'workflow_steps',
            'workflows',
            'processing_jobs',
        ];
    }
}
