<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Client instance portal — dashboard + middleware (not SaaS control plane).
 */
final class EnsureInstanceWebAuth
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
        if ($role !== null && $role->isSaasAdmin()) {
            if (config('platform.control_plane', false)) {
                return redirect()->route('control.overview');
            }

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Use la URL del panel SaaS para iniciar sesión como administrador SaaS.']);
        }

        return $next($request);
    }
}
