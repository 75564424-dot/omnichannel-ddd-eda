<?php

declare(strict_types=1);

namespace App\Console\Commands\Platform;

use App\Shared\Platform\Services\ClientInstanceBootstrapService;
use Illuminate\Console\Command;

final class BootstrapClientInstanceCommand extends Command
{
    protected $signature = 'platform:instance:bootstrap
                            {--slug= : Override PLATFORM_CLIENT_SLUG for this run}
                            {--skip-admin : Do not create/update platform admin from env}';

    protected $description = 'Bootstrap dedicated client instance (tenant row + optional admin)';

    public function handle(ClientInstanceBootstrapService $bootstrap): int
    {
        if ($bootstrap->isControlPlaneHost()) {
            $this->warn('PLATFORM_CONTROL_PLANE=true — this host looks like a SaaS registry, not a client silo.');
        }

        try {
            $result = $bootstrap->bootstrap(
                is_string($this->option('slug')) && $this->option('slug') !== ''
                    ? (string) $this->option('slug')
                    : null,
                (bool) $this->option('skip-admin'),
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Instance tenant: {$result['tenant_name']} ({$result['tenant_id']})");

        if ($result['catalog_applied']) {
            $this->info('Catálogo de módulos escrito en MODULES_CONFIG_PATH para esta instancia.');
        }

        if ($result['other_tenants'] > 0) {
            $this->warn("There are still {$result['other_tenants']} other tenant row(s). Re-run seeder or prune manually.");
        }

        $this->newLine();
        $this->line('Next: php artisan config:cache && smoke test publish/sync.');

        return self::SUCCESS;
    }
}
