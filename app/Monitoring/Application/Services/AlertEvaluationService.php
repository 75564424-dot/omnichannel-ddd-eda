<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services;

use App\Middleware\Application\Services\BusHealthService;
use App\Monitoring\Application\Services\Evaluators\BusMetricsAlertEvaluator;
use App\Monitoring\Application\Services\Evaluators\BusStoppedAlertEvaluator;
use App\Monitoring\Application\Services\Evaluators\InfrastructureAlertEvaluator;
use App\Monitoring\Domain\ValueObjects\MonitoringAlert;
use App\Shared\Persistence\BusStatusMetricMapper;

/**
 * Evaluates bus and infrastructure alerts against configured thresholds (Plan_Monitoreo).
 */
final class AlertEvaluationService
{
    public function __construct(
        private readonly BusHealthService $busHealth,
        private readonly BusMetricsAlertEvaluator $busMetricsEvaluator,
        private readonly BusStoppedAlertEvaluator $busStoppedEvaluator,
        private readonly InfrastructureAlertEvaluator $infrastructureEvaluator,
    ) {}

    /** @return list<MonitoringAlert> */
    public function evaluate(): array
    {
        if (! config('platform_monitoring.enabled', true)) {
            return [];
        }

        $thresholds = MonitoringAlertThresholds::fromConfig();
        $snapshot   = $this->busHealth->getLatestSnapshot();

        return [
            ...$this->busMetricsEvaluator->evaluate($snapshot, $thresholds),
            ...$this->busStoppedEvaluator->evaluate($snapshot->busStatus->value(), $thresholds),
            ...$this->infrastructureEvaluator->evaluate($thresholds),
        ];
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
