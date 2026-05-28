<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Domain\ValueObjects\EventType;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EventTypeTest extends TestCase
{
    #[Test]
    public function rejects_empty_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new EventType('');
    }

    #[Test]
    public function known_types_follow_merged_subscription_config(): void
    {
        Config::set('eventbus.subscriptions', [
            'Demo.A' => [],
            'Demo.B' => [['module' => 'X']],
        ]);

        foreach (EventType::knownTypes() as $known) {
            $this->assertTrue((new EventType($known))->isKnown(), $known);
        }
    }

    #[Test]
    public function arbitrary_string_can_exist_outside_catalog(): void
    {
        Config::set('eventbus.subscriptions', []);
        $t = new EventType('CustomFutureEvent');
        $this->assertFalse($t->isKnown());
    }
}
