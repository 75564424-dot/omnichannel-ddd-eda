<?php

declare(strict_types=1);

namespace App\Http\Application\Security;

use Illuminate\Contracts\Foundation\Application;

/**
 * Builds Content-Security-Policy header values from security config.
 */
final class SecurityHeadersCspBuilder
{
    public function __construct(
        private readonly Application $app,
    ) {}

    /**
     * @param array<string, mixed> $headers
     */
    public function build(array $headers): string
    {
        $csp = trim((string) ($headers['content_security_policy'] ?? ''));
        if ($csp === '') {
            return '';
        }

        if (env('PLATFORM_HEADER_CSP') !== null && env('PLATFORM_HEADER_CSP') !== '') {
            return $csp;
        }

        if (! $this->app->environment('local')) {
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
