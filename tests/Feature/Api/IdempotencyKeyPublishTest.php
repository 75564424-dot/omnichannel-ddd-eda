<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class IdempotencyKeyPublishTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function idempotency_key_returns_same_response_on_replay(): void
    {
        $eventId = Uuid::uuid4()->toString();
        $key     = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        $body = [
            'event_id'    => $eventId,
            'event_type'  => 'Platform.Api.Idempotency',
            'occurred_at' => $occurred,
            'payload'     => [
                'event_id'    => $eventId,
                'event'       => 'Platform.Api.Idempotency',
                'occurred_at' => $occurred,
            ],
        ];

        $first = $this->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/middleware/events/publish', $body);

        $second = $this->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/middleware/events/publish', $body);

        $first->assertCreated();
        $second->assertCreated();
        $this->assertSame($first->json('entry_id'), $second->json('entry_id'));
    }
}
