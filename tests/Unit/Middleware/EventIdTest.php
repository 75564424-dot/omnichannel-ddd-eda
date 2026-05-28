<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Domain\ValueObjects\EventId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventIdTest extends TestCase
{
    #[Test]
    public function rejects_empty_identifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new EventId('   ');
    }

    #[Test]
    public function equals_matches_by_value(): void
    {
        $a = new EventId('evt-1');
        $b = new EventId('evt-1');
        $this->assertTrue($a->equals($b));
    }
}
