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
    'multi_tenant_portal_login' => filter_var(
        env('PLATFORM_PORTAL_MULTI_TENANT_LOGIN', false),
        FILTER_VALIDATE_BOOLEAN,
    ),

    /*
    |--------------------------------------------------------------------------
    | Friendly routing (path-based multi-tenant portal — v1.6 Fase 7)
    |--------------------------------------------------------------------------
    |
    | When true and control_plane=true, enables routes /{tenant_slug}/login
    | that redirect to the silo's port-based URL (ADR-011, Opción A).
    | Disabled by default; activate only on the control plane host.
    |
    */
    'friendly_routing' => filter_var(
        env('PLATFORM_FRIENDLY_ROUTING', false),
        FILTER_VALIDATE_BOOLEAN,
    ),

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
    'control_plane' => filter_var(
        env('PLATFORM_CONTROL_PLANE', false),
        FILTER_VALIDATE_BOOLEAN,
    ),

    'lifecycle_v15' => filter_var(
        env('PLATFORM_TENANT_LIFECYCLE_V15', true),
        FILTER_VALIDATE_BOOLEAN,
    ),

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
    | Local fleet (dev) — auto isolated instance per tenant on control plane
    |--------------------------------------------------------------------------
    */
    'local_fleet' => [
        'auto_provision'           => filter_var(
            env('PLATFORM_LOCAL_FLEET_AUTO_PROVISION', false),
            FILTER_VALIDATE_BOOLEAN,
        ),
        'registry_path'            => env('PLATFORM_LOCAL_FLEET_REGISTRY', 'deploy/local-instances/fleet-registry.json'),
        'port_range_start'         => (int) env('PLATFORM_LOCAL_FLEET_PORT_START', 8001),
        'default_admin_password'   => env('PLATFORM_LOCAL_FLEET_DEFAULT_ADMIN_PASSWORD', 'client-local-dev'),
        'control_plane_slug'       => env('PLATFORM_LOCAL_FLEET_CONTROL_SLUG', 'platform'),
        'stop_on_suspend'          => filter_var(
            env('PLATFORM_LOCAL_FLEET_STOP_ON_SUSPEND', false),
            FILTER_VALIDATE_BOOLEAN,
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Client simulation (prepare / run — see platform:simulation:prepare)
    |--------------------------------------------------------------------------
    */
    'simulation' => [
        'enabled'      => env('PLATFORM_SIMULATION_ENABLED', true),
        'fixture_slug' => env('PLATFORM_SIMULATION_FIXTURE_SLUG', 'acmepos'),
        'control_plane_url' => env('PLATFORM_CONTROL_PLANE_URL', 'http://127.0.0.1:8000'),
        'internal_token'    => env('PLATFORM_SIMULATION_INTERNAL_TOKEN', 'local-dev-simulation-token'),
        /** Fail RUNNING runs with zero progress after this many minutes (stale guard). */
        'no_progress_timeout_minutes' => (int) env('PLATFORM_SIMULATION_NO_PROGRESS_TIMEOUT_MINUTES', 5),
        /** Grace before no-progress timeout while the detached worker is starting. */
        'startup_grace_minutes' => (int) env('PLATFORM_SIMULATION_STARTUP_GRACE_MINUTES', 3),
        'tenant_fixture_map' => [
            'acme-retail'     => 'acmepos',
            'pruebas-retail'  => 'acmepos',
            'retail-norte'    => 'acmepos',
            'retail-sur'      => 'acmepos',
        ],
        'defaults' => [
            'events_per_minute' => (int) env('PLATFORM_SIMULATION_EVENTS_PER_MINUTE', 10),
            'duration_minutes'  => (int) env('PLATFORM_SIMULATION_DURATION_MINUTES', 1),
        ],
    ],

];
