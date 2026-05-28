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
 * Plan_Calidad — event_store append idempotency via publisher guard.
 */
final class EventStoreIdempotencyIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function duplicate_publish_creates_single_event_store_row(): void
    {
        $eventId  = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        $envelope = [
            'event_id'    => $eventId,
            'event_type'  => 'Platform.Quality.EventStore',
            'occurred_at' => $occurred,
            'payload'     => [
                'event_id'    => $eventId,
                'event'       => 'Platform.Quality.EventStore',
                'occurred_at' => $occurred,
            ],
        ];

        /** @var EventPublisherService $publisher */
        $publisher = app(EventPublisherService::class);

        $publisher->publish($envelope);
        $publisher->publish($envelope);

        $this->assertSame(1, DB::table('event_store')->where('event_uuid', $eventId)->count());
    }
}
