<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Database\Seeders\AcmeRetailSimulationSeeder;
use Database\Seeders\InstanceTenantSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class DemoIdentityResetService
{
    /**
     * @return array{
     *     tenant_name: string,
     *     tenant_slug: string,
     *     deleted_users: int,
     *     deleted_tenants: int,
     *     deleted_reports: int,
     *     operators: list<array{0: string, 1: string, 2: string}>
     * }
     */
    public function reset(): array
    {
        $slug = Str::slug((string) config('platform.client_slug', 'acme-retail'));
        $admin = config('platform_auth.admin_operator', []);
        $saas = config('platform_auth.saas_operator', []);

        $adminEmail = trim((string) ($admin['email'] ?? 'admin@local'));
        $saasEmail = trim((string) ($saas['email'] ?? 'saas@local'));
        $keepEmails = array_values(array_unique(array_filter([$adminEmail, $saasEmail])));

        Artisan::call('db:seed', ['--class' => InstanceTenantSeeder::class, '--force' => true]);

        $tenant = TenantModel::query()->where('slug', $slug)->first();
        if ($tenant === null) {
            throw new \RuntimeException("No existe el tenant «{$slug}». Revise PLATFORM_CLIENT_SLUG.");
        }

        $deletedReports = 0;
        $otherTenantIds = TenantModel::query()
            ->where('slug', '!=', $slug)
            ->pluck('id')
            ->all();

        if ($otherTenantIds !== [] && Schema::hasTable('client_incident_reports')) {
            $deletedReports = (int) DB::table('client_incident_reports')
                ->whereIn('tenant_id', $otherTenantIds)
                ->delete();
        }

        $deletedUsers = (int) User::query()->whereNotIn('email', $keepEmails)->delete();
        $deletedTenants = (int) TenantModel::query()->where('slug', '!=', $slug)->delete();

        User::query()->updateOrCreate(
            ['email' => $saasEmail],
            [
                'tenant_id' => null,
                'name' => (string) ($saas['name'] ?? 'SaaS Admin'),
                'password' => Hash::make((string) ($saas['password'] ?? 'password')),
                'platform_role' => 'saas_admin',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'tenant_id' => $tenant->id,
                'name' => (string) ($admin['name'] ?? 'Platform Admin'),
                'password' => Hash::make((string) ($admin['password'] ?? 'password')),
                'platform_role' => (string) ($admin['platform_role'] ?? 'platform_admin'),
            ],
        );

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['primary_admin_email'] = $adminEmail;
        $tenant->update(['settings' => $settings]);

        Artisan::call('db:seed', ['--class' => AcmeRetailSimulationSeeder::class, '--force' => true]);
        Cache::forget('platform.monitoring.bus_stopped_since');

        return [
            'tenant_name' => (string) $tenant->name,
            'tenant_slug' => $slug,
            'deleted_users' => $deletedUsers,
            'deleted_tenants' => $deletedTenants,
            'deleted_reports' => $deletedReports,
            'operators' => [
                [$adminEmail, 'platform_admin', $slug],
                [$saasEmail, 'saas_admin', '— (control SaaS)'],
            ],
        ];
    }
}
