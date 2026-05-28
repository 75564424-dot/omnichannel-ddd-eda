<?php

declare(strict_types=1);

namespace Tests\Integration\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

/**
 * BusTrackingListener observes string events without modifying payloads.
 * Dispatch-only flow (no EventPublisherService): first handling writes completed; repeats are idempotent on the same row.
 */
final class BusTrackingDirectDispatchIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function skips_recording_when_payload_has_no_event_id(): void
    {
        $before = DB::table('message_queue')->count();

        Event::dispatch('Platform.Bus.IntegrationTest', [[
            'event'       => 'Platform.Bus.IntegrationTest',
            'occurred_at' => now()->toIso8601String(),
        ]]);

        $this->assertSame($before, DB::table('message_queue')->count());
    }

    #[Test]
    public function dispatches_create_single_queue_row_and_second_dispatch_is_idempotent(): void
    {
        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        $payload = [
            'event_id'         => $eventId,
            'event'            => 'Platform.Bus.IntegrationTest',
            'occurred_at'      => $occurred,
            'sku'              => 'MW-BUS-1',
            'trace_only_field' => 'must_remain',
        ];

        Event::dispatch('Platform.Bus.IntegrationTest', [$payload]);
        Event::dispatch('Platform.Bus.IntegrationTest', [$payload]);

        $this->assertSame(1, DB::table('message_queue')->where('event_uuid', $eventId)->count());

        $this->assertDatabaseHas('message_queue', [
            'event_uuid'   => $eventId,
            'message_type' => 'Platform.Bus.IntegrationTest',
            'status'       => 'completed',
        ]);

        $stored = DB::table('message_queue')->where('event_uuid', $eventId)->value('payload');
        $this->assertIsString($stored);
        $decoded = json_decode($stored, true);
        $this->assertSame($payload, $decoded);
    }
}
