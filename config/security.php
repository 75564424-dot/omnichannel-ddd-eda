<?php

declare(strict_types=1);

/**
 * Platform security — auth, rate limits, headers, audit (Plan_Seguridad.md).
 */
return [

    /*
    |--------------------------------------------------------------------------
    | API authentication
    |--------------------------------------------------------------------------
    |
    | When false (e.g. phpunit), platform API routes skip token checks.
    | Production instances MUST enable this.
    |
    */
    'api_auth_enabled' => env('PLATFORM_API_AUTH_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Static API keys (M2M integrators)
    |--------------------------------------------------------------------------
    |
    | Format: key|ability1,ability2;another-key|bus:admin
    | Abilities: events:publish, bus:read, bus:admin, dashboard:read
    |
    */
    'api_keys' => env('PLATFORM_API_KEYS', ''),

    /*
    |--------------------------------------------------------------------------
    | Rate limits (requests per minute unless noted)
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'publish'     => (int) env('PLATFORM_RATE_LIMIT_PUBLISH', 100),
        'sync_config' => (int) env('PLATFORM_RATE_LIMIT_SYNC', 10),
        'stream'      => (int) env('PLATFORM_RATE_LIMIT_STREAM', 60),
        'default_api' => (int) env('PLATFORM_RATE_LIMIT_DEFAULT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'enabled'                  => env('PLATFORM_SECURITY_HEADERS', true),
        'x_frame_options'          => env('PLATFORM_HEADER_X_FRAME', 'SAMEORIGIN'),
        'x_content_type_options'   => env('PLATFORM_HEADER_X_CONTENT_TYPE', 'nosniff'),
        'referrer_policy'          => env('PLATFORM_HEADER_REFERRER', 'strict-origin-when-cross-origin'),
        'permissions_policy'       => env('PLATFORM_HEADER_PERMISSIONS', 'camera=(), microphone=(), geolocation=()'),
        'strict_transport_security'=> env('PLATFORM_HEADER_HSTS', 'max-age=31536000; includeSubDomains'),
        // Fonts are bundled via Vite (@fontsource/inter + @material-symbols/font-400) — no external CDN.
        // Local Vite dev origins are appended at runtime in SecurityHeadersMiddleware.
        'content_security_policy'  => env('PLATFORM_HEADER_CSP', "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; font-src 'self' data:; img-src 'self' data:; connect-src 'self'"),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit log
    |--------------------------------------------------------------------------
    */
    'audit_enabled' => env('PLATFORM_AUDIT_ENABLED', true),

];
