<?php

declare(strict_types=1);

/**
 * RBAC matrix — roles to platform abilities (Plan_Usuarios.md).
 *
 * Abilities align with route middleware `platform.ability:*` and API token scopes.
 */
return [

    'default_role' => 'platform_admin',

    'roles' => [
        'saas_admin' => [
            'control:read',
            'control:manage',
            'tenants:manage',
            'users:manage',
        ],
        'platform_admin' => [
            'events:publish',
            'bus:read',
            'bus:admin',
            'dashboard:read',
            'integrations:admin',
        ],
        'bus_operator' => [
            'events:publish',
            'bus:read',
            'bus:admin',
            'dashboard:read',
            'integrations:admin',
        ],
        'dashboard_viewer' => [
            'bus:read',
            'dashboard:read',
        ],
    ],

    /*
    | api_integrator — M2M only via Sanctum token / X-API-Key scopes (not a UI role).
    */

];
