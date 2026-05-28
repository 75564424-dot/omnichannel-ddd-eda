<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;
use App\Shared\Platform\LocalFleet\LocalFleetTenantMirror;
use Illuminate\Console\Command;

final class SyncLocalFleetInstancesCommand extends Command
{
    protected $signature = 'platform:fleet:sync-local
                            {--slug= : Provision only this tenant slug}
                            {--force : Re-run bootstrap even if already in registry}';

    protected $description = 'Create local isolated instances (.env + SQLite) for SaaS tenants';

    public function handle(LocalFleetInstanceProvisioner $provisioner): int
    {
        if (! $provisioner->isEnabled()) {
            $this->error('Enable PLATFORM_LOCAL_FLEET_AUTO_PROVISION=true on the control-plane host.');

            return self::FAILURE;
        }

        $slug = $this->option('slug');
        $tenants = TenantModel::query()
            ->when(is_string($slug) && $slug !== '', fn ($q) => $q->where('slug', $slug))
            ->orderBy('name')
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants matched.');

            return self::SUCCESS;
        }

        $provisioned = 0;

        foreach ($tenants as $tenant) {
            if ($provisioner->isProvisioned($tenant) && ! $this->option('force')) {
                $this->line("  skip {$tenant->slug} (already provisioned)");

                continue;
            }

            if ($provisioner->isProvisioned($tenant) && $this->option('force')) {
                $this->info("Re-sync {$tenant->name} ({$tenant->slug})…");
                app(LocalFleetTenantMirror::class)->mirror($tenant->fresh());
                $this->line('  → mirror OK');

                continue;
            }

            $this->info("Provisioning {$tenant->name} ({$tenant->slug})…");
            $result = $provisioner->provision($tenant);

            if ($result->provisioned) {
                ++$provisioned;
                $this->line('  → '.$result->appUrl());
            } else {
                $this->warn('  → '.($result->message ?? 'skipped'));
            }
        }

        $this->newLine();
        $this->info("Done. {$provisioned} instance(s) ready. Run: npm run instances:serve");

        return self::SUCCESS;
    }
}
