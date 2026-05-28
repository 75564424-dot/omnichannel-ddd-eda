<?php

declare(strict_types=1);

/**
 * Platform authentication — operators (session) + integrators (tokens/keys).
 *
 * @see docs/production/Plan_Autenticacion.md
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Web session auth (Inertia UI)
    |--------------------------------------------------------------------------
    */
    'web_auth_enabled' => env('PLATFORM_WEB_AUTH_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default operator admin (seed)
    |--------------------------------------------------------------------------
    */
    'seed_admin_operator' => env('PLATFORM_SEED_ADMIN_OPERATOR', true),

    'admin_operator' => [
        'name'          => env('PLATFORM_ADMIN_NAME', 'Platform Admin'),
        'email'         => env('PLATFORM_ADMIN_EMAIL', 'admin@local'),
        'password'      => env('PLATFORM_ADMIN_PASSWORD', 'password'),
        'platform_role' => env('PLATFORM_ADMIN_ROLE', 'platform_admin'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SaaS control plane operator (your company — manages all tenants)
    |--------------------------------------------------------------------------
    */
    'seed_saas_operator' => env('PLATFORM_SEED_SAAS_OPERATOR', true),

    'saas_operator' => [
        'name'     => env('PLATFORM_SAAS_ADMIN_NAME', 'SaaS Admin'),
        'email'    => env('PLATFORM_SAAS_ADMIN_EMAIL', 'saas@local'),
        'password' => env('PLATFORM_SAAS_ADMIN_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy flat operator abilities (superseded by platform_roles.php RBAC)
    |--------------------------------------------------------------------------
    */
    'operator_abilities' => [
        'events:publish',
        'bus:read',
        'bus:admin',
        'dashboard:read',
        'integrations:admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | CI / smoke service token (optional pre-seed via env)
    |--------------------------------------------------------------------------
    */
    'smoke_service_token' => env('PLATFORM_SMOKE_SERVICE_TOKEN'),

];
