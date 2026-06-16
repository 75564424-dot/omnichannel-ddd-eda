<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services;

/**
 * Typed view of platform_monitoring alert thresholds.
 */
final class MonitoringAlertThresholds
{
    public static function fromConfig(): self
    {
        $thresholds = config('platform_monitoring.alerts', []);

        return new self(is_array($thresholds) ? $thresholds : []);
    }

    /** @param array<string, mixed> $thresholds */
    public function __construct(
        private readonly array $thresholds,
    ) {}

    public function errorRatePercent(): float
    {
        return (float) ($this->thresholds['error_rate_percent'] ?? 10.0);
    }

    public function latencyMs(): int
    {
        return (int) ($this->thresholds['latency_ms'] ?? 2000);
    }

    public function dlqUnresolvedMax(): int
    {
        return (int) ($this->thresholds['dlq_unresolved_max'] ?? 10);
    }

    public function busStoppedMinutes(): int
    {
        return (int) ($this->thresholds['bus_stopped_minutes'] ?? 5);
    }

    public function databaseUsagePercent(): float
    {
        return (float) ($this->thresholds['database_usage_percent'] ?? 80.0);
    }

    public function queueDepthMax(): int
    {
        return (int) ($this->thresholds['queue_depth_max'] ?? 1000);
    }
}
