<?php

declare(strict_types=1);

namespace Tests\Unit\Monitoring;

use App\Middleware\Domain\ReadModels\BusMetricsSnapshot;
use App\Middleware\Domain\ValueObjects\BusStatus;
use App\Middleware\Domain\ValueObjects\ErrorRate;
use App\Middleware\Domain\ValueObjects\LatencyMs;
use App\Middleware\Domain\ValueObjects\ThroughputEps;
use App\Monitoring\Application\Services\Evaluators\BusMetricsAlertEvaluator;
use App\Monitoring\Application\Services\MonitoringAlertThresholds;
use App\Monitoring\Domain\ValueObjects\AlertSeverity;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BusMetricsAlertEvaluatorTest extends TestCase
{
    #[Test]
    public function evaluate_returns_no_alerts_when_metrics_are_healthy(): void
    {
        $evaluator = new BusMetricsAlertEvaluator();
        $thresholds = new MonitoringAlertThresholds([
            'error_rate_percent' => 10.0,
            'latency_ms' => 2000,
            'dlq_unresolved_max' => 10,
        ]);

        $alerts = $evaluator->evaluate($this->snapshot(errorRate: 1.0, latency: 100, dlq: 0), $thresholds);

        $this->assertSame([], $alerts);
    }

    #[Test]
    public function evaluate_fires_high_error_rate_as_p1(): void
    {
        $evaluator = new BusMetricsAlertEvaluator();
        $thresholds = new MonitoringAlertThresholds(['error_rate_percent' => 5.0]);

        $alerts = $evaluator->evaluate($this->snapshot(errorRate: 12.5), $thresholds);

        $this->assertCount(1, $alerts);
        $this->assertSame('HighErrorRate', $alerts[0]->name);
        $this->assertSame(AlertSeverity::P1, $alerts[0]->severity);
    }

    #[Test]
    public function evaluate_fires_latency_and_dlq_alerts_as_p2(): void
    {
        $evaluator = new BusMetricsAlertEvaluator();
        $thresholds = new MonitoringAlertThresholds([
            'latency_ms' => 500,
            'dlq_unresolved_max' => 3,
        ]);

        $alerts = $evaluator->evaluate($this->snapshot(latency: 900, dlq: 5), $thresholds);

        $this->assertCount(2, $alerts);
        $this->assertSame('HighLatency', $alerts[0]->name);
        $this->assertSame(AlertSeverity::P2, $alerts[0]->severity);
        $this->assertSame('DLQBacklog', $alerts[1]->name);
    }

    private function snapshot(
        float $errorRate = 0.0,
        int $latency = 50,
        int $dlq = 0,
    ): BusMetricsSnapshot {
        return new BusMetricsSnapshot(
            latencyMs: new LatencyMs($latency),
            eventsPerSecond: new ThroughputEps(10),
            errorRate: new ErrorRate($errorRate),
            deadLettersCount: $dlq,
            busStatus: BusStatus::active(),
            recordedAt: now()->toIso8601String(),
        );
    }
}
