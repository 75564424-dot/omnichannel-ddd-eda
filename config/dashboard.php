<?php

declare(strict_types=1);

/**
 * Observability dashboard — defaults without any external business module.
 * Host applications merge additional keys (nodes, hooks) from integration packs.
 *
 * Dynamic KPIs and charts: see dashboard_config.json (counter_cards, metrics, daily_series).
 */

$dashboardFile = __DIR__.'/dashboard_config.json';
$fileConfig    = [];
if (is_readable($dashboardFile)) {
    try {
        $fileConfig = json_decode((string) file_get_contents($dashboardFile), true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException) {
        $fileConfig = [];
    }
}

return [

    'monitored_node_keys' => ['middleware'],

    'transient_sync_nodes' => ['middleware'],

    'ingestion_gates' => [],

    /** @var list<class-string<\App\Dashboard\Domain\Hooks\AfterEventFeedInsertHookInterface>> */
    'after_feed_insert_hooks' => [],

    /** Merged from dashboard_config.json — optional daily feed aggregation (see daily_series) */
    'daily_series' => is_array($fileConfig['daily_series'] ?? null)
        ? $fileConfig['daily_series']
        : (is_array($fileConfig['revenue_chart'] ?? null) ? $fileConfig['revenue_chart'] : null),

    /** @var list<array<string, mixed>> */
    'counter_cards' => is_array($fileConfig['counter_cards'] ?? null) ? $fileConfig['counter_cards'] : [],

    /** @var list<array<string, mixed>> Chart definitions (bar, dual_bar, …) */
    'dynamic_metrics' => is_array($fileConfig['metrics'] ?? null) ? $fileConfig['metrics'] : [],

    /** Documentary contract for integrators (also in JSON). */
    'event_envelope_contract' => is_array($fileConfig['event_envelope'] ?? null) ? $fileConfig['event_envelope'] : [],

    'ui' => [
        'system_module_rows' => [
            ['key' => 'middleware', 'label' => 'Event Bus'],
        ],
    ],
];
