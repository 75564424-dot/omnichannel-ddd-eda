<?php

declare(strict_types=1);

return [

    'version' => env('PLATFORM_API_VERSION', 'v1'),

    'legacy_prefix' => 'api',
    'versioned_prefix' => 'api/v1',

    'pagination' => [
        'default_limit' => (int) env('PLATFORM_API_DEFAULT_LIMIT', 50),
        'max_limit'     => (int) env('PLATFORM_API_MAX_LIMIT', 200),
    ],

    'idempotency' => [
        'enabled'    => env('PLATFORM_API_IDEMPOTENCY_ENABLED', true),
        'header'     => 'Idempotency-Key',
        'ttl_seconds'=> (int) env('PLATFORM_API_IDEMPOTENCY_TTL', 86400),
    ],

    'problem_details' => [
        'enabled' => env('PLATFORM_API_PROBLEM_DETAILS', true),
        'type_base' => env('PLATFORM_API_PROBLEM_TYPE_BASE', 'https://api.platform.local/problems'),
    ],

];
