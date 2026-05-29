<?php

declare(strict_types=1);

namespace App\Console\Commands\Platform;

use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Database\Seeders\InstanceTenantSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class EnsureInstanceTenantCommand extends Command
{
    protected $signature = 'platform:ensure-instance-tenant';

    protected $description = 'Upserts the tenants row for this deployment instance (ADR-001)';

    public function handle(InstanceTenantContextInterface $context): int
    {
        $this->callSilent('db:seed', ['--class' => InstanceTenantSeeder::class, '--force' => true]);

        if (method_exists($context, 'refreshTenantCache')) {
            $context->refreshTenantCache();
        }
        Log::shareContext($context->logContext());

        $this->info('Instance tenant ensured. Verify with: SELECT id, slug, name FROM tenants;');

        return self::SUCCESS;
    }
}
