<?php

declare(strict_types=1);

namespace App\Http\Application\Security;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies configured security headers to HTTP responses.
 */
final class SecurityHeadersApplicator
{
    public function __construct(
        private readonly SecurityHeadersCspBuilder $cspBuilder,
    ) {}

    /**
     * @param array<string, mixed> $headers
     */
    public function apply(Request $request, Response $response, array $headers): Response
    {
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

        $csp = $this->cspBuilder->build($headers);
        if ($csp !== '') {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        if ($request->isSecure() && ! empty($headers['strict_transport_security'])) {
            $response->headers->set('Strict-Transport-Security', (string) $headers['strict_transport_security']);
        }

        return $response;
    }
}
