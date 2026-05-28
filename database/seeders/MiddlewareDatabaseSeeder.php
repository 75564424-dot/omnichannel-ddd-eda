<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * Seeds default middleware channel and retention keys in system_configurations (Plan_BaseDeDatos).
 */
final class MiddlewareDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('channels')) {
            $this->command?->warn('Middleware schema not migrated — skipping MiddlewareDatabaseSeeder.');

            return;
        }

        $tenantId = $this->resolveTenantId();

        $this->seedDefaultChannel($tenantId);
        $this->seedRetentionConfigurations($tenantId);

        $this->command?->info('Middleware database defaults seeded (channel + retention keys).');
    }

    private function resolveTenantId(): ?string
    {
        $slug = Str::slug((string) config('platform.client_slug', 'default'));
        $tenant = TenantModel::query()->where('slug', $slug)->first();

        return $tenant?->id;
    }

    private function seedDefaultChannel(?string $tenantId): void
    {
        $exists = DB::table('channels')
            ->where('code', 'middleware')
            ->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($tenantId === null, fn ($q) => $q->whereNull('tenant_id'))
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('channels')->insert([
            'id'           => Uuid::uuid4()->toString(),
            'tenant_id'    => $tenantId,
            'code'         => 'middleware',
            'name'         => 'Middleware Bus Channel',
            'channel_type' => 'internal',
            'status'       => 'active',
            'metadata'     => json_encode(['seed' => 'MiddlewareDatabaseSeeder'], JSON_THROW_ON_ERROR),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function seedRetentionConfigurations(?string $tenantId): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('system_configurations')) {
            return;
        }

        /** @var array<string, int> $defaults */
        $defaults = config('platform_retention.tables', []);
        $prefix   = (string) config('platform_retention.config_key_prefix', 'retention.');

        foreach ($defaults as $table => $days) {
            $key = $prefix.$table.'_days';

            $exists = DB::table('system_configurations')
                ->where('config_key', $key)
                ->where('scope', 'global')
                ->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))
                ->when($tenantId === null, fn ($q) => $q->whereNull('tenant_id'))
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('system_configurations')->insert([
                'id'           => Uuid::uuid4()->toString(),
                'tenant_id'    => $tenantId,
                'config_key'   => $key,
                'config_value' => json_encode(['days' => $days], JSON_THROW_ON_ERROR),
                'scope'        => 'global',
                'version'      => 1,
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
