<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Domain\ValueObjects\ErrorRate;
use App\Middleware\Domain\ValueObjects\LatencyMs;
use App\Middleware\Domain\ValueObjects\ThroughputEps;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LatencyMsAndThroughputAndErrorRateTest extends TestCase
{
    #[Test]
    public function latency_ms_guardrails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LatencyMs(-1);
    }

    #[Test]
    public function latency_acceptance_buckets(): void
    {
        $this->assertTrue(LatencyMs::of(400)->isAcceptable());
        $this->assertTrue(LatencyMs::of(600)->isDegraded());
        $this->assertTrue(LatencyMs::of(2500)->isCritical());
    }

    #[Test]
    public function throughput_eps_high_load(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ThroughputEps::of(-2);
    }

    #[Test]
    public function throughput_eps_idle_vs_high(): void
    {
        $this->assertTrue(ThroughputEps::zero()->isIdle());
        $this->assertTrue(ThroughputEps::of(101)->isHighLoad());
    }

    #[Test]
    public function error_rate_compute_handles_zero_total(): void
    {
        $this->assertSame(0.0, ErrorRate::compute(5, 0)->value());
    }

    #[Test]
    public function error_rate_health_buckets(): void
    {
        $this->assertTrue((new ErrorRate(0.5))->isHealthy());
        $this->assertTrue((new ErrorRate(5.0))->isDegraded());
        $this->assertTrue((new ErrorRate(12.0))->isCritical());
    }
}
