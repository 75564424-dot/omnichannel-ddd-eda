<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Support\Str;

final class LocalFleetOrphanPruner
{
    public function __construct(
        private readonly LocalFleetRegistry $registry,
        private readonly LocalFleetEnvBuilder $envBuilder,
    ) {}

    /**
     * @param list<string> $extraSlugs
     * @return list<string>
     */
    public function prune(array $extraSlugs = []): array
    {
        $allowed = collect($this->registry->clientInstances())
            ->pluck('slug')
            ->map(fn ($s) => Str::slug((string) $s));
        $allowed->push('platform');

        $removeSlugs = TenantModel::query()
            ->pluck('slug')
            ->map(fn ($s) => Str::slug((string) $s))
            ->filter(fn (string $slug) => ! $allowed->contains($slug))
            ->merge(collect($extraSlugs)->map(fn ($s) => Str::slug((string) $s)))
            ->unique()
            ->values();

        $lines = [];

        foreach ($removeSlugs as $slug) {
            if ($slug === 'platform') {
                continue;
            }
            $lines = array_merge($lines, $this->removeSlug($slug));
        }

        return $lines;
    }

    /** @return list<string> */
    private function removeSlug(string $slug): array
    {
        $lines = [];

        $tenant = TenantModel::withTrashed()->where('slug', $slug)->first();
        if ($tenant !== null) {
            User::query()->where('tenant_id', $tenant->id)->delete();
            $tenant->forceDelete();
            $lines[] = "Removed tenant «{$slug}» from control plane (hard).";
        }

        $envPath = base_path($this->envBuilder->envFileName($slug));
        if (is_file($envPath)) {
            unlink($envPath);
            $lines[] = "Deleted {$envPath}";
        }

        $dbPath = $this->envBuilder->ensureSqliteFile($slug);
        if (is_file($dbPath)) {
            unlink($dbPath);
            $lines[] = "Deleted {$dbPath}";
        }

        $modulesDir = config_path('modules/instances/'.$slug);
        if (is_dir($modulesDir)) {
            array_map('unlink', glob($modulesDir.'/*') ?: []);
            rmdir($modulesDir);
            $lines[] = "Deleted {$modulesDir}";
        }

        return $lines;
    }
}
