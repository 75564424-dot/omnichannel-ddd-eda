<?php

declare(strict_types=1);

namespace Tests\Integration\Middleware;

use App\Middleware\Application\Services\EventPublisherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

/**
 * Publisher validates structure, persists pending, dispatches to the bus;
 * BusTrackingListener completes the row. Duplicate event_uuid returns idempotent result (Plan_Resiliencia).
 */
final class EventPublisherServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function publish_inserts_pending_then_listener_marks_processed_after_sync_dispatch(): void
    {
        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        $innerPayload = [
            'event_id'    => $eventId,
            'event'       => 'Platform.Bus.IntegrationTest',
            'occurred_at' => $occurred,
            'sku'         => 'PUB-TST',
        ];

        /** @var EventPublisherService $publisher */
        $publisher = app(EventPublisherService::class);

        $result = $publisher->publish([
            'event_id'    => $eventId,
            'event_type'  => 'Platform.Bus.IntegrationTest',
            'occurred_at' => $occurred,
            'payload'     => $innerPayload,
            'origin'      => 'External',
        ]);

        $this->assertFalse($result->idempotent);

        $this->assertDatabaseHas('message_queue', [
            'event_uuid'   => $eventId,
            'message_type' => 'Platform.Bus.IntegrationTest',
            'status'       => 'completed',
        ]);

        $this->assertDatabaseHas('event_store', [
            'event_uuid' => $eventId,
            'event_type' => 'Platform.Bus.IntegrationTest',
        ]);

        $this->assertDatabaseHas('event_logs', [
            'event_uuid' => $eventId,
            'status'     => 'received',
        ]);

        $row = DB::table('message_queue')->where('event_uuid', $eventId)->first();
        $this->assertNotNull($row->dispatched_at);
        $this->assertNotNull($row->processing_time_ms);
    }

    #[Test]
    public function second_publish_with_same_event_id_is_idempotent(): void
    {
        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        $envelope = [
            'event_id'    => $eventId,
            'event_type'  => 'Platform.Bus.IntegrationTest',
            'occurred_at' => $occurred,
            'payload'     => [
                'event_id'    => $eventId,
                'event'       => 'Platform.Bus.IntegrationTest',
                'occurred_at' => $occurred,
            ],
        ];

        /** @var EventPublisherService $publisher */
        $publisher = app(EventPublisherService::class);
        $first = $publisher->publish($envelope);
        $second = $publisher->publish($envelope);

        $this->assertFalse($first->idempotent);
        $this->assertTrue($second->idempotent);
        $this->assertSame(1, DB::table('message_queue')->where('event_uuid', $eventId)->count());
    }
}
