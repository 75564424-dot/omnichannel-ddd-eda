<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Support\Str;

/**
 * ADR-001: one deployable silo per commercial client.
 */
final class InstanceDeploymentService
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceContext,
    ) {}

    public function instanceSlug(): string
    {
        return Str::slug((string) config('platform.client_slug', 'default'));
    }

    public function isDedicatedInstanceMode(): bool
    {
        return config('platform.deployment_mode', 'instance_per_client') === 'instance_per_client';
    }

    public function isControlPlaneRegistry(): bool
    {
        return (bool) config('platform.control_plane', false);
    }

    public function allowsCrossTenantPortalLogin(): bool
    {
        return (bool) config('platform.multi_tenant_portal_login', false);
    }

    public function isTenantBoundToThisInstance(TenantModel|string $tenantOrSlug): bool
    {
        if (! $this->isDedicatedInstanceMode()) {
            return true;
        }

        $slug = $tenantOrSlug instanceof TenantModel
            ? $tenantOrSlug->slug
            : (string) $tenantOrSlug;

        return Str::slug($slug) === $this->instanceSlug();
    }

    public function portalLoginAllowedForTenant(TenantModel $tenant): bool
    {
        if ($this->isTenantBoundToThisInstance($tenant)) {
            return true;
        }

        return $this->allowsCrossTenantPortalLogin();
    }

    public function operatorsManageableOnThisHost(TenantModel $tenant): bool
    {
        if ($this->isTenantBoundToThisInstance($tenant)) {
            return true;
        }

        return $this->isControlPlaneRegistry() && $this->allowsCrossTenantPortalLogin();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentationForTenant(TenantModel $tenant): array
    {
        $bound = $this->isTenantBoundToThisInstance($tenant);
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $stored = is_array($settings['deployment'] ?? null) ? $settings['deployment'] : [];
        $localInstance = is_array($stored['local_instance'] ?? null) ? $stored['local_instance'] : null;

        $status = $bound ? 'active_on_instance' : (string) ($stored['status'] ?? 'pending_dedicated_instance');
        $recommendedUrl = is_array($localInstance)
            ? (string) ($localInstance['app_url'] ?? $this->recommendedAppUrl($tenant->slug))
            : $this->recommendedAppUrl($tenant->slug);

        return [
            'mode'                    => 'instance_per_client',
            'instance_slug'           => $this->instanceSlug(),
            'tenant_slug'             => $tenant->slug,
            'is_bound_to_instance'    => $bound,
            'is_control_plane'        => $this->isControlPlaneRegistry(),
            'allows_portal_on_host'   => $this->portalLoginAllowedForTenant($tenant),
            'operators_on_this_host'  => $this->operatorsManageableOnThisHost($tenant),
            'status'                  => $status,
            'status_label'            => $this->statusLabel($status, $bound, $localInstance !== null),
            'recommended_app_url'     => $recommendedUrl,
            'local_instance'          => $localInstance,
            'env_snippet'             => $this->envSnippet($tenant, $recommendedUrl),
            'bootstrap_commands'      => $this->bootstrapCommands(),
            'runbook_path'            => 'docs/production/Runbook_Onboarding_Cliente.md',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function globalPresentation(): array
    {
        return [
            'deployment_mode'       => config('platform.deployment_mode'),
            'instance_slug'       => $this->instanceSlug(),
            'is_control_plane'      => $this->isControlPlaneRegistry(),
            'multi_tenant_portal'   => $this->allowsCrossTenantPortalLogin(),
            'app_url'               => config('app.url'),
        ];
    }

    public function operatorBlockReason(TenantModel $tenant): ?string
    {
        if ($this->operatorsManageableOnThisHost($tenant)) {
            return null;
        }

        return sprintf(
            'Los operadores de «%s» inician sesión en su instancia dedicada (PLATFORM_CLIENT_SLUG=%s), no en esta URL (%s).',
            $tenant->name,
            $tenant->slug,
            $this->instanceSlug(),
        );
    }

    /**
     * @return list<string>
     */
    public function bootstrapCommands(): array
    {
        return [
            'php artisan migrate --force',
            'php artisan platform:instance:bootstrap',
            'php artisan config:cache',
            'php artisan route:cache',
        ];
    }

    private function recommendedAppUrl(string $tenantSlug): string
    {
        $template = (string) config('platform.deployment.app_url_template', 'https://{slug}.middleware.example.com');

        return str_replace('{slug}', Str::slug($tenantSlug), $template);
    }

    /**
     * @return list<string>
     */
    private function envSnippet(TenantModel $tenant, string $appUrl): array
    {
        return [
            'APP_URL='.$appUrl,
            'PLATFORM_CLIENT_SLUG='.$tenant->slug,
            'PLATFORM_CLIENT_NAME="'.$tenant->name.'"',
            'PLATFORM_DEPLOYMENT_MODE=instance_per_client',
            'PLATFORM_CONTROL_PLANE=false',
            'PLATFORM_PORTAL_MULTI_TENANT_LOGIN=false',
            'PLATFORM_SEED_INSTANCE_TENANT=true',
        ];
    }

    private function statusLabel(string $status, bool $bound, bool $hasLocalInstance = false): string
    {
        if ($bound) {
            return 'Activa en esta instancia';
        }

        if ($hasLocalInstance || $status === 'active_on_instance') {
            return 'Instancia local aislada (fleet dev)';
        }

        return match ($status) {
            'pending_dedicated_instance' => 'Pendiente: desplegar instancia dedicada',
            default => 'Instancia dedicada requerida',
        };
    }
}
