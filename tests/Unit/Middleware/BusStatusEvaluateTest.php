<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Domain\ValueObjects\BusStatus;
use App\Middleware\Domain\ValueObjects\ErrorRate;
use App\Middleware\Domain\ValueObjects\LatencyMs;
use App\Middleware\Domain\ValueObjects\ThroughputEps;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BusStatusEvaluateTest extends TestCase
{
    #[Test]
    public function idle_healthy_bus_is_active_not_stopped(): void
    {
        $status = BusStatus::evaluate(
            errorRate: ErrorRate::zero(),
            eps: ThroughputEps::zero(),
            latency: LatencyMs::of(0),
            deadLetterCount: 0,
        );

        $this->assertSame(BusStatus::ACTIVE, $status->value());
    }

    #[Test]
    public function high_error_rate_is_degraded(): void
    {
        $status = BusStatus::evaluate(
            errorRate: new ErrorRate(15.0),
            eps: ThroughputEps::of(5),
            latency: LatencyMs::of(10),
            deadLetterCount: 0,
        );

        $this->assertSame(BusStatus::DEGRADED, $status->value());
    }

    #[Test]
    public function high_throughput_is_hi_load(): void
    {
        $status = BusStatus::evaluate(
            errorRate: ErrorRate::zero(),
            eps: ThroughputEps::of(150),
            latency: LatencyMs::of(10),
            deadLetterCount: 0,
        );

        $this->assertSame(BusStatus::HI_LOAD, $status->value());
    }
}
