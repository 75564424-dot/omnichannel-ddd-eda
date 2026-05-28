<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects unauthenticated visitors to login when web auth is enabled.
 */
final class EnsurePlatformWebAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('platform_auth.web_auth_enabled', true)) {
            return $next($request);
        }

        if ($request->user() === null) {
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}
