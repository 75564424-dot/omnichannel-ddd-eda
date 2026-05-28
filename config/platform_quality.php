<?php

declare(strict_types=1);

return [

    'coverage' => [
        'application_min_percent' => (int) env('PLATFORM_QUALITY_COVERAGE_MIN', 70),
        'clover_path'             => env('PLATFORM_QUALITY_CLOVER_PATH', 'build/coverage/clover.xml'),
    ],

    'load_test' => [
        'target_eps'        => (int) env('PLATFORM_LOAD_TEST_EPS', 100),
        'duration_seconds'  => (int) env('PLATFORM_LOAD_TEST_DURATION', 60),
        'max_error_rate'    => (float) env('PLATFORM_LOAD_TEST_MAX_ERROR_RATE', 0.05),
        'p95_latency_ms'    => (int) env('PLATFORM_LOAD_TEST_P95_MS', 2000),
        'publish_path'      => '/api/middleware/events/publish',
        'event_type'        => env('PLATFORM_LOAD_TEST_EVENT_TYPE', 'Platform.Quality.LoadTest'),
    ],

    'ui_e2e' => [
        'pages' => [
            ['path' => '/login', 'title_contains' => 'Login'],
            ['path' => '/dashboard', 'status' => 200],
            ['path' => '/middleware', 'status' => 200],
        ],
    ],

    'security_scan' => [
        'zap_baseline_enabled' => env('PLATFORM_ZAP_BASELINE_ENABLED', true),
        'staging_target_path'    => '/up',
    ],

    'runner' => [
        'primary' => 'phpunit',
        'note'    => 'Pest is installed for future migration; PHPUnit is the enforced CI runner.',
    ],

];
