<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Domain\ValueObjects\EventStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventStatusTest extends TestCase
{
    #[Test]
    public function factories_match_predicate_helpers(): void
    {
        $this->assertTrue(EventStatus::pending()->isPending());
        $this->assertTrue(EventStatus::procesado()->isProcessed());
        $this->assertTrue(EventStatus::fallido()->isFailed());
    }

    #[Test]
    public function unknown_raw_defaults_to_pending(): void
    {
        $s = new EventStatus('UNKNOWN_STATE');
        $this->assertTrue($s->isPending());
    }
}
