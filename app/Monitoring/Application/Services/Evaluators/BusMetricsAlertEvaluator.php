<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services\Evaluators;

use App\Middleware\Domain\ReadModels\BusMetricsSnapshot;
use App\Monitoring\Application\Services\MonitoringAlertThresholds;
use App\Monitoring\Domain\ValueObjects\AlertSeverity;
use App\Monitoring\Domain\ValueObjects\MonitoringAlert;

final class BusMetricsAlertEvaluator
{
    /**
     * @return list<MonitoringAlert>
     */
    public function evaluate(BusMetricsSnapshot $snapshot, MonitoringAlertThresholds $thresholds): array
    {
        $alerts = [];

        $errorRate = $snapshot->errorRate->value();
        if ($errorRate > $thresholds->errorRatePercent()) {
            $alerts[] = new MonitoringAlert(
                name: 'HighErrorRate',
                severity: AlertSeverity::P1,
                message: 'Bus error rate exceeded threshold',
                currentValue: $errorRate,
                threshold: $thresholds->errorRatePercent(),
            );
        }

        $latency = $snapshot->latencyMs->value();
        if ($latency > $thresholds->latencyMs()) {
            $alerts[] = new MonitoringAlert(
                name: 'HighLatency',
                severity: AlertSeverity::P2,
                message: 'Bus processing latency exceeded threshold',
                currentValue: $latency,
                threshold: $thresholds->latencyMs(),
            );
        }

        $dlq = $snapshot->deadLettersCount;
        if ($dlq > $thresholds->dlqUnresolvedMax()) {
            $alerts[] = new MonitoringAlert(
                name: 'DLQBacklog',
                severity: AlertSeverity::P2,
                message: 'Unresolved dead-letter queue entries exceeded threshold',
                currentValue: $dlq,
                threshold: $thresholds->dlqUnresolvedMax(),
            );
        }

        return $alerts;
    }
}
