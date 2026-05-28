<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services;

use App\Middleware\Application\Services\BusHealthService;
use App\Middleware\Domain\ValueObjects\BusStatus;
use App\Monitoring\Domain\ValueObjects\AlertSeverity;
use App\Monitoring\Domain\ValueObjects\MonitoringAlert;
use App\Shared\Persistence\BusStatusMetricMapper;
use Illuminate\Support\Facades\Cache;

/**
 * Evaluates bus and infrastructure alerts against configured thresholds (Plan_Monitoreo).
 */
final class AlertEvaluationService
{
    private const BUS_STOPPED_CACHE_KEY = 'platform.monitoring.bus_stopped_since';

    public function __construct(
        private readonly BusHealthService $busHealth,
        private readonly DatabaseCapacityChecker $databaseCapacity,
        private readonly QueueDepthChecker $queueDepth,
    ) {}

    /** @return list<MonitoringAlert> */
    public function evaluate(): array
    {
        if (! config('platform_monitoring.enabled', true)) {
            return [];
        }

        $alerts = [];
        $snapshot = $this->busHealth->getLatestSnapshot();
        $thresholds = config('platform_monitoring.alerts', []);

        $errorRate = $snapshot->errorRate->value();
        if ($errorRate > (float) ($thresholds['error_rate_percent'] ?? 10.0)) {
            $alerts[] = new MonitoringAlert(
                name: 'HighErrorRate',
                severity: AlertSeverity::P1,
                message: 'Bus error rate exceeded threshold',
                currentValue: $errorRate,
                threshold: (float) ($thresholds['error_rate_percent'] ?? 10.0),
            );
        }

        $latency = $snapshot->latencyMs->value();
        if ($latency > (int) ($thresholds['latency_ms'] ?? 2000)) {
            $alerts[] = new MonitoringAlert(
                name: 'HighLatency',
                severity: AlertSeverity::P2,
                message: 'Bus processing latency exceeded threshold',
                currentValue: $latency,
                threshold: (int) ($thresholds['latency_ms'] ?? 2000),
            );
        }

        $dlq = $snapshot->deadLettersCount;
        if ($dlq > (int) ($thresholds['dlq_unresolved_max'] ?? 10)) {
            $alerts[] = new MonitoringAlert(
                name: 'DLQBacklog',
                severity: AlertSeverity::P2,
                message: 'Unresolved dead-letter queue entries exceeded threshold',
                currentValue: $dlq,
                threshold: (int) ($thresholds['dlq_unresolved_max'] ?? 10),
            );
        }

        $status = $snapshot->busStatus->value();
        if ($status === BusStatus::STOPPED) {
            $this->trackBusStopped($alerts, $thresholds);
        } else {
            Cache::forget(self::BUS_STOPPED_CACHE_KEY);
        }

        $dbUsage = $this->databaseCapacity->usagePercent();
        if ($dbUsage > (float) ($thresholds['database_usage_percent'] ?? 80.0)) {
            $alerts[] = new MonitoringAlert(
                name: 'DiskSpace',
                severity: AlertSeverity::P2,
                message: 'Database storage usage exceeded threshold',
                currentValue: $dbUsage,
                threshold: (float) ($thresholds['database_usage_percent'] ?? 80.0),
            );
        }

        $queuePending = $this->queueDepth->totalPending();
        if ($queuePending > (int) ($thresholds['queue_depth_max'] ?? 1000)) {
            $alerts[] = new MonitoringAlert(
                name: 'QueueBacklog',
                severity: AlertSeverity::P2,
                message: 'Laravel queue depth exceeded threshold',
                currentValue: $queuePending,
                threshold: (int) ($thresholds['queue_depth_max'] ?? 1000),
            );
        }

        return $alerts;
    }

    /** @param array<string, mixed> $thresholds */
    private function trackBusStopped(array &$alerts, array $thresholds): void
    {
        $minutes = (int) ($thresholds['bus_stopped_minutes'] ?? 5);
        $since   = Cache::get(self::BUS_STOPPED_CACHE_KEY);

        if ($since === null) {
            Cache::put(self::BUS_STOPPED_CACHE_KEY, now()->timestamp, now()->addHours(24));

            return;
        }

        $elapsedMinutes = (now()->timestamp - (int) $since) / 60;
        if ($elapsedMinutes >= $minutes) {
            $alerts[] = new MonitoringAlert(
                name: 'BusStopped',
                severity: AlertSeverity::P1,
                message: 'Bus stream_status has been STOPPED beyond threshold',
                currentValue: (int) round($elapsedMinutes),
                threshold: $minutes,
            );
        }
    }

    public function busStreamStatusNumeric(): int
    {
        return BusStatusMetricMapper::toNumeric(
            $this->busHealth->getLatestSnapshot()->busStatus->value(),
        );
    }

    public function currentErrorRate(): float
    {
        return $this->busHealth->getLatestSnapshot()->errorRate->value();
    }
}
