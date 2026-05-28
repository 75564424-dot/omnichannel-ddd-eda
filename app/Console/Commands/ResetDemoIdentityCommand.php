<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Database\Seeders\AcmeRetailSimulationSeeder;
use Database\Seeders\InstanceTenantSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Demo reset: single instance tenant (PLATFORM_CLIENT_SLUG) + admin@local + saas@local only.
 */
final class ResetDemoIdentityCommand extends Command
{
    protected $signature = 'platform:reset-demo-identity';

    protected $description = 'Remove extra tenants/users; keep acme-retail, admin@local and saas@local';

    public function handle(): int
    {
        $slug = Str::slug((string) config('platform.client_slug', 'acme-retail'));
        $admin = config('platform_auth.admin_operator', []);
        $saas  = config('platform_auth.saas_operator', []);

        $adminEmail = trim((string) ($admin['email'] ?? 'admin@local'));
        $saasEmail  = trim((string) ($saas['email'] ?? 'saas@local'));
        $keepEmails = array_values(array_unique(array_filter([$adminEmail, $saasEmail])));

        $this->callSilent('db:seed', ['--class' => InstanceTenantSeeder::class, '--force' => true]);

        $tenant = TenantModel::query()->where('slug', $slug)->first();
        if ($tenant === null) {
            $this->error("No existe el tenant «{$slug}». Revise PLATFORM_CLIENT_SLUG.");

            return self::FAILURE;
        }

        $otherTenantIds = TenantModel::query()
            ->where('slug', '!=', $slug)
            ->pluck('id')
            ->all();

        if ($otherTenantIds !== [] && Schema::hasTable('client_incident_reports')) {
            $reports = DB::table('client_incident_reports')
                ->whereIn('tenant_id', $otherTenantIds)
                ->delete();
            $this->line("Reportes de incidentes eliminados (otros tenants): {$reports}");
        }

        $deletedUsers = User::query()->whereNotIn('email', $keepEmails)->delete();
        $this->line("Usuarios eliminados: {$deletedUsers}");

        $deletedTenants = TenantModel::query()->where('slug', '!=', $slug)->delete();
        $this->line("Empresas (tenants) eliminadas: {$deletedTenants}");

        User::query()->updateOrCreate(
            ['email' => $saasEmail],
            [
                'tenant_id'     => null,
                'name'          => (string) ($saas['name'] ?? 'SaaS Admin'),
                'password'      => Hash::make((string) ($saas['password'] ?? 'password')),
                'platform_role' => 'saas_admin',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'tenant_id'     => $tenant->id,
                'name'          => (string) ($admin['name'] ?? 'Platform Admin'),
                'password'      => Hash::make((string) ($admin['password'] ?? 'password')),
                'platform_role' => (string) ($admin['platform_role'] ?? 'platform_admin'),
            ],
        );

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['primary_admin_email'] = $adminEmail;
        $tenant->update(['settings' => $settings]);

        $this->callSilent('db:seed', ['--class' => AcmeRetailSimulationSeeder::class, '--force' => true]);

        Cache::forget('platform.monitoring.bus_stopped_since');

        $this->newLine();
        $this->info("Listo. Empresa: {$tenant->name} ({$slug})");
        $this->table(
            ['Email', 'Rol', 'Empresa'],
            [
                [$adminEmail, 'platform_admin', $slug],
                [$saasEmail, 'saas_admin', '— (control SaaS)'],
            ],
        );
        $this->comment('Contraseñas según PLATFORM_ADMIN_PASSWORD y PLATFORM_SAAS_ADMIN_PASSWORD en .env');

        return self::SUCCESS;
    }
}
