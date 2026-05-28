<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Domain\ValueObjects\EventOrigin;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MiddlewareEventOriginInferTest extends TestCase
{
    #[Test]
    public function infers_from_standard_channel_hints(): void
    {
        $this->assertSame(EventOrigin::WEB_GATEWAY, EventOrigin::inferFromPayload(['channel' => 'WEB'])->value());
        $this->assertSame(EventOrigin::RETAIL_POS, EventOrigin::inferFromPayload(['channel' => 'POS'])->value());
        $this->assertSame('Mobile', EventOrigin::inferFromPayload(['channel' => 'MOBILE'])->value());
        $this->assertSame('Partner API', EventOrigin::inferFromPayload(['channel' => 'PARTNER_API'])->value());
    }

    #[Test]
    public function infers_alias_for_unknown_uppercase_channel(): void
    {
        $this->assertSame('KIOSK', EventOrigin::inferFromPayload(['channel' => 'KIOSK'])->value());
    }

    #[Test]
    public function falls_back_to_unknown(): void
    {
        $this->assertSame(EventOrigin::UNKNOWN, EventOrigin::inferFromPayload([])->value());
    }
}
