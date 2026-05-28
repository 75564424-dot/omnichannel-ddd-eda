<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Middleware\Domain\Entities\DeadLetterEntry;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class ResilienceApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function duplicate_publish_returns_200_idempotent(): void
    {
        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();
        $body = [
            'event_id'    => $eventId,
            'event_type'  => 'Platform.Resilience.Test',
            'occurred_at' => $occurred,
            'payload'     => [
                'event_id'    => $eventId,
                'event'       => 'Platform.Resilience.Test',
                'occurred_at' => $occurred,
            ],
        ];

        $this->postJson('/api/middleware/events/publish', $body)->assertCreated();
        $this->postJson('/api/middleware/events/publish', $body)
            ->assertOk()
            ->assertJsonPath('status', 'already_processed');
    }

    #[Test]
    public function dead_letter_can_be_requeued(): void
    {
        $eventId = Uuid::uuid4()->toString();

        /** @var DeadLetterRepositoryInterface $repo */
        $repo = app(DeadLetterRepositoryInterface::class);
        $repo->save(DeadLetterEntry::fromFailedJob(
            eventId: $eventId,
            eventType: 'Platform.Resilience.Test',
            origin: 'Test',
            payload: [
                'event_id'    => $eventId,
                'event'       => 'Platform.Resilience.Test',
                'occurred_at' => now()->toIso8601String(),
            ],
            failureReason: 'simulated failure',
        ));

        $dlqId = (int) \Illuminate\Support\Facades\DB::table('dead_letter_queue')
            ->where('event_uuid', $eventId)
            ->value('id');

        $this->postJson("/api/middleware/dead-letters/{$dlqId}/requeue")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('dead_letter_queue', [
            'id'                => $dlqId,
            'resolution_action' => 'requeue',
        ]);
    }

    #[Test]
    public function publish_records_retry_attempt_row(): void
    {
        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        $this->postJson('/api/middleware/events/publish', [
            'event_id'    => $eventId,
            'event_type'  => 'Platform.Resilience.Test',
            'occurred_at' => $occurred,
            'payload'     => [
                'event_id'    => $eventId,
                'event'       => 'Platform.Resilience.Test',
                'occurred_at' => $occurred,
            ],
        ])->assertCreated();

        $this->assertGreaterThanOrEqual(
            1,
            \Illuminate\Support\Facades\DB::table('retries')->where('event_uuid', $eventId)->count()
        );
    }
}
