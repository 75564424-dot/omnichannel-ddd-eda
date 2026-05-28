<?php

declare(strict_types=1);

return [
    'instance' => env('PLATFORM_CLIENT_SLUG', 'default'),

    'slis' => [
        'bus_events_published_total' => [
            'description' => 'Events published to message_queue per hour window',
            'target'      => null,
            'unit'        => 'count',
        ],
        'bus_processing_latency_ms' => [
            'description' => 'Average queue processing latency',
            'target_p99_ms' => (int) env('PLATFORM_SLO_BUS_LATENCY_P99_MS', 2000),
            'unit'        => 'milliseconds',
        ],
        'bus_dlq_unresolved' => [
            'description' => 'Unresolved dead-letter entries',
            'target_max'  => (int) env('PLATFORM_SLO_DLQ_MAX', 10),
            'unit'        => 'count',
        ],
        'feed_projection_lag_ms' => [
            'description' => 'Average lag between event occurrence and feed projection',
            'target_p99_ms' => (int) env('PLATFORM_SLO_FEED_LAG_P99_MS', 3000),
            'unit'        => 'milliseconds',
        ],
        'sse_stream_connections_active' => [
            'description' => 'Active dashboard SSE connections',
            'target_max'  => (int) env('PLATFORM_SLO_SSE_MAX_CONNECTIONS', 500),
            'unit'        => 'count',
        ],
    ],
];
