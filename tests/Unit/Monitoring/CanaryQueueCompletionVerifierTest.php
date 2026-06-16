<?php

declare(strict_types=1);

namespace Tests\Unit\Monitoring;

use App\Monitoring\Application\Services\Canary\CanaryQueueCompletionVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CanaryQueueCompletionVerifierTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function is_completed_returns_true_for_processed_status(): void
    {
        $eventId = 'canary-event-1';

        $this->assertDatabaseMissing('message_queue', ['event_uuid' => $eventId]);

        \DB::table('message_queue')->insert([
            'event_uuid' => $eventId,
            'message_type' => 'Platform.Monitoring.Canary',
            'status' => 'processed',
            'payload' => '{}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue((new CanaryQueueCompletionVerifier())->isCompleted($eventId));
    }

    #[Test]
    public function is_completed_returns_false_for_pending_status(): void
    {
        $eventId = 'canary-event-2';

        \DB::table('message_queue')->insert([
            'event_uuid' => $eventId,
            'message_type' => 'Platform.Monitoring.Canary',
            'status' => 'pending',
            'payload' => '{}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse((new CanaryQueueCompletionVerifier())->isCompleted($eventId));
    }
}
