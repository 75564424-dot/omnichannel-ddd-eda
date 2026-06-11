<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Application\Presenters\TenantSuspendedResponsePresenter;
use App\Http\Application\Security\OperatorSessionTerminator;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantOperationalStatus
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceContext,
        private readonly OperatorSessionTerminator $sessionTerminator,
        private readonly TenantSuspendedResponsePresenter $suspendedResponse,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('platform.lifecycle_v15', true) || config('platform.control_plane', false)) {
            return $next($request);
        }

        if ($this->shouldBypassPath($request->path())) {
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
            $this->sessionTerminator->terminate($request);
        }

        return $this->suspendedResponse->respond($request);
    }

    private function shouldBypassPath(string $path): bool
    {
        return $path === 'up'
            || $path === 'health/ready'
            || str_starts_with($path, '_vite')
            || str_starts_with($path, 'build');
    }
}
