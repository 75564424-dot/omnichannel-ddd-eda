<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use App\Shared\Platform\Services\ClientSimulationService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientSimulationDeadlineTest extends TestCase
{
    #[Test]
    public function resolve_publish_plan_caps_total_by_duration(): void
    {
        $plan = ClientSimulationService::resolvePublishPlan(0, 30, 2);

        $this->assertSame(60, $plan['total']);
        $this->assertSame(2_000_000, $plan['interval_microseconds']);
    }
}
