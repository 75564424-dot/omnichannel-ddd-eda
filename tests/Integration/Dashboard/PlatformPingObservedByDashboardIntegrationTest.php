<?php

declare(strict_types=1);

namespace Tests\Integration\Dashboard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

/**
 * Simulates an external producer: string-event + payload only (no in-process domain module).
 */
final class PlatformPingObservedByDashboardIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function platform_ping_is_recorded_in_event_feed(): void
    {
        $id   = Uuid::uuid4()->toString();
        $body = [
            'event_id'    => $id,
            'channel'     => 'EXTERNAL',
            'occurred_at' => now()->toIso8601String(),
            'message'     => 'mock',
        ];

        Event::dispatch('PlatformPing', [$body]);

        $this->assertDatabaseHas('event_feed_projections', [
            'event_uuid' => $id,
            'event_type' => 'PlatformPing',
        ]);
    }
}
