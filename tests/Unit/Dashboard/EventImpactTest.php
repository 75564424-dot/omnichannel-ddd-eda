<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard;

use App\Dashboard\Domain\ValueObjects\EventImpact;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventImpactTest extends TestCase
{
    #[Test]
    public function uses_impact_hint_when_present(): void
    {
        $impact = EventImpact::fromGenericEnvelope('AnyType', ['impact_hint' => '+42 orders']);
        $this->assertSame('+42 orders', $impact->value());
    }

    #[Test]
    public function formats_numeric_delta_when_present(): void
    {
        $this->assertSame('Δ +10', EventImpact::fromGenericEnvelope('X', ['delta' => 10])->value());
        $this->assertSame('Δ -3', EventImpact::fromGenericEnvelope('X', ['delta' => -3])->value());
    }

    #[Test]
    public function falls_back_to_event_type(): void
    {
        $impact = EventImpact::fromGenericEnvelope('Platform.External.Signal', []);
        $this->assertSame('Platform.External.Signal', $impact->value());
    }
}
