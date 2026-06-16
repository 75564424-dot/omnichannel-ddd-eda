<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;

/**
 * Validates instance-portal access rules (tenant binding + role-scoped routes).
 */
final class InstancePortalAccessGuard
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceContext,
    ) {}

    /**
     * @return array{allowed: bool, redirect?: string, logout?: bool, error?: string}
     */
    public function evaluate(User $user, string $path): array
    {
        $role = PlatformRole::tryFromString((string) $user->getAttribute('platform_role'));
        if ($role === null || $role->isSaasAdmin()) {
            return ['allowed' => false, 'redirect' => route('control.overview')];
        }

        $userTenantId = $user->getAttribute('tenant_id');
        if ($userTenantId === null) {
            return [
                'allowed' => false,
                'logout'  => true,
                'redirect' => route('login'),
                'error'   => 'Su cuenta no está asignada a una empresa. Contacte al administrador SaaS.',
            ];
        }

        if ($this->instanceContext->allowsMultiTenantPortalLogin()) {
            $this->instanceContext->bindPortalTenantFromSession((string) $userTenantId);
        }

        $activeTenantId = $this->instanceContext->tenantId();
        if ($activeTenantId !== null && (string) $userTenantId !== $activeTenantId) {
            return [
                'allowed' => false,
                'logout'  => true,
                'redirect' => route('login'),
                'error'   => 'Su cuenta no pertenece a esta empresa. Contacte al administrador SaaS.',
            ];
        }

        if ($this->isDashboardPath($path) && ! $role->canAccessDashboardWeb()) {
            return ['allowed' => false, 'redirect' => route('middleware')];
        }

        if ($this->isMiddlewarePath($path) && ! $role->canAccessMiddlewareWeb()) {
            return ['allowed' => false, 'redirect' => route('dashboard')];
        }

        return ['allowed' => true];
    }

    private function isDashboardPath(string $path): bool
    {
        return $path === 'dashboard' || str_starts_with($path, 'dashboard/');
    }

    private function isMiddlewarePath(string $path): bool
    {
        return $path === 'middleware';
    }
}
