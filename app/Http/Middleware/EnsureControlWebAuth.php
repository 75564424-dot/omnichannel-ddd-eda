<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SaaS control plane — only saas_admin operators.
 */
final class EnsureControlWebAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('platform_auth.web_auth_enabled', true)) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->guest(route('login'));
        }

        $role = PlatformRole::tryFromString((string) $user->getAttribute('platform_role'));
        if ($role === null || ! $role->isSaasAdmin()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
