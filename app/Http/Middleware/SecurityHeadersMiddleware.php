<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic security headers (Plan_Seguridad Fase 1).
 */
final class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! config('security.headers.enabled', true)) {
            return $response;
        }

        $headers = config('security.headers', []);

        if (! empty($headers['x_frame_options'])) {
            $response->headers->set('X-Frame-Options', (string) $headers['x_frame_options']);
        }

        if (! empty($headers['x_content_type_options'])) {
            $response->headers->set('X-Content-Type-Options', (string) $headers['x_content_type_options']);
        }

        if (! empty($headers['referrer_policy'])) {
            $response->headers->set('Referrer-Policy', (string) $headers['referrer_policy']);
        }

        if (! empty($headers['permissions_policy'])) {
            $response->headers->set('Permissions-Policy', (string) $headers['permissions_policy']);
        }

        $csp = $this->contentSecurityPolicy($headers);
        if ($csp !== '') {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        if ($request->isSecure() && ! empty($headers['strict_transport_security'])) {
            $response->headers->set('Strict-Transport-Security', (string) $headers['strict_transport_security']);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $headers
     */
    private function contentSecurityPolicy(array $headers): string
    {
        $csp = trim((string) ($headers['content_security_policy'] ?? ''));
        if ($csp === '') {
            return '';
        }

        if (env('PLATFORM_HEADER_CSP') !== null && env('PLATFORM_HEADER_CSP') !== '') {
            return $csp;
        }

        if (! app()->environment('local')) {
            return $csp;
        }

        $viteOrigins = 'http://127.0.0.1:5173 http://localhost:5173';

        return str_replace(
            [
                "script-src 'self' 'unsafe-inline'",
                "style-src 'self' 'unsafe-inline'",
                "font-src 'self' data:",
                "connect-src 'self'",
            ],
            [
                "script-src 'self' 'unsafe-inline' {$viteOrigins}",
                "style-src 'self' 'unsafe-inline' {$viteOrigins}",
                "font-src 'self' data: {$viteOrigins}",
                "connect-src 'self' {$viteOrigins} ws://127.0.0.1:5173 ws://localhost:5173",
            ],
            $csp,
        );
    }
}
