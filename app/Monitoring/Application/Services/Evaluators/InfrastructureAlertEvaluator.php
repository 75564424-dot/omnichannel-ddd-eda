<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services\Evaluators;

use App\Monitoring\Application\Services\DatabaseCapacityChecker;
use App\Monitoring\Application\Services\MonitoringAlertThresholds;
use App\Monitoring\Application\Services\QueueDepthChecker;
use App\Monitoring\Domain\ValueObjects\AlertSeverity;
use App\Monitoring\Domain\ValueObjects\MonitoringAlert;

final class InfrastructureAlertEvaluator
{
    public function __construct(
        private readonly DatabaseCapacityChecker $databaseCapacity,
        private readonly QueueDepthChecker $queueDepth,
    ) {}

    /**
     * @return list<MonitoringAlert>
     */
    public function evaluate(MonitoringAlertThresholds $thresholds): array
    {
        $alerts = [];

        $dbUsage = $this->databaseCapacity->usagePercent();
        if ($dbUsage > $thresholds->databaseUsagePercent()) {
            $alerts[] = new MonitoringAlert(
                name: 'DiskSpace',
                severity: AlertSeverity::P2,
                message: 'Database storage usage exceeded threshold',
                currentValue: $dbUsage,
                threshold: $thresholds->databaseUsagePercent(),
            );
        }

        $queuePending = $this->queueDepth->totalPending();
        if ($queuePending > $thresholds->queueDepthMax()) {
            $alerts[] = new MonitoringAlert(
                name: 'QueueBacklog',
                severity: AlertSeverity::P2,
                message: 'Laravel queue depth exceeded threshold',
                currentValue: $queuePending,
                threshold: $thresholds->queueDepthMax(),
            );
        }

        return $alerts;
    }
}
