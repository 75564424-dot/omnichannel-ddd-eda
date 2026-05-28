<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Scope check for platform API tokens (basic RBAC — Plan_Seguridad Fase 3 light).
 */
final class EnforcePlatformAbility
{
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        if (! config('security.api_auth_enabled', true)) {
            return $next($request);
        }

        $principal = AuthenticatePlatformApi::principal($request);
        if ($principal === null || ! $principal->hasAnyAbility($abilities)) {
            return response()->json([
                'success' => false,
                'error'   => 'Forbidden — insufficient token abilities.',
            ], 403);
        }

        return $next($request);
    }
}
