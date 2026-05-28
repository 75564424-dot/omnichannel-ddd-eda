<?php

declare(strict_types=1);

return [
    'prometheus_enabled' => env('PLATFORM_PROMETHEUS_ENABLED', true),
    'trace_spans_enabled' => env('PLATFORM_TRACE_SPANS_ENABLED', true),
    'service_name' => env('PLATFORM_OBSERVABILITY_SERVICE_NAME', env('PLATFORM_CLIENT_SLUG', 'middleware')),
];
