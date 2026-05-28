<?php

declare(strict_types=1);

namespace Tests\E2E\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

/**
 * Simulación tipo instancia cliente: catálogo declarativo + bus runtime,
 * varios tipos de evento, payloads heterogéneos (sin interpretación en middleware),
 * trazabilidad y API de observabilidad alineadas.
 */
final class ClientProductionLikeSimulationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function multi_event_type_client_sync_publish_queue_catalog_and_event_status_remain_consistent(): void
    {
        $typeOrder = 'Platform.E2E.Client.OrderPlaced';
        $typeStock = 'Platform.E2E.Client.StockAdjusted';

        $catalog = is_array(config('modules.catalog', [])) ? config('modules.catalog', []) : [];
        $catalog['producers'] = [
            [
                'id'                  => 'client_erp',
                'name'                => 'Client ERP',
                'event_types_emitted' => [$typeOrder, $typeStock],
            ],
        ];
        $catalog['subscribers'] = [
            [
                'id'                   => 'orders_module',
                'name'                 => 'Orders module',
                'event_types_consumed' => [$typeOrder],
            ],
            [
                'id'                   => 'inventory_module',
                'name'                 => 'Inventory module',
                'event_types_consumed' => [$typeStock],
            ],
        ];
        config()->set('modules.catalog', $catalog);

        config()->set('eventbus.producers', [
            'client_erp' => [
                'label'    => 'Client ERP',
                'produces' => [$typeOrder, $typeStock],
            ],
        ]);
        config()->set('eventbus.subscriptions', [
            $typeOrder => [['module' => 'orders_module']],
            $typeStock => [['module' => 'inventory_module']],
        ]);

        $this->postJson('/api/middleware/registry/sync-config')->assertOk()->assertJsonPath('success', true);

        $this->getJson('/api/dashboard/modules/catalog')
            ->assertOk()
            ->assertJsonPath('producers.0.id', 'client_erp')
            ->assertJsonPath('subscribers.0.id', 'orders_module')
            ->assertJsonPath('subscribers.1.id', 'inventory_module');

        $occurred = now()->toIso8601String();
        $idOrder = Uuid::uuid4()->toString();
        $idStock = Uuid::uuid4()->toString();

        $this->postJson('/api/middleware/events/publish', [
            'event_id'    => $idOrder,
            'event_type'  => $typeOrder,
            'occurred_at' => $occurred,
            'origin'      => 'ClientSimulationE2E',
            'payload'     => [
                'event_id'    => $idOrder,
                'event'       => $typeOrder,
                'occurred_at' => $occurred,
                'order_ref'   => 'PO-1001',
                'channel'     => 'b2b',
            ],
        ])->assertCreated();

        $this->postJson('/api/middleware/events/publish', [
            'event_id'    => $idStock,
            'event_type'  => $typeStock,
            'occurred_at' => $occurred,
            'origin'      => 'ClientSimulationE2E',
            'payload'     => [
                'event_id'    => $idStock,
                'event'       => $typeStock,
                'occurred_at' => $occurred,
                'sku'         => 'SKU-42',
                'delta'       => -3,
            ],
        ])->assertCreated();

        foreach ([$idOrder, $idStock] as $eventId) {
            $this->getJson("/api/middleware/events/{$eventId}")
                ->assertOk()
                ->assertJsonPath('data.status', 'PROCESADO');
        }

        $queue = $this->getJson('/api/middleware/queue?limit=50')->assertOk()->json('data');
        $this->assertIsArray($queue);
        $ids = array_column($queue, 'event_id');
        $this->assertContains($idOrder, $ids);
        $this->assertContains($idStock, $ids);

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

        $this->postJson('/api/middleware/registry/sync-config')
            ->assertOk()
            ->assertJsonPath('success', true);

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
