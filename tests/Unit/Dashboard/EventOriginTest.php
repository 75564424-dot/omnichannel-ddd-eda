<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard;

use App\Dashboard\Domain\ValueObjects\EventOrigin;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventOriginTest extends TestCase
{
    #[Test]
    public function maps_channel_hints_to_labels(): void
    {
        $this->assertSame('POS', EventOrigin::fromEventPayload('Any.Type', ['channel' => 'POS'])->value());
        $this->assertSame('Web', EventOrigin::fromEventPayload('Any.Type', ['channel' => 'WEB'])->value());
    }

    #[Test]
    public function prefers_explicit_origin_fields(): void
    {
        $this->assertSame('ERP-West', EventOrigin::fromEventPayload('Any.Type', [
            'origin' => 'ERP-West',
            'channel' => 'WEB',
        ])->value());
    }

    #[Test]
    public function unknown_event_type_without_channel_maps_to_unknown_origin(): void
    {
        $this->assertSame('Unknown', EventOrigin::fromEventPayload('FooBar', [])->value());
    }
}
