<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard;

use App\Dashboard\Domain\ReadModels\EventFeedEntry;
use App\Dashboard\Domain\ValueObjects\EventImpact;
use App\Dashboard\Domain\ValueObjects\EventOrigin;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventFeedEntryLatencyTest extends TestCase
{
    #[Test]
    public function latency_ms_is_non_negative_difference_between_received_and_occurred(): void
    {
        $entry = EventFeedEntry::project(
            eventId:    'evt-latency',
            eventType:  'PlatformPing',
            origin:     new EventOrigin('POS'),
            impact:     new EventImpact('PlatformPing'),
            occurredAt: '2026-05-01T10:00:00+00:00',
            rawPayload: [],
        );

        $this->assertGreaterThanOrEqual(0, $entry->latencyMs());
    }
}
