<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\InstanceDeploymentService;
use Illuminate\Support\Str;

final class LocalFleetInstanceProvisioner
{
    public function __construct(
        private readonly LocalFleetRegistry $registry,
        private readonly LocalFleetEnvBuilder $envBuilder,
        private readonly LocalFleetTenantMirror $tenantMirror,
        private readonly InstanceDeploymentService $deployment,
        private readonly LocalFleetAdminCredentialsResolver $adminCredentials,
        private readonly LocalFleetAppKeyResolver $appKeyResolver,
        private readonly LocalFleetInstanceArtisanRunner $artisanRunner,
        private readonly LocalFleetLocalInstanceDescriptor $localInstanceDescriptor,
        private readonly LocalFleetTenantProvisionMarker $provisionMarker,
        private readonly bool $enabled,
        private readonly string $defaultAdminPassword,
        private readonly string $controlPlaneSlug,
    ) {}

    public function isEnabled(): bool
    {
        return $this->enabled && $this->deployment->isControlPlaneRegistry();
    }

    public function isProvisioned(TenantModel $tenant): bool
    {
        return $this->registry->isProvisioned($tenant->slug);
    }

    /**
     * @param array{name?: string, email?: string, password?: string}|null $admin
     */
    public function provision(TenantModel $tenant, ?array $admin = null): LocalFleetProvisionResult
    {
        if (! $this->isEnabled()) {
            return new LocalFleetProvisionResult(
                provisioned: false,
                instance: [],
                localInstance: [],
                message: 'Local fleet auto-provision is disabled or this host is not the control plane.',
            );
        }

        if ($this->shouldSkipTenant($tenant)) {
            return new LocalFleetProvisionResult(
                provisioned: false,
                instance: [],
                localInstance: [],
                message: 'Tenant skipped for local fleet (control-plane slug).',
            );
        }

        $admin = $this->adminCredentials->resolve($tenant, $admin, $this->defaultAdminPassword);
        $existing = $this->registry->findBySlug($tenant->slug);

        $instanceRow = $this->registry->upsert([
            'id'            => $existing['id'] ?? $this->envBuilder->instanceEnvId($tenant->slug),
            'label'         => $tenant->name,
            'slug'          => $tenant->slug,
            'port'          => $existing['port'] ?? null,
            'tenantId'      => $tenant->id,
            'adminEmail'    => $admin['email'],
            'adminPassword' => $admin['password'],
            'adminName'     => $admin['name'],
            'provisionedAt' => now()->toIso8601String(),
        ]);

        $envId = (string) $instanceRow['id'];
        $appKey = $this->appKeyResolver->resolve($envId);
        $this->envBuilder->ensureSqliteFile($tenant->slug);
        $envPath = base_path($this->envBuilder->envFileName($tenant->slug));
        file_put_contents($envPath, $this->envBuilder->build($instanceRow, $appKey));

        $this->artisanRunner->bootstrapInstance($envId);
        $this->tenantMirror->mirror($tenant->fresh());

        $localInstance = $this->localInstanceDescriptor->describe($instanceRow, $envPath, $tenant);
        $primaryEmail = $this->adminCredentials->primaryOperatorEmail($tenant);

        $this->provisionMarker->markProvisioned($tenant, $localInstance, $primaryEmail);

        return new LocalFleetProvisionResult(
            provisioned: true,
            instance: $instanceRow,
            localInstance: $localInstance,
            message: 'Instancia local aislada creada en '.$localInstance['app_url'],
        );
    }

    /** @return list<LocalFleetProvisionResult> */
    public function syncAllTenants(): array
    {
        $results = [];

        TenantModel::query()
            ->orderBy('name')
            ->get()
            ->each(function (TenantModel $tenant) use (&$results): void {
                if ($this->shouldSkipTenant($tenant)) {
                    return;
                }

                if ($this->isProvisioned($tenant)) {
                    return;
                }

                $results[] = $this->provision($tenant);
            });

        return $results;
    }

    public function allClientTenantsProvisioned(): bool
    {
        $pending = TenantModel::query()
            ->get()
            ->filter(fn (TenantModel $tenant): bool => ! $this->shouldSkipTenant($tenant))
            ->filter(fn (TenantModel $tenant): bool => ! $this->isProvisioned($tenant));

        return $pending->isEmpty();
    }

    private function shouldSkipTenant(TenantModel $tenant): bool
    {
        return Str::slug($tenant->slug) === Str::slug($this->controlPlaneSlug);
    }
}
