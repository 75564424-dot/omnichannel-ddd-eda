<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * Upserts the single tenant row representing this deployment instance (ADR-001).
 */
final class InstanceTenantSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('platform.seed_instance_tenant', true)) {
            $this->command?->warn('PLATFORM_SEED_INSTANCE_TENANT=false — skipping instance tenant seed.');

            return;
        }

        if (config('platform.deployment_mode') !== 'instance_per_client') {
            $this->command?->warn('deployment_mode is not instance_per_client — skipping tenant seed.');

            return;
        }

        $slug = Str::slug((string) config('platform.client_slug', 'default'));
        $name = (string) config('platform.client_name', config('app.name', 'Platform Instance'));

        if (! config('platform.control_plane', false)) {
            $pruned = TenantModel::query()->where('slug', '!=', $slug)->delete();
            if ($pruned > 0) {
                $this->command?->warn("Removed {$pruned} tenant(s) not matching instance slug «{$slug}».");
            }
        }

        $existing = TenantModel::query()->where('slug', $slug)->first();

        $settings = [
            'app_url'              => config('app.url'),
            'deployment_mode'      => config('platform.deployment_mode'),
            'plan'                 => 'unconfigured',
            'modules'              => ['middleware'],
            'primary_admin_email'  => config('platform_auth.admin_operator.email'),
            'seeded_at'            => now()->toIso8601String(),
        ];

        if ($existing !== null) {
            $existing->update([
                'name'     => $name,
                'status'   => 'active',
                'settings' => array_merge($existing->settings ?? [], $settings),
            ]);
            $this->command?->info("Instance tenant updated: slug={$slug} id={$existing->id}");

            return;
        }

        $id = Uuid::uuid4()->toString();

        TenantModel::query()->create([
            'id'       => $id,
            'slug'     => $slug,
            'name'     => $name,
            'status'   => 'active',
            'settings' => $settings,
        ]);

        $this->command?->info("Instance tenant created: slug={$slug} id={$id}");
    }
}
