<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Instance portal: tenant membership + role-scoped dashboard/middleware routes.
 */
final class EnsureInstancePortalAccess
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->guest(route('login'));
        }

        $role = PlatformRole::tryFromString((string) $user->getAttribute('platform_role'));
        if ($role === null || $role->isSaasAdmin()) {
            return redirect()->route('control.overview');
        }

        $userTenantId = $user->getAttribute('tenant_id');

        if ($userTenantId === null) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Su cuenta no está asignada a una empresa. Contacte al administrador SaaS.']);
        }

        if ($this->instanceContext->allowsMultiTenantPortalLogin()) {
            $this->instanceContext->bindPortalTenantFromSession((string) $userTenantId);
        }

        $activeTenantId = $this->instanceContext->tenantId();

        if ($activeTenantId !== null && (string) $userTenantId !== $activeTenantId) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Su cuenta no pertenece a esta empresa. Contacte al administrador SaaS.']);
        }

        $path = $request->path();

        if ($this->isDashboardPath($path) && ! $role->canAccessDashboardWeb()) {
            return redirect()->route('middleware');
        }

        if ($this->isMiddlewarePath($path) && ! $role->canAccessMiddlewareWeb()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
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
