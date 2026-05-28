<?php

declare(strict_types=1);

namespace Tests\Feature\Logging;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class CorrelationLogContextTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function publish_with_correlation_header_propagates_to_event_logs(): void
    {
        $eventId = Uuid::uuid4()->toString();
        $correlationId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        $this->withHeader('X-Correlation-Id', $correlationId)
            ->postJson('/api/middleware/events/publish', [
                'event_id'    => $eventId,
                'event_type'  => 'Platform.Logs.Correlation',
                'occurred_at' => $occurred,
                'payload'     => [
                    'event_id'    => $eventId,
                    'event'       => 'Platform.Logs.Correlation',
                    'occurred_at' => $occurred,
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('event_logs', [
            'event_uuid'     => $eventId,
            'correlation_id' => $correlationId,
        ]);
    }
}
