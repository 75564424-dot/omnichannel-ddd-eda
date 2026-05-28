<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds the default platform operator (Plan_Autenticacion Fase 1).
 */
final class PlatformOperatorSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('platform_auth.seed_admin_operator', true)) {
            return;
        }

        $admin = config('platform_auth.admin_operator', []);
        $email = trim((string) ($admin['email'] ?? ''));
        if ($email === '') {
            return;
        }

        $tenantId = TenantModel::query()
            ->where('slug', Str::slug((string) config('platform.client_slug', 'default')))
            ->value('id');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'tenant_id'     => $tenantId,
                'name'          => (string) ($admin['name'] ?? 'Platform Admin'),
                'password'      => Hash::make((string) ($admin['password'] ?? 'password')),
                'platform_role' => (string) ($admin['platform_role'] ?? 'platform_admin'),
            ]
        );

        $this->command?->info("Platform operator seeded: {$email}");
    }
}
