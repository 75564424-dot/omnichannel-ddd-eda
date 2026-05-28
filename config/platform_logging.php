<?php

declare(strict_types=1);

/**
 * Platform logging policy (Plan_Logs.md).
 */
return [

    'levels' => [
        'local'      => env('LOG_LEVEL', 'debug'),
        'testing'    => 'warning',
        'staging'    => env('LOG_LEVEL', 'info'),
        'production' => env('LOG_LEVEL', 'info'),
    ],

    /*
    | Cloud: LOG_STACK=stderr_json ships JSON to container stderr (CloudWatch, Loki, ELK).
    */
    'cloud_stack' => env('LOG_CLOUD_STACK', 'stderr_json'),

    'context_fields' => [
        'correlation_id',
        'event_uuid',
        'tenant_id',
        'actor_id',
        'event_type',
        'origin',
    ],

    /*
    | Keys redacted or hashed before writing to laravel.log
    */
    'redact_keys' => [
        'password',
        'token',
        'api_key',
        'secret',
        'authorization',
        'credential',
        'encrypted_value',
    ],

];
