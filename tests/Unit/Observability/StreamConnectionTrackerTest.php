<?php

declare(strict_types=1);

namespace Tests\Unit\Observability;

use App\Observability\Application\Services\StreamConnectionTracker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StreamConnectionTrackerTest extends TestCase
{
    #[Test]
    public function tracks_active_sse_connections(): void
    {
        $tracker = new StreamConnectionTracker();

        $this->assertSame(0, $tracker->activeCount());

        $tracker->increment();
        $tracker->increment();
        $this->assertSame(2, $tracker->activeCount());

        $tracker->decrement();
        $this->assertSame(1, $tracker->activeCount());

        $tracker->decrement();
        $tracker->decrement();
        $this->assertSame(0, $tracker->activeCount());
    }
}
