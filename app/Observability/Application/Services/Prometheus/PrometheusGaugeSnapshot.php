<?php

declare(strict_types=1);

namespace App\Observability\Application\Services\Prometheus;

/**
 * Live gauge values scraped into Prometheus text format.
 */
final class PrometheusGaugeSnapshot
{
    public function __construct(
        public readonly int $publishedTotal,
        public readonly int $processingLatencyMs,
        public readonly float $errorRatePercent,
        public readonly int $streamStatus,
        public readonly int $dlqUnresolved,
        public readonly int $feedProjectionLagMs,
        public readonly int $sseActiveConnections,
        public readonly float $databaseUsagePercent,
        public readonly int $queueJobsPending,
        public readonly int $canaryLastSuccessAgeSeconds,
    ) {}
}
