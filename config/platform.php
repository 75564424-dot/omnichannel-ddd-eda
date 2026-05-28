<?php

declare(strict_types=1);

/**
 * Platform instance identity — one deployable silo per commercial client (ADR-001).
 *
 * @see docs/production/ADR_001_instancia_por_cliente.md
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Deployment mode
    |--------------------------------------------------------------------------
    |
    | instance_per_client — default; one Laravel process = one client silo.
    | multi_tenant        — reserved for future ADR-002 (not active).
    |
    */
    'deployment_mode' => env('PLATFORM_DEPLOYMENT_MODE', 'instance_per_client'),

    /*
    |--------------------------------------------------------------------------
    | Client identity (this instance)
    |--------------------------------------------------------------------------
    */
    'client_slug' => env('PLATFORM_CLIENT_SLUG', 'default'),

    'client_name' => env('PLATFORM_CLIENT_NAME', env('APP_NAME', 'Platform Instance')),

    /*
    |--------------------------------------------------------------------------
    | Multi-tenant portal login (demo / local)
    |--------------------------------------------------------------------------
    |
    | When true, instance operators may sign in even if their tenant slug differs
    | from PLATFORM_CLIENT_SLUG. The active portal tenant is stored in session.
    | Disable in production (one silo per deploy).
    |
    */
    'multi_tenant_portal_login' => (bool) env('PLATFORM_PORTAL_MULTI_TENANT_LOGIN', false),

    /*
    |--------------------------------------------------------------------------
    | Control plane (SaaS registry on this host)
    |--------------------------------------------------------------------------
    |
    | When true, this deployment lists/manages many tenants (fleet registry).
    | Client-facing portal login for other slugs requires multi_tenant_portal_login.
    | Production client silos: false + matching PLATFORM_CLIENT_SLUG only.
    |
    */
    'control_plane' => (bool) env('PLATFORM_CONTROL_PLANE', false),

    /*
    |--------------------------------------------------------------------------
    | Dedicated instance URL template (SaaS UI hints)
    |--------------------------------------------------------------------------
    */
    'deployment' => [
        'app_url_template' => env('PLATFORM_INSTANCE_URL_TEMPLATE', 'https://{slug}.middleware.example.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Instance tenant seeding
    |--------------------------------------------------------------------------
    |
    | When true, artisan db:seed runs InstanceTenantSeeder to upsert the tenant row.
    |
    */
    'seed_instance_tenant' => env('PLATFORM_SEED_INSTANCE_TENANT', true),

    /*
    |--------------------------------------------------------------------------
    | Client simulation (prepare / run — see platform:simulation:prepare)
    |--------------------------------------------------------------------------
    */
    'simulation' => [
        'enabled'      => env('PLATFORM_SIMULATION_ENABLED', true),
        'fixture_slug' => env('PLATFORM_SIMULATION_FIXTURE_SLUG', 'acmepos'),
        'tenant_fixture_map' => [
            'acme-retail' => 'acmepos',
        ],
        'defaults' => [
            'events_per_minute' => (int) env('PLATFORM_SIMULATION_EVENTS_PER_MINUTE', 10),
            'duration_minutes'  => (int) env('PLATFORM_SIMULATION_DURATION_MINUTES', 1),
        ],
    ],

];
