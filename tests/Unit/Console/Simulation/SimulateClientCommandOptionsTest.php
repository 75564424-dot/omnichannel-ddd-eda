<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Simulation;

use App\Console\Application\Services\Simulation\SimulateClientCommandOptions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulateClientCommandOptionsTest extends TestCase
{
    #[Test]
    public function publish_plan_uses_burst_mode_when_per_minute_is_absent(): void
    {
        $options = new SimulateClientCommandOptions(
            slug: 'retailco',
            events: 10,
            perMinute: null,
            durationMinutes: 1,
            applyFixture: false,
            skipSync: false,
            skipValidate: false,
        );

        $plan = $options->publishPlan();

        $this->assertSame(10, $plan['total']);
        $this->assertNull($plan['interval_microseconds']);
    }

    #[Test]
    public function publish_plan_computes_rate_and_duration(): void
    {
        $options = new SimulateClientCommandOptions(
            slug: 'acmepos',
            events: 0,
            perMinute: 10,
            durationMinutes: 5,
            applyFixture: false,
            skipSync: false,
            skipValidate: false,
        );

        $plan = $options->publishPlan();

        $this->assertSame(50, $plan['total']);
        $this->assertSame(6_000_000, $plan['interval_microseconds']);
    }
}
