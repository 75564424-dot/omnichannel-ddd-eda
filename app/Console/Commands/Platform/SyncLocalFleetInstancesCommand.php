<?php

declare(strict_types=1);

namespace App\Console\Commands\Platform;

use App\Shared\Platform\LocalFleet\LocalFleetSyncService;
use Illuminate\Console\Command;

final class SyncLocalFleetInstancesCommand extends Command
{
    protected $signature = 'platform:fleet:sync-local
                            {--slug= : Provision only this tenant slug}
                            {--force : Re-run bootstrap even if already in registry}';

    protected $description = 'Create local isolated instances (.env + SQLite) for SaaS tenants';

    public function handle(LocalFleetSyncService $syncService): int
    {
        if (! $syncService->isEnabled()) {
            $this->error('Enable PLATFORM_LOCAL_FLEET_AUTO_PROVISION=true on the control-plane host.');

            return self::FAILURE;
        }

        $slug = $this->option('slug');
        $result = $syncService->sync(
            is_string($slug) && $slug !== '' ? $slug : null,
            (bool) $this->option('force'),
        );

        foreach ($result['lines'] as $line) {
            str_starts_with($line, 'No tenants') ? $this->warn($line) : $this->line($line);
        }

        $this->newLine();
        $this->info("Done. {$result['provisioned']} instance(s) ready. Run: npm run instances:serve");

        return self::SUCCESS;
    }
}
