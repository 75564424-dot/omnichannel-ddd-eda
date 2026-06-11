<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Application\Security\SecurityHeadersApplicator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic security headers (Plan_Seguridad Fase 1).
 */
final class SecurityHeadersMiddleware
{
    public function __construct(
        private readonly SecurityHeadersApplicator $headersApplicator,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! config('security.headers.enabled', true)) {
            return $response;
        }

        return $this->headersApplicator->apply(
            $request,
            $response,
            config('security.headers', []),
        );
    }
}
