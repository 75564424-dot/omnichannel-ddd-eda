<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Database\Seeders\InstanceTenantSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Materializes the single tenant + admin operator for a dedicated client instance (ADR-001).
 */
final class BootstrapClientInstanceCommand extends Command
{
    protected $signature = 'platform:instance:bootstrap
                            {--slug= : Override PLATFORM_CLIENT_SLUG for this run}
                            {--skip-admin : Do not create/update platform admin from env}';

    protected $description = 'Bootstrap dedicated client instance (tenant row + optional admin)';

    public function handle(InstanceTenantContextInterface $context): int
    {
        if (config('platform.deployment_mode') !== 'instance_per_client') {
            $this->error('PLATFORM_DEPLOYMENT_MODE must be instance_per_client.');

            return self::FAILURE;
        }

        if (config('platform.control_plane', false)) {
            $this->warn('PLATFORM_CONTROL_PLANE=true — this host looks like a SaaS registry, not a client silo.');
        }

        $slug = Str::slug((string) ($this->option('slug') ?: config('platform.client_slug', '')));
        if ($slug === '') {
            $this->error('Set PLATFORM_CLIENT_SLUG or pass --slug=');

            return self::FAILURE;
        }

        config(['platform.client_slug' => $slug]);
        if (method_exists($context, 'refreshTenantCache')) {
            $context->refreshTenantCache();
        }

        $this->callSilent('db:seed', ['--class' => InstanceTenantSeeder::class, '--force' => true]);

        $tenant = TenantModel::query()->where('slug', $slug)->first();
        if ($tenant === null) {
            $this->error("Tenant «{$slug}» was not created. Check PLATFORM_SEED_INSTANCE_TENANT.");

            return self::FAILURE;
        }

        $this->info("Instance tenant: {$tenant->name} ({$tenant->id})");

        if (! $this->option('skip-admin')) {
            $this->seedAdminForTenant($tenant);
        }

        $otherTenants = TenantModel::query()->where('slug', '!=', $slug)->count();
        if ($otherTenants > 0) {
            $this->warn("There are still {$otherTenants} other tenant row(s). Re-run seeder or prune manually.");
        }

        $this->newLine();
        $this->line('Next: php artisan config:cache && smoke test publish/sync.');

        return self::SUCCESS;
    }

    private function seedAdminForTenant(TenantModel $tenant): void
    {
        if (! config('platform_auth.seed_admin_operator', true)) {
            $this->warn('PLATFORM_SEED_ADMIN_OPERATOR=false — skipping admin.');

            return;
        }

        $admin = config('platform_auth.admin_operator', []);
        $email = (string) ($admin['email'] ?? '');
        if ($email === '') {
            $this->warn('No PLATFORM_ADMIN_EMAIL configured.');

            return;
        }

        $user = User::query()->where('email', $email)->first();
        $payload = [
            'tenant_id'     => $tenant->id,
            'name'          => (string) ($admin['name'] ?? 'Platform Admin'),
            'password'      => Hash::make((string) ($admin['password'] ?? 'change-me')),
            'platform_role' => (string) ($admin['role'] ?? 'platform_admin'),
        ];

        if ($user === null) {
            User::query()->create(array_merge($payload, ['email' => $email]));
            $this->info("Created platform admin: {$email}");
        } else {
            $user->update($payload);
            $this->info("Updated platform admin: {$email}");
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['primary_admin_email'] = $email;
        $settings['deployment'] = [
            'mode'   => 'instance_per_client',
            'status' => 'active_on_instance',
        ];
        $tenant->update(['settings' => $settings]);
    }
}
