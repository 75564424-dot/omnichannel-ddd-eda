<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use App\Shared\Platform\Services\ClientSimulationService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientSimulationPublishPlanTest extends TestCase
{
    #[Test]
    public function burst_mode_uses_events_count_only(): void
    {
        $plan = ClientSimulationService::resolvePublishPlan(10, null, null);

        $this->assertSame(10, $plan['total']);
        $this->assertNull($plan['interval_microseconds']);
    }

    #[Test]
    public function per_minute_with_duration_computes_total_and_interval(): void
    {
        $plan = ClientSimulationService::resolvePublishPlan(0, 10, 5);

        $this->assertSame(50, $plan['total']);
        $this->assertSame(6_000_000, $plan['interval_microseconds']);
    }

    #[Test]
    public function per_minute_without_duration_defaults_to_one_minute_of_events(): void
    {
        $plan = ClientSimulationService::resolvePublishPlan(0, 10, null);

        $this->assertSame(10, $plan['total']);
        $this->assertSame(6_000_000, $plan['interval_microseconds']);
    }
}
