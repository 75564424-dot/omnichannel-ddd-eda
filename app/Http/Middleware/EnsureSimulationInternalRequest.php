<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSimulationInternalRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('platform.control_plane', false)) {
            abort(404);
        }

        $expected = (string) config('platform.simulation.internal_token', '');
        if ($expected === '') {
            abort(503, 'Simulation internal API is not configured.');
        }

        $provided = (string) $request->header('X-Simulation-Internal-Token', '');
        if (! hash_equals($expected, $provided)) {
            abort(403, 'Invalid simulation internal token.');
        }

        $ip = $request->ip();
        if ($ip !== null && $ip !== '127.0.0.1' && $ip !== '::1') {
            abort(403, 'Simulation internal API is restricted to localhost.');
        }

        return $next($request);
    }
}
