<?php

declare(strict_types=1);

namespace Tests\Unit\Observability;

use App\Observability\Application\Services\Prometheus\PrometheusGaugeCollector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PrometheusGaugeCollectorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function collect_returns_snapshot_with_non_negative_gauges(): void
    {
        $snapshot = app(PrometheusGaugeCollector::class)->collect();

        $this->assertGreaterThanOrEqual(0, $snapshot->publishedTotal);
        $this->assertGreaterThanOrEqual(0, $snapshot->processingLatencyMs);
        $this->assertGreaterThanOrEqual(0.0, $snapshot->errorRatePercent);
        $this->assertContains($snapshot->streamStatus, [0, 1, 2, 3]);
        $this->assertGreaterThanOrEqual(0, $snapshot->dlqUnresolved);
        $this->assertGreaterThanOrEqual(0, $snapshot->feedProjectionLagMs);
        $this->assertGreaterThanOrEqual(0, $snapshot->sseActiveConnections);
        $this->assertGreaterThanOrEqual(0.0, $snapshot->databaseUsagePercent);
        $this->assertGreaterThanOrEqual(0, $snapshot->queueJobsPending);
    }
}
