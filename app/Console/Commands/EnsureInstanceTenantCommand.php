<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\InstanceTenantSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;

final class EnsureInstanceTenantCommand extends Command
{
    protected $signature = 'platform:ensure-instance-tenant';

    protected $description = 'Upserts the tenants row for this deployment instance (ADR-001)';

    public function handle(): int
    {
        $this->callSilent('db:seed', ['--class' => InstanceTenantSeeder::class, '--force' => true]);

        $context = $this->laravel->make(InstanceTenantContextInterface::class);
        if (method_exists($context, 'refreshTenantCache')) {
            $context->refreshTenantCache();
        }
        Log::shareContext($context->logContext());

        $this->info('Instance tenant ensured. Verify with: SELECT id, slug, name FROM tenants;');

        return self::SUCCESS;
    }
}
