<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Database\Seeders\InstanceTenantSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class ClientInstanceBootstrapService
{
    public function __construct(
        private readonly InstanceTenantContextInterface $context,
        private readonly TenantModuleCatalogService $moduleCatalog,
    ) {}

    /**
     * @return array{tenant_id: string, tenant_name: string, slug: string, other_tenants: int, catalog_applied: bool}
     */
    public function bootstrap(?string $slugOverride, bool $skipAdmin): array
    {
        if (config('platform.deployment_mode') !== 'instance_per_client') {
            throw new \RuntimeException('PLATFORM_DEPLOYMENT_MODE must be instance_per_client.');
        }

        $slug = Str::slug((string) ($slugOverride ?: config('platform.client_slug', '')));
        if ($slug === '') {
            throw new \RuntimeException('Set PLATFORM_CLIENT_SLUG or pass --slug=');
        }

        config(['platform.client_slug' => $slug]);
        if (method_exists($this->context, 'refreshTenantCache')) {
            $this->context->refreshTenantCache();
        }

        Artisan::call('db:seed', ['--class' => InstanceTenantSeeder::class, '--force' => true]);

        $tenant = TenantModel::query()->where('slug', $slug)->first();
        if ($tenant === null) {
            throw new \RuntimeException("Tenant «{$slug}» was not created. Check PLATFORM_SEED_INSTANCE_TENANT.");
        }

        $catalogApplied = false;
        if ($this->moduleCatalog->canApplyToCurrentInstance($tenant)) {
            $this->moduleCatalog->applyToCurrentInstance($tenant);
            $catalogApplied = true;
        }

        if (! $skipAdmin) {
            $this->seedAdminForTenant($tenant);
        }

        return [
            'tenant_id' => (string) $tenant->id,
            'tenant_name' => (string) $tenant->name,
            'slug' => $slug,
            'other_tenants' => TenantModel::query()->where('slug', '!=', $slug)->count(),
            'catalog_applied' => $catalogApplied,
        ];
    }

    public function isControlPlaneHost(): bool
    {
        return (bool) config('platform.control_plane', false);
    }

    private function seedAdminForTenant(TenantModel $tenant): void
    {
        if (! config('platform_auth.seed_admin_operator', true)) {
            return;
        }

        $admin = config('platform_auth.admin_operator', []);
        $email = (string) ($admin['email'] ?? '');
        if ($email === '') {
            return;
        }

        $user = User::query()->where('email', $email)->first();
        $payload = [
            'tenant_id' => $tenant->id,
            'name' => (string) ($admin['name'] ?? 'Platform Admin'),
            'password' => Hash::make((string) ($admin['password'] ?? 'change-me')),
            'platform_role' => (string) ($admin['role'] ?? 'platform_admin'),
        ];

        if ($user === null) {
            User::query()->create(array_merge($payload, ['email' => $email]));
        } else {
            $user->update($payload);
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['primary_admin_email'] = $email;
        $settings['deployment'] = [
            'mode' => 'instance_per_client',
            'status' => 'active_on_instance',
        ];
        $tenant->update(['settings' => $settings]);
    }
}

