<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            InstanceTenantSeeder::class,
            MiddlewareDatabaseSeeder::class,
            PlatformOperatorSeeder::class,
            SaasOperatorSeeder::class,
        ]);

        $this->command?->info('Platform core seeded. Use `php artisan platform:emit-mock` for demo traffic.');
    }
}
