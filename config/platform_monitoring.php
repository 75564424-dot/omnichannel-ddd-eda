<?php

declare(strict_types=1);

return [

    'enabled' => env('PLATFORM_MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Alert thresholds (align with Plan_Monitoreo + config/eventbus.php)
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'error_rate_percent' => (float) env('PLATFORM_ALERT_ERROR_RATE_PERCENT', 10.0),
        'latency_ms'         => (int) env('PLATFORM_ALERT_LATENCY_MS', 2000),
        'dlq_unresolved_max' => (int) env('PLATFORM_ALERT_DLQ_MAX', 10),
        'bus_stopped_minutes' => (int) env('PLATFORM_ALERT_BUS_STOPPED_MINUTES', 5),
        'database_usage_percent' => (float) env('PLATFORM_ALERT_DB_USAGE_PERCENT', 80.0),
        'queue_depth_max'    => (int) env('PLATFORM_ALERT_QUEUE_DEPTH_MAX', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Synthetic canary (Fase 3)
    |--------------------------------------------------------------------------
    */
    'canary' => [
        'enabled'    => env('PLATFORM_CANARY_ENABLED', true),
        'event_type' => env('PLATFORM_CANARY_EVENT_TYPE', 'Platform.Monitoring.Canary'),
        'interval_minutes' => (int) env('PLATFORM_CANARY_INTERVAL_MINUTES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database capacity probe (DiskSpace alert)
    |--------------------------------------------------------------------------
    */
    'database' => [
        'max_size_mb' => (int) env('PLATFORM_DB_MAX_SIZE_MB', 10240),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitored queues for depth alert
    |--------------------------------------------------------------------------
    */
    'queues' => [
        'names' => array_filter(array_map('trim', explode(',', (string) env(
            'PLATFORM_MONITOR_QUEUES',
            'middleware,dashboard-feed,default'
        )))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Uptime external probes (documentation targets — Fase 1)
    |--------------------------------------------------------------------------
    */
    'uptime' => [
        'liveness_path'  => '/up',
        'readiness_path' => '/health/ready',
        'metrics_path'   => '/metrics',
    ],

];
