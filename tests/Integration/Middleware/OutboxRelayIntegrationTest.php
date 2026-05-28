<?php

declare(strict_types=1);

namespace Tests\Integration\Middleware;

use App\Middleware\Application\Services\EventPublisherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class OutboxRelayIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function outbox_mode_relays_to_bus_and_marks_published(): void
    {
        config()->set('eventbus.outbox.enabled', true);

        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        /** @var EventPublisherService $publisher */
        $publisher = app(EventPublisherService::class);
        $publisher->publish([
            'event_id'    => $eventId,
            'event_type'  => 'Platform.Outbox.Test',
            'occurred_at' => $occurred,
            'payload'     => [
                'event_id'    => $eventId,
                'event'       => 'Platform.Outbox.Test',
                'occurred_at' => $occurred,
            ],
        ]);

        $this->assertDatabaseHas('outbox_messages', [
            'event_uuid' => $eventId,
            'status'     => 'published',
        ]);

        $this->assertDatabaseHas('message_queue', [
            'event_uuid' => $eventId,
        ]);
    }
}
