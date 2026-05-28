<?php

declare(strict_types=1);

/**
 * Event bus — core platform. Catalog keys stay stable for external integration;
 * consumer rows are empty until an integration pack merges listeners.
 *
 * Client simulation overlay: config/eventbus_client_overlay.json (Plan_SimulacionClientes).
 *
 * @see \App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface
 */

use App\Platform\Demo\DemoPackEventConsumers;

$consumerRegistrars = [];

if (filter_var(env('DEMO_PACK_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
    $consumerRegistrars[] = DemoPackEventConsumers::class;
}

$subscriptions = [];
$producers     = [];

$overlayPath = __DIR__.'/eventbus_client_overlay.json';
if (is_readable($overlayPath)) {
    try {
        /** @var array<string, mixed> $overlay */
        $overlay = json_decode((string) file_get_contents($overlayPath), true, 512, JSON_THROW_ON_ERROR);
        if (is_array($overlay['producers'] ?? null)) {
            $producers = $overlay['producers'];
        }
        if (is_array($overlay['subscriptions'] ?? null)) {
            $subscriptions = $overlay['subscriptions'];
        }
        if (is_array($overlay['consumer_registrars'] ?? null)) {
            foreach ($overlay['consumer_registrars'] as $registrar) {
                if (is_string($registrar) && $registrar !== '') {
                    $consumerRegistrars[] = $registrar;
                }
            }
        }
    } catch (\JsonException) {
        // ignore invalid overlay — validate-catalog / simulate-client will surface issues
    }
}

return [

    'subscriptions' => $subscriptions,

    'consumer_registrars' => array_values(array_unique($consumerRegistrars)),

    'producers' => $producers,

    'thresholds' => [
        'high_load_eps'           => 100,
        'degraded_error_rate'     => 1.0,
        'critical_error_rate'     => 10.0,
        'critical_latency_ms'     => 2000,
        'dead_letter_alert'       => 1,
        'queue_retention_days'    => 30,
    ],

    'queues' => [
        'middleware' => 'middleware',
        'dashboard'  => 'dashboard-feed',
    ],

    'retry' => [
        'max_attempts' => 3,
        'backoff'      => [5, 30, 120],
    ],

    'resilience' => [
        'async_processing' => env('EVENTBUS_ASYNC_PROCESSING', false),
        'async_listeners'  => env('EVENTBUS_ASYNC_LISTENERS', false),
        'processing_timeout' => (int) env('EVENTBUS_PROCESSING_TIMEOUT', 30),
        'circuit_breaker' => [
            'enabled'           => env('EVENTBUS_CIRCUIT_BREAKER_ENABLED', false),
            'failure_threshold' => (int) env('EVENTBUS_CIRCUIT_BREAKER_FAILURES', 5),
            'open_seconds'      => (int) env('EVENTBUS_CIRCUIT_BREAKER_OPEN_SECONDS', 60),
        ],
    ],

    'schema_validation_enabled' => env('EVENTBUS_SCHEMA_VALIDATION', false),

    'schema_registry' => [
        'Platform.Smoke.Probe' => [
            'path'           => config_path('schemas/platform_smoke_probe.json'),
            'event_version'  => 1,
            'schema_version' => '2026-05-01',
        ],
    ],

    'publish_schemas' => [
        // Legacy map — prefer schema_registry
    ],

    'driver' => env('EVENTBUS_DRIVER', 'laravel'),

    'kafka' => [
        'brokers' => env('EVENTBUS_KAFKA_BROKERS', 'localhost:9092'),
        'topic'   => env('EVENTBUS_KAFKA_TOPIC', 'platform.events'),
    ],

    'outbox' => [
        'enabled' => env('EVENTBUS_OUTBOX_ENABLED', false),
    ],

    'workflows' => [
        'enabled' => env('EVENTBUS_WORKFLOWS_ENABLED', false),
    ],
];
