<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Shared\Api\Http\Responses\ProblemDetailsFactory;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantOperationalStatus
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('platform.lifecycle_v15', true) || config('platform.control_plane', false)) {
            return $next($request);
        }

        $path = $request->path();
        if ($path === 'up' || $path === 'health/ready' || str_starts_with($path, '_vite') || str_starts_with($path, 'build')) {
            return $next($request);
        }

        $tenantId = $this->instanceContext->tenantId();
        if ($tenantId === null) {
            return $next($request);
        }

        $tenant = TenantModel::query()->find($tenantId);
        if ($tenant === null || $tenant->status !== 'suspended') {
            return $next($request);
        }

        if ($request->user() !== null) {
            auth()->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }

        $message = 'El servicio asociado a esta empresa se encuentra temporalmente suspendido. Contacte al administrador para obtener más información.';

        if ($request->is('api/*') || $request->expectsJson()) {
            return ProblemDetailsFactory::make(
                title: 'Tenant Suspended',
                status: 403,
                detail: $message,
                type: 'tenant_suspended'
            );
        }

        return Inertia::render('Tenant/Suspended', [
            'message' => $message,
        ])->toResponse($request)->setStatusCode(503);
    }
}
