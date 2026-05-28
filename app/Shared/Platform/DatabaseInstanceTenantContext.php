<?php

declare(strict_types=1);

namespace App\Shared\Platform;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class DatabaseInstanceTenantContext implements InstanceTenantContextInterface
{
    private ?string $resolvedTenantId = null;

    public function deploymentMode(): string
    {
        return (string) config('platform.deployment_mode', 'instance_per_client');
    }

    public function clientSlug(): string
    {
        return Str::slug((string) config('platform.client_slug', 'default'));
    }

    public function clientName(): string
    {
        return (string) config('platform.client_name', config('app.name', 'Platform Instance'));
    }

    public function tenantId(): ?string
    {
        if ($this->allowsMultiTenantPortalLogin()) {
            $sessionTenantId = session('portal_tenant_id');
            if (is_string($sessionTenantId) && $sessionTenantId !== '') {
                return $sessionTenantId;
            }
        }

        if ($this->resolvedTenantId !== null) {
            return $this->resolvedTenantId;
        }

        if ($this->deploymentMode() !== 'instance_per_client') {
            return null;
        }

        return $this->resolveTenantIdFromClientSlug();
    }

    public function configuredInstanceTenantId(): ?string
    {
        if ($this->deploymentMode() !== 'instance_per_client') {
            return null;
        }

        return $this->resolveTenantIdFromClientSlug();
    }

    public function resolveTenantIdFromClientSlug(): ?string
    {
        if ($this->resolvedTenantId !== null) {
            return $this->resolvedTenantId;
        }

        if (! Schema::hasTable('tenants')) {
            return null;
        }

        $row = TenantModel::query()->where('slug', $this->clientSlug())->first(['id']);

        if ($row !== null) {
            $this->resolvedTenantId = $row->id;
        }

        return $this->resolvedTenantId;
    }

    public function refreshTenantCache(): void
    {
        $this->resolvedTenantId = null;
    }

    public function allowsMultiTenantPortalLogin(): bool
    {
        return (bool) config('platform.multi_tenant_portal_login', false);
    }

    public function bindPortalTenantFromSession(?string $tenantId): void
    {
        if (! $this->allowsMultiTenantPortalLogin()) {
            return;
        }

        if ($tenantId === null || $tenantId === '') {
            session()->forget('portal_tenant_id');
        } else {
            session(['portal_tenant_id' => $tenantId]);
        }

        $this->refreshTenantCache();
    }

    /** @return array<string, mixed> */
    public function logContext(): array
    {
        return array_filter([
            'platform_client_slug' => $this->clientSlug(),
            'platform_client_name' => $this->clientName(),
            'tenant_id' => $this->tenantId(),
            'deployment_mode' => $this->deploymentMode(),
        ], fn ($v) => $v !== null && $v !== '');
    }
}
