<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class MiddlewarePipelineTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function publish_propagates_correlation_id_from_header(): void
    {
        $eventId = Uuid::uuid4()->toString();
        $correlationId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        $this->withHeader('X-Correlation-Id', $correlationId)
            ->postJson('/api/middleware/events/publish', [
                'event_id'    => $eventId,
                'event_type'  => 'Platform.Pipeline.Test',
                'occurred_at' => $occurred,
                'payload'     => [
                    'event_id'    => $eventId,
                    'event'       => 'Platform.Pipeline.Test',
                    'occurred_at' => $occurred,
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('event_store', [
            'event_uuid'     => $eventId,
            'correlation_id' => $correlationId,
        ]);

        $this->assertDatabaseHas('message_queue', [
            'event_uuid'     => $eventId,
            'correlation_id' => $correlationId,
        ]);
    }

    #[Test]
    public function schema_validation_rejects_invalid_payload_when_enabled(): void
    {
        config()->set('eventbus.schema_validation_enabled', true);
        config()->set('eventbus.schema_registry', [
            'Platform.Schema.Test' => [
                'path'           => config_path('schemas/platform_smoke_probe.json'),
                'event_version'  => 1,
                'schema_version' => '2026-05-01',
            ],
        ]);

        $eventId = Uuid::uuid4()->toString();

        $this->postJson('/api/middleware/events/publish', [
            'event_id'    => $eventId,
            'event_type'  => 'Platform.Schema.Test',
            'occurred_at' => now()->toIso8601String(),
            'payload'     => ['invalid' => true],
        ])->assertStatus(422);
    }
}
