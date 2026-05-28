<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the SaaS provider operator (control plane / System Control).
 */
final class SaasOperatorSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('platform_auth.seed_saas_operator', true)) {
            return;
        }

        $admin = config('platform_auth.saas_operator', []);
        $email = trim((string) ($admin['email'] ?? ''));
        if ($email === '') {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'tenant_id'     => null,
                'name'          => (string) ($admin['name'] ?? 'SaaS Admin'),
                'password'      => Hash::make((string) ($admin['password'] ?? 'password')),
                'platform_role' => 'saas_admin',
            ]
        );

        $this->command?->info("SaaS control operator seeded: {$email}");
    }
}
