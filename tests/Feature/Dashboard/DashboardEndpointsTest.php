<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class DashboardEndpointsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function idle_dashboard_feed_reconciles_syncing_middleware_snapshot_online(): void
    {
        DB::table('channel_status_snapshots')->where('node_code', 'middleware')->update(['status' => 'SYNCING']);

        $this->getJson('/api/dashboard/nodes/status')
            ->assertOk()
            ->assertJsonPath('middleware.status', 'ONLINE');

        $this->assertDatabaseHas('channel_status_snapshots', ['node_code' => 'middleware', 'status' => 'ONLINE']);
    }

    #[Test]
    public function recent_dashboard_feed_entries_skip_syncing_reconciliation(): void
    {
        DB::table('event_feed_projections')->insert([
            'event_uuid'  => Uuid::uuid4()->toString(),
            'event_type'  => 'PlatformPing',
            'origin'      => 'External',
            'impact'      => '—',
            'status'      => 'SUCCESS',
            'occurred_at' => now(),
            'received_at' => now(),
            'raw_payload' => json_encode([]),
            'created_at'  => now(),
        ]);

        DB::table('channel_status_snapshots')->where('node_code', 'middleware')->update(['status' => 'SYNCING']);

        $this->getJson('/api/dashboard/nodes/status')
            ->assertOk()
            ->assertJsonPath('middleware.status', 'SYNCING');
    }

    #[Test]
    public function get_dashboard_metrics_returns_global_counters_shape(): void
    {
        $this->getJson('/api/dashboard/metrics')
            ->assertStatus(200)
            ->assertJsonStructure([
                'counters',
                'last_updated',
            ]);
    }

    #[Test]
    public function get_dashboard_metrics_catalog_returns_metrics_and_event_envelope(): void
    {
        $this->getJson('/api/dashboard/metrics/catalog')
            ->assertOk()
            ->assertJsonStructure([
                'metrics' => [
                    ['id', 'name', 'type', 'chart'],
                ],
                'event_envelope',
            ]);
    }

    #[Test]
    public function get_dashboard_metric_series_returns_chart_payload(): void
    {
        $this->getJson('/api/dashboard/metrics/series/demo_feed_sum_by_day?days=7')
            ->assertOk()
            ->assertJsonStructure([
                'metric_id',
                'title',
                'chart',
                'points',
                'days',
            ])
            ->assertJsonPath('metric_id', 'demo_feed_sum_by_day')
            ->assertJsonPath('days', 7);
    }

    #[Test]
    public function get_dashboard_metric_series_returns_404_for_unknown_metric(): void
    {
        $this->getJson('/api/dashboard/metrics/series/__no_such_metric__')
            ->assertNotFound()
            ->assertJsonPath('message', 'Metric not found or disabled');
    }

    #[Test]
    public function get_dashboard_modules_catalog_returns_normalized_topology_payload(): void
    {
        $this->getJson('/api/dashboard/modules/catalog')
            ->assertOk()
            ->assertJsonStructure([
                'producers',
                'subscribers',
                'available_producers',
                'available_subscribers',
                'visible_producer_ids',
                'visible_subscriber_ids',
                'middleware' => ['id', 'name', 'description', 'role'],
                'service_contact_message',
            ])
            ->assertJsonPath('producers.0.id', 'acme_pos')
            ->assertJsonPath('subscribers.0.id', 'acme_reporting')
            ->assertJsonPath('middleware.id', 'middleware');
    }

    #[Test]
    public function get_dashboard_events_feed_returns_list_wrapper(): void
    {
        $this->getJson('/api/dashboard/events/feed?limit=10')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'count']);
    }

    #[Test]
    public function get_dashboard_snapshot_returns_aggregate_payload(): void
    {
        $this->getJson('/api/dashboard/snapshot')
            ->assertStatus(200)
            ->assertJsonStructure([
                'metrics',
                'feed',
                'nodes',
                'bus',
                'primary_daily_series',
            ]);
    }

    #[Test]
    public function get_dashboard_metrics_flow_returns_diagram_payload(): void
    {
        $this->getJson('/api/dashboard/metrics/flow')
            ->assertStatus(200);
    }

    #[Test]
    public function get_dashboard_daily_series_respects_days_cap(): void
    {
        $this->getJson('/api/dashboard/metrics/daily-series?days=7')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'days'])
            ->assertJsonPath('days', 7);
    }

    #[Test]
    public function get_dashboard_nodes_returns_nested_payload(): void
    {
        $this->getJson('/api/dashboard/nodes/status')
            ->assertStatus(200)
            ->assertJsonStructure([
                'middleware' => ['status', 'middleware_events_enabled'],
                'last_updated',
            ]);
    }

    #[Test]
    public function refresh_node_returns_updated_status_snapshot(): void
    {
        $this->patchJson('/api/dashboard/nodes/middleware/middleware-events', [
            'middleware_events_enabled' => false,
        ])->assertStatus(200)->assertJsonPath('middleware.middleware_events_enabled', false);

        $this->postJson('/api/dashboard/nodes/middleware/refresh')
            ->assertStatus(200)
            ->assertJsonPath('middleware.status', 'ONLINE')
            ->assertJsonPath('middleware.middleware_events_enabled', true);
    }

    #[Test]
    public function patch_middleware_events_updates_flag_and_restores_default(): void
    {
        $this->patchJson('/api/dashboard/nodes/middleware/middleware-events', [
            'middleware_events_enabled' => false,
        ])
            ->assertStatus(200)
            ->assertJsonPath('middleware.middleware_events_enabled', false);

        $this->patchJson('/api/dashboard/nodes/middleware/middleware-events', [
            'middleware_events_enabled' => true,
        ])
            ->assertStatus(200)
            ->assertJsonPath('middleware.middleware_events_enabled', true);
    }

    #[Test]
    public function patch_middleware_events_validates_boolean(): void
    {
        $this->patchJson('/api/dashboard/nodes/middleware/middleware-events', [])
            ->assertStatus(422);
    }

    #[Test]
    public function patch_middleware_events_rejects_unknown_node(): void
    {
        $this->patchJson('/api/dashboard/nodes/unknown_node/middleware-events', [
            'middleware_events_enabled' => true,
        ])->assertStatus(404);
    }

    #[Test]
    public function get_dashboard_nodes_and_bus_endpoints_respond(): void
    {
        $this->getJson('/api/dashboard/nodes/status')->assertStatus(200);
        $this->getJson('/api/dashboard/middleware/bus')->assertStatus(200);
    }
}
