<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Blocks /control/* on client silos (404 before auth redirect). */
final class EnsureControlPlaneHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('platform.control_plane', false)) {
            abort(404);
        }

        return $next($request);
    }
}
