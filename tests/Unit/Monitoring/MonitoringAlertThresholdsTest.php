<?php

declare(strict_types=1);

namespace Tests\Unit\Monitoring;

use App\Monitoring\Application\Services\MonitoringAlertThresholds;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MonitoringAlertThresholdsTest extends TestCase
{
    #[Test]
    public function from_config_reads_platform_monitoring_alert_defaults(): void
    {
        config([
            'platform_monitoring.alerts' => [
                'error_rate_percent' => 7.5,
                'latency_ms' => 1500,
                'dlq_unresolved_max' => 5,
                'bus_stopped_minutes' => 3,
                'database_usage_percent' => 70.0,
                'queue_depth_max' => 500,
            ],
        ]);

        $thresholds = MonitoringAlertThresholds::fromConfig();

        $this->assertSame(7.5, $thresholds->errorRatePercent());
        $this->assertSame(1500, $thresholds->latencyMs());
        $this->assertSame(5, $thresholds->dlqUnresolvedMax());
        $this->assertSame(3, $thresholds->busStoppedMinutes());
        $this->assertSame(70.0, $thresholds->databaseUsagePercent());
        $this->assertSame(500, $thresholds->queueDepthMax());
    }

    #[Test]
    public function defaults_apply_when_config_keys_are_missing(): void
    {
        config(['platform_monitoring.alerts' => []]);

        $thresholds = MonitoringAlertThresholds::fromConfig();

        $this->assertSame(10.0, $thresholds->errorRatePercent());
        $this->assertSame(2000, $thresholds->latencyMs());
    }
}
