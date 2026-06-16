<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use App\Shared\Platform\Services\ClientSimulationService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientSimulationFixedCountTest extends TestCase
{
    #[Test]
    public function resolve_publish_plan_fixes_total_for_rate_times_duration(): void
    {
        $plan = ClientSimulationService::resolvePublishPlan(0, 10, 1);

        $this->assertSame(10, $plan['total']);
        $this->assertSame(6_000_000, $plan['interval_microseconds']);
    }
}
