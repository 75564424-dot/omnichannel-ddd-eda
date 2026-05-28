<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetEnvBuilder;
use App\Shared\Platform\LocalFleet\LocalFleetRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class PruneLocalFleetClientsCommand extends Command
{
    protected $signature = 'platform:fleet:prune-orphans
                            {--slug=* : Extra slugs to remove (e.g. retail-norte)}';

    protected $description = 'Remove client tenants/silos not listed in fleet-registry.json';

    public function handle(LocalFleetRegistry $registry, LocalFleetEnvBuilder $envBuilder): int
    {
        $allowed = collect($registry->clientInstances())->pluck('slug')->map(fn ($s) => Str::slug((string) $s));
        $allowed->push('platform');

        $extra = collect($this->option('slug') ?? [])->map(fn ($s) => Str::slug((string) $s));
        $removeSlugs = TenantModel::query()
            ->pluck('slug')
            ->map(fn ($s) => Str::slug((string) $s))
            ->filter(fn (string $slug) => ! $allowed->contains($slug))
            ->merge($extra)
            ->unique()
            ->values();

        foreach ($removeSlugs as $slug) {
            if ($slug === 'platform') {
                continue;
            }

            $tenant = TenantModel::query()->where('slug', $slug)->first();
            if ($tenant !== null) {
                User::query()->where('tenant_id', $tenant->id)->delete();
                $tenant->delete();
                $this->line("Removed tenant «{$slug}» from control plane.");
            }

            $envPath = base_path($envBuilder->envFileName($slug));
            if (is_file($envPath)) {
                unlink($envPath);
                $this->line("Deleted {$envPath}");
            }

            $dbPath = $envBuilder->ensureSqliteFile($slug);
            if (is_file($dbPath)) {
                unlink($dbPath);
                $this->line("Deleted {$dbPath}");
            }

            $modulesDir = config_path('modules/instances/'.$slug);
            if (is_dir($modulesDir)) {
                array_map('unlink', glob($modulesDir.'/*') ?: []);
                rmdir($modulesDir);
                $this->line("Deleted {$modulesDir}");
            }
        }

        $this->info('Prune complete.');

        return self::SUCCESS;
    }
}
