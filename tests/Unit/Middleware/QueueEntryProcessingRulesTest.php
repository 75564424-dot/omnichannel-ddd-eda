<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Domain\Entities\QueueEntry;
use App\Middleware\Domain\ValueObjects\ConsumerList;
use App\Middleware\Domain\ValueObjects\EventStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class QueueEntryProcessingRulesTest extends TestCase
{
    #[Test]
    public function mark_processed_updates_timing_fields(): void
    {
        $published = new DateTimeImmutable('-5 seconds');

        $entry = QueueEntry::reconstitute(
            id:               1,
            eventId:          'q-e1',
            eventType:        'TestEvt',
            origin:           'External',
            consumers:        ConsumerList::of('A'),
            payload:          ['event_id' => 'q-e1'],
            status:           EventStatus::pending(),
            publishedAt:      $published,
            dispatchedAt:     null,
            processingTimeMs: null,
            attemptCount:     0,
        );

        $entry->markProcessed();

        $this->assertTrue($entry->status()->isProcessed());
        $this->assertNotNull($entry->dispatchedAt());
        $this->assertNotNull($entry->processingTimeMs());
        $this->assertGreaterThanOrEqual(0, $entry->processingTimeMs());
    }

    #[Test]
    public function mark_failed_increments_attempts(): void
    {
        $entry = QueueEntry::reconstitute(
            id:               2,
            eventId:          'q-e2',
            eventType:        'TestEvt',
            origin:           'External',
            consumers:        ConsumerList::empty(),
            payload:          [],
            status:           EventStatus::pending(),
            publishedAt:      new DateTimeImmutable(),
            dispatchedAt:     null,
            processingTimeMs: null,
            attemptCount:     1,
        );

        $entry->markFailed();

        $this->assertTrue($entry->status()->isFailed());
        $this->assertSame(2, $entry->attemptCount());
    }
}
