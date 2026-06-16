<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Tenants;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\Contracts\LocalFleetTenantMirrorInterface;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

final class TenantAdminService
{
    public function __construct(
        private readonly TenantPresentationService $presentation,
        private readonly LocalFleetTenantMirrorInterface $tenantMirror,
    ) {}

    /**
     * @param array<string, mixed> $companyProfile
     * @param list<string>         $modules
     */
    public function create(
        string $name,
        string $slug,
        string $plan = 'starter',
        array $companyProfile = [],
        array $modules = ['middleware'],
    ): TenantModel {
        $slug = Str::slug($slug);
        $modules = $modules !== [] ? array_values(array_unique($modules)) : ['middleware'];

        return TenantModel::query()->create([
            'id'       => Uuid::uuid4()->toString(),
            'name'     => $name,
            'slug'     => $slug,
            'status'   => 'active',
            'settings' => [
                'plan'                 => $plan,
                'modules'              => $modules,
                'company_profile'      => $companyProfile,
                'app_url'              => null,
                'primary_admin_email'  => null,
                'provisioned_at'       => now()->toIso8601String(),
            ],
        ]);
    }

    public function suspend(TenantModel $tenant): void
    {
        $tenant->update(['status' => 'suspended']);
    }

    public function activate(TenantModel $tenant): void
    {
        $tenant->update(['status' => 'active']);
    }

    public function updatePlan(TenantModel $tenant, string $plan): void
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['plan'] = $plan;
        $tenant->update(['settings' => $settings]);
    }

    /** @param list<string> $modules */
    public function updateModules(TenantModel $tenant, array $modules): void
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['modules'] = array_values(array_unique($modules));
        $tenant->update(['settings' => $settings]);

        if ($this->hasLocalInstanceDeployment($settings)) {
            $this->tenantMirror->mirrorCatalog($tenant->fresh() ?? $tenant);
        }
    }

    /** @param array<string, mixed> $settings */
    private function hasLocalInstanceDeployment(array $settings): bool
    {
        $deployment = $settings['deployment'] ?? null;
        if (! is_array($deployment)) {
            return false;
        }

        $local = $deployment['local_instance'] ?? null;

        return is_array($local)
            && trim((string) ($local['db_path'] ?? '')) !== '';
    }
}

