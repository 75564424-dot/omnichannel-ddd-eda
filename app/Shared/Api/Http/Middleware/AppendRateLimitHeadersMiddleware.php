<?php

declare(strict_types=1);

namespace App\Shared\Api\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Normalizes X-RateLimit-* headers on API responses (Plan_APIs).
 */
final class AppendRateLimitHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! str_starts_with($request->path(), 'api/')) {
            return $response;
        }

        foreach (['Limit', 'Remaining'] as $suffix) {
            $value = $response->headers->get("X-Ratelimit-{$suffix}")
                ?? $response->headers->get("X-RateLimit-{$suffix}");

            if ($value !== null) {
                $response->headers->set("X-RateLimit-{$suffix}", $value);
            }
        }

        return $response;
    }
}
