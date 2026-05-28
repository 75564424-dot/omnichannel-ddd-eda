<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Middleware\Infrastructure\Persistence\Models\MiddlewareRegisteredModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\Fixtures\Middleware\E2ECountedConsumerListener;
use Tests\TestCase;

/**
 * Regresión B.2 (sync + JSON), publicación, cola/topología y coherencia con API de catálogo (Dashboard).
 */
final class MiddlewarePipelineEndToEndTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function post_registry_sync_config_is_idempotent_for_persisted_module_rows(): void
    {
        config()->set('eventbus.producers', [
            'retail_pos' => [
                'label'    => 'Retail POS',
                'produces' => ['Platform.E2E.Idempotent'],
            ],
        ]);
        config()->set('eventbus.subscriptions', [
            'Platform.E2E.Idempotent' => [
                ['module' => 'Pack.Consumer.A'],
            ],
        ]);

        $this->postJson('/api/middleware/registry/sync-config')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(
            1,
            MiddlewareRegisteredModule::query()->where('logical_id', 'retail_pos')->where('type', 'producer')->count()
        );
        $this->assertSame(
            1,
            MiddlewareRegisteredModule::query()->where('logical_id', 'pack.consumer.a')->where('type', 'consumer')->count()
        );

        $this->postJson('/api/middleware/registry/sync-config')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(
            1,
            MiddlewareRegisteredModule::query()->where('logical_id', 'retail_pos')->where('type', 'producer')->count()
        );
        $this->assertSame(
            1,
            MiddlewareRegisteredModule::query()->where('logical_id', 'pack.consumer.a')->where('type', 'consumer')->count()
        );

        $producer = MiddlewareRegisteredModule::query()
            ->where('logical_id', 'retail_pos')
            ->where('type', 'producer')
            ->first();
        $this->assertNotNull($producer);
        $types = $producer->event_types;
        $this->assertIsArray($types);
        $this->assertSame(['Platform.E2E.Idempotent'], array_values(array_unique($types)));
    }

    #[Test]
    public function post_registry_sync_config_from_declarative_catalog_only_remains_stable_on_second_sync(): void
    {
        config()->set('eventbus.producers', []);
        config()->set('eventbus.subscriptions', []);

        $catalog = is_array(config('modules.catalog', [])) ? config('modules.catalog', []) : [];
        $catalog['producers'] = [
            [
                'id'                    => 'e2e_api_only',
                'name'                  => 'E2E API producer',
                'event_types_emitted'   => ['Platform.E2E.JsonRegistry'],
            ],
        ];
        $catalog['subscribers'] = [
            [
                'id'                     => 'e2e_sink',
                'name'                   => 'E2E sink',
                'event_types_consumed'   => ['Platform.E2E.JsonRegistry'],
            ],
        ];
        config()->set('modules.catalog', $catalog);

        $this->postJson('/api/middleware/registry/sync-config')->assertOk();
        $this->postJson('/api/middleware/registry/sync-config')->assertOk();

        $this->assertSame(1, MiddlewareRegisteredModule::query()->where('logical_id', 'e2e_api_only')->count());
        $this->assertSame(1, MiddlewareRegisteredModule::query()->where('logical_id', 'e2e_sink')->count());
    }

    #[Test]
    public function post_publish_invokes_subscribed_string_listener(): void
    {
        $eventType = 'Platform.E2E.PublishWithListener';

        config()->set('eventbus.subscriptions', [
            $eventType => [
                ['module' => 'E2E.Subscriber'],
            ],
        ]);

        E2ECountedConsumerListener::reset();
        Event::listen($eventType, E2ECountedConsumerListener::class);

        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        $this->postJson('/api/middleware/events/publish', [
            'event_id'    => $eventId,
            'event_type'  => $eventType,
            'occurred_at' => $occurred,
            'origin'      => 'E2ETest',
            'payload'     => [
                'event_id'    => $eventId,
                'event'       => $eventType,
                'occurred_at' => $occurred,
            ],
        ])->assertCreated();

        $this->assertSame(1, E2ECountedConsumerListener::$invocations);

        $this->getJson("/api/middleware/events/{$eventId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'PROCESADO')
            ->assertJsonPath('data.event_type', $eventType);
    }

    #[Test]
    public function full_flow_modules_config_sync_publish_exposed_in_queue_topology_and_dashboard_catalog(): void
    {
        $eventType = 'Platform.E2E.FullFlow';

        $catalog = is_array(config('modules.catalog', [])) ? config('modules.catalog', []) : [];
        $catalog['producers'] = [
            [
                'id'                    => 'full_flow_producer',
                'name'                  => 'Full flow producer',
                'event_types_emitted'   => [$eventType],
            ],
        ];
        $catalog['subscribers'] = [
            [
                'id'                     => 'full_flow_sink',
                'name'                   => 'Full flow sink',
                'event_types_consumed'   => [$eventType],
            ],
        ];
        config()->set('modules.catalog', $catalog);

        config()->set('eventbus.producers', [
            'full_flow_producer' => [
                'label'    => 'Full flow producer',
                'produces' => [$eventType],
            ],
        ]);
        config()->set('eventbus.subscriptions', [
            $eventType => [
                ['module' => 'full_flow_sink'],
            ],
        ]);

        $this->postJson('/api/middleware/registry/sync-config')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/api/dashboard/modules/catalog')
            ->assertOk()
            ->assertJsonPath('producers.0.id', 'full_flow_producer')
            ->assertJsonPath('subscribers.0.id', 'full_flow_sink');

        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        $this->postJson('/api/middleware/events/publish', [
            'event_id'    => $eventId,
            'event_type'  => $eventType,
            'occurred_at' => $occurred,
            'origin'      => 'FullFlowTest',
            'payload'     => [
                'event_id'    => $eventId,
                'event'       => $eventType,
                'occurred_at' => $occurred,
            ],
        ])->assertCreated();

        $queueResponse = $this->getJson('/api/middleware/queue?limit=20');
        $queueResponse
            ->assertOk()
            ->assertJsonPath('success', true);

        $queuePayload = $queueResponse->json('data');
        $this->assertIsArray($queuePayload);
        $ids = array_column($queuePayload, 'event_id');
        $this->assertContains($eventId, $ids);

        $this->getJson('/api/middleware/topology')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'producers',
                    'bus',
                    'consumers',
                    'generated_at',
                    'observed',
                ],
            ]);

        $this->getJson('/api/dashboard/snapshot')
            ->assertOk()
            ->assertJsonStructure([
                'metrics',
                'feed',
                'nodes',
                'bus',
            ]);
    }
}
