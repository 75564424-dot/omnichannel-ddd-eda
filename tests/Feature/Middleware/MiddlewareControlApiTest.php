<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

/**
 * HTTP surface for Control Middleware — queue, topology, metrics, publish, event lookup.
 */
final class MiddlewareControlApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function get_queue_returns_success_payload(): void
    {
        $this->getJson('/api/middleware/queue')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'count']);
    }

    #[Test]
    public function get_topology_includes_observed_registry_payload(): void
    {
        $this->getJson('/api/middleware/topology')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'producers',
                    'bus',
                    'consumers',
                    'generated_at',
                    'observed' => [
                        'producers',
                        'consumers',
                        'connections',
                    ],
                ],
            ]);
    }

    #[Test]
    public function post_registry_sync_config_persists_catalog_modules(): void
    {
        config()->set('eventbus.producers', [
            'retail_pos' => [
                'label'    => 'Retail POS',
                'produces' => ['Platform.Registry.SyncDemo'],
            ],
        ]);
        config()->set('eventbus.subscriptions', [
            'Platform.Registry.SyncDemo' => [
                ['module' => 'IntegrationPack.Adapters'],
            ],
        ]);
        config()->set('modules.catalog', [
            'producers'   => [],
            'subscribers' => [],
        ]);

        $this->postJson('/api/middleware/registry/sync-config')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.producer_bindings', 1)
            ->assertJsonPath('data.consumer_bindings', 1);

        $this->assertDatabaseHas('registered_modules', [
            'logical_id' => 'retail_pos',
            'type'       => 'producer',
            'name'       => 'Retail POS',
        ]);
        $this->assertDatabaseHas('registered_modules', [
            'logical_id' => 'integrationpack.adapters',
            'type'       => 'consumer',
            'name'       => 'IntegrationPack.Adapters',
        ]);
    }

    #[Test]
    public function post_registry_sync_config_includes_declarative_modules_catalog_when_eventbus_empty(): void
    {
        config()->set('eventbus.producers', []);
        config()->set('eventbus.subscriptions', []);

        $catalog = config('modules.catalog', []);
        if (! is_array($catalog)) {
            $catalog = [];
        }
        $catalog['producers'] = [
            [
                'id'                    => 'partner_api',
                'name'                  => 'Partner API',
                'event_types_emitted'   => ['Platform.Declarative.RegistryOnly'],
            ],
        ];
        $catalog['subscribers'] = [
            [
                'id'                     => 'analytics_sink',
                'name'                   => 'Analytics sink',
                'event_types_consumed'   => ['Platform.Declarative.RegistryOnly'],
            ],
        ];
        config()->set('modules.catalog', $catalog);

        $this->postJson('/api/middleware/registry/sync-config')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.producer_bindings', 1)
            ->assertJsonPath('data.consumer_bindings', 1);

        $this->assertDatabaseHas('registered_modules', [
            'logical_id' => 'partner_api',
            'type'       => 'producer',
            'name'       => 'Partner API',
        ]);
        $this->assertDatabaseHas('registered_modules', [
            'logical_id' => 'analytics_sink',
            'type'       => 'consumer',
            'name'       => 'Analytics sink',
        ]);
    }

    #[Test]
    public function post_registry_sync_config_dedupes_identical_binding_from_eventbus_and_modules_catalog(): void
    {
        $eventType = 'Platform.Registry.DedupDemo';

        config()->set('eventbus.producers', [
            'retail_pos' => [
                'label'    => 'Retail POS',
                'produces' => [$eventType],
            ],
        ]);
        config()->set('eventbus.subscriptions', [
            $eventType => [
                ['module' => 'IntegrationPack.Adapters'],
            ],
        ]);

        $catalog = config('modules.catalog', []);
        if (! is_array($catalog)) {
            $catalog = [];
        }
        $catalog['producers'] = [
            [
                'id'                    => 'retail_pos',
                'name'                  => 'Retail POS (declarativo)',
                'event_types_emitted'   => [$eventType],
            ],
        ];
        $catalog['subscribers'] = [
            [
                'id'                     => 'IntegrationPack.Adapters',
                'name'                   => 'IntegrationPack.Adapters',
                'event_types_consumed'   => [$eventType],
            ],
        ];
        config()->set('modules.catalog', $catalog);

        $this->postJson('/api/middleware/registry/sync-config')
            ->assertStatus(200)
            ->assertJsonPath('data.producer_bindings', 1)
            ->assertJsonPath('data.consumer_bindings', 1);
    }

    #[Test]
    public function get_status_returns_bus_status_string(): void
    {
        $this->getJson('/api/middleware/status')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['bus_status']);
    }

    #[Test]
    public function get_metrics_and_refresh_return_snapshots(): void
    {
        $this->getJson('/api/middleware/metrics')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data']);

        $this->postJson('/api/middleware/metrics/refresh')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['latency_ms', 'events_per_second', 'error_rate', 'dead_letters', 'bus_status', 'recorded_at']]);
    }

    #[Test]
    public function post_publish_validation_error_when_envelope_incomplete(): void
    {
        $response = $this->postJson('/api/middleware/events/publish', [
            'event_id' => Uuid::uuid4()->toString(),
        ])->assertStatus(422);

        if (config('platform_api.problem_details.enabled', true)) {
            $response->assertHeader('Content-Type', 'application/problem+json')
                ->assertJsonStructure(['type', 'title', 'status']);
        } else {
            $response->assertJsonPath('success', false);
        }
    }

    #[Test]
    public function post_publish_then_get_event_by_id_returns_tracking_row(): void
    {
        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        $eventType = 'Platform.HttpPublish.Probe';

        $this->postJson('/api/middleware/events/publish', [
            'event_id'    => $eventId,
            'event_type'  => $eventType,
            'occurred_at' => $occurred,
            'payload'     => [
                'event_id'    => $eventId,
                'event'       => $eventType,
                'occurred_at' => $occurred,
                'ref'         => 'HTTP-PUB',
            ],
        ])->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->getJson("/api/middleware/events/{$eventId}")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.event_id', $eventId)
            ->assertJsonPath('data.event_type', $eventType)
            ->assertJsonPath('data.status', 'PROCESADO');
    }

    #[Test]
    public function get_unknown_event_id_returns_404(): void
    {
        $unknown = Uuid::uuid4()->toString();

        $this->getJson("/api/middleware/events/{$unknown}")
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function get_dead_letters_returns_list_envelope(): void
    {
        $this->getJson('/api/middleware/dead-letters')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'count']);
    }
}
