<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard;

use App\Dashboard\Domain\ValueObjects\StreamStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StreamStatusTest extends TestCase
{
    #[Test]
    public function from_metrics_maps_idle_to_stopped(): void
    {
        $this->assertSame(StreamStatus::STOPPED, StreamStatus::fromMetrics(0, 0)->value());
    }

    #[Test]
    public function from_metrics_high_volume_becomes_degraded(): void
    {
        $this->assertSame(StreamStatus::DEGRADED, StreamStatus::fromMetrics(1001, 0)->value());
        $this->assertSame(StreamStatus::DEGRADED, StreamStatus::fromMetrics(10, 501)->value());
    }

    #[Test]
    public function from_metrics_normal_load_is_active(): void
    {
        $this->assertTrue(StreamStatus::fromMetrics(50, 100)->isActive());
    }

    #[Test]
    public function invalid_raw_status_defaults_to_stopped(): void
    {
        $this->assertSame(StreamStatus::STOPPED, (new StreamStatus('INVALID'))->value());
    }
}
