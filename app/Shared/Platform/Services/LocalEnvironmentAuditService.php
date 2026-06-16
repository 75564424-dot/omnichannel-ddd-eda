<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Detects client-tenant artifacts that survived a local environment reset.
 */
final class LocalEnvironmentAuditService
{
    public function __construct(
        private readonly LocalFleetRegistry $fleetRegistry,
    ) {}

    /**
     * @return list<string> Non-empty means the environment is not clean.
     */
    public function findSurvivors(): array
    {
        $issues = [];
        $controlPlaneSlug = Str::slug((string) config('platform.client_slug', 'platform'));

        $activeSlugs = TenantModel::query()
            ->pluck('slug')
            ->map(fn ($slug) => Str::slug((string) $slug))
            ->all();

        $softDeletedSlugs = TenantModel::withTrashed()
            ->whereNotNull('deleted_at')
            ->pluck('slug')
            ->map(fn ($slug) => Str::slug((string) $slug))
            ->filter(fn (string $slug) => $slug !== '' && $slug !== $controlPlaneSlug)
            ->values()
            ->all();

        foreach ($softDeletedSlugs as $slug) {
            $issues[] = "Tenant soft-deleted en CP: {$slug}";
        }

        $allowedSlugs = array_values(array_unique(array_merge([$controlPlaneSlug], $activeSlugs)));

        $activeClientSlugs = array_values(array_filter(
            $allowedSlugs,
            fn (string $slug) => $slug !== $controlPlaneSlug,
        ));

        foreach ($this->fleetRegistry->clientInstances() as $instance) {
            $slug = Str::slug((string) ($instance['slug'] ?? ''));
            if ($slug !== '' && ! in_array($slug, $allowedSlugs, true)) {
                $issues[] = "Fleet registry huérfano: {$slug}";
            }
        }

        foreach (glob(base_path('.env.client-*')) ?: [] as $envPath) {
            $slug = Str::after(basename($envPath), '.env.client-');
            if ($slug === '' || in_array($slug, $allowedSlugs, true)) {
                continue;
            }
            if ($activeClientSlugs === [] || in_array($slug, $softDeletedSlugs, true)) {
                $issues[] = 'Archivo env residual: '.basename($envPath);
            }
        }

        foreach (glob(base_path('database/instances/*.sqlite')) ?: [] as $dbPath) {
            $base = basename($dbPath, '.sqlite');
            if ($base === $controlPlaneSlug || in_array($base, $allowedSlugs, true)) {
                continue;
            }
            if ($activeClientSlugs === [] || in_array($base, $softDeletedSlugs, true)) {
                $issues[] = 'SQLite residual: database/instances/'.$base.'.sqlite';
            }
        }

        $modulesRoot = config_path('modules/instances');
        if (is_dir($modulesRoot)) {
            foreach (glob($modulesRoot.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
                $slug = basename($dir);
                if (in_array($slug, $allowedSlugs, true)) {
                    continue;
                }
                if ($activeClientSlugs === [] || in_array($slug, $softDeletedSlugs, true)) {
                    $issues[] = 'Mirror modules residual: config/modules/instances/'.$slug;
                }
            }
        }

        $handoffDir = storage_path('app/simulation-handoff');
        if (is_dir($handoffDir)) {
            $handoffCount = count(glob($handoffDir.DIRECTORY_SEPARATOR.'*.json') ?: []);
            if ($handoffCount > 0) {
                $issues[] = "Handoffs de simulación pendientes: {$handoffCount}";
            }
        }

        if (Schema::hasTable('simulation_runs') && (int) DB::table('simulation_runs')->count() > 0) {
            $issues[] = 'Filas simulation_runs pendientes: '.DB::table('simulation_runs')->count();
        }

        if (Schema::hasTable('client_incident_reports')) {
            $orphanReports = (int) DB::table('client_incident_reports as r')
                ->leftJoin('tenants as t', 't.id', '=', 'r.tenant_id')
                ->whereNull('t.id')
                ->count();

            if ($orphanReports > 0) {
                $issues[] = "Reportes de incidentes huérfanos: {$orphanReports}";
            }
        }

        return $issues;
    }

    public function isClean(): bool
    {
        return $this->findSurvivors() === [];
    }
}
