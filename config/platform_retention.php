<?php

declare(strict_types=1);

/**
 * Data retention defaults — Plan_BaseDeDatos.md.
 * Override at runtime via system_configurations (seeded by MiddlewareDatabaseSeeder).
 */
return [

    'tables' => [
        'message_queue'          => (int) env('RETENTION_MESSAGE_QUEUE_DAYS', 30),
        'event_logs'             => (int) env('RETENTION_EVENT_LOGS_DAYS', 30),
        'observability_metrics'  => (int) env('RETENTION_OBSERVABILITY_METRICS_DAYS', 14),
        'trace_logs'             => (int) env('RETENTION_TRACE_LOGS_DAYS', 14),
        'event_store'            => (int) env('RETENTION_EVENT_STORE_DAYS', 90),
        'audit_logs'             => (int) env('RETENTION_AUDIT_LOGS_DAYS', 2555),
    ],

    'config_key_prefix' => 'retention.',

];
