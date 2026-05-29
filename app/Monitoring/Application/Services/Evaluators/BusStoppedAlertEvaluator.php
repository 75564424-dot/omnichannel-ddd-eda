<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services\Evaluators;

use App\Middleware\Domain\ValueObjects\BusStatus;
use App\Monitoring\Application\Services\MonitoringAlertThresholds;
use App\Monitoring\Domain\ValueObjects\AlertSeverity;
use App\Monitoring\Domain\ValueObjects\MonitoringAlert;
use Illuminate\Support\Facades\Cache;

final class BusStoppedAlertEvaluator
{
    private const CACHE_KEY = 'platform.monitoring.bus_stopped_since';

    /**
     * @return list<MonitoringAlert>
     */
    public function evaluate(string $busStatus, MonitoringAlertThresholds $thresholds): array
    {
        if ($busStatus === BusStatus::STOPPED) {
            return $this->trackStopped($thresholds);
        }

        Cache::forget(self::CACHE_KEY);

        return [];
    }

    /**
     * @return list<MonitoringAlert>
     */
    private function trackStopped(MonitoringAlertThresholds $thresholds): array
    {
        $minutes = $thresholds->busStoppedMinutes();
        $since   = Cache::get(self::CACHE_KEY);

        if ($since === null) {
            Cache::put(self::CACHE_KEY, now()->timestamp, now()->addHours(24));

            return [];
        }

        $elapsedMinutes = (now()->timestamp - (int) $since) / 60;
        if ($elapsedMinutes < $minutes) {
            return [];
        }

        return [
            new MonitoringAlert(
                name: 'BusStopped',
                severity: AlertSeverity::P1,
                message: 'Bus stream_status has been STOPPED beyond threshold',
                currentValue: (int) round($elapsedMinutes),
                threshold: $minutes,
            ),
        ];
    }
}
