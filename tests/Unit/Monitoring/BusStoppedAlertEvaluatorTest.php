<?php

declare(strict_types=1);

namespace Tests\Unit\Monitoring;

use App\Middleware\Domain\ValueObjects\BusStatus;
use App\Monitoring\Application\Services\Evaluators\BusStoppedAlertEvaluator;
use App\Monitoring\Application\Services\MonitoringAlertThresholds;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BusStoppedAlertEvaluatorTest extends TestCase
{
    #[Test]
    public function evaluate_clears_cache_when_bus_is_not_stopped(): void
    {
        Cache::put('platform.monitoring.bus_stopped_since', now()->subMinutes(10)->timestamp, 3600);

        $evaluator = new BusStoppedAlertEvaluator();
        $alerts = $evaluator->evaluate(BusStatus::ACTIVE, new MonitoringAlertThresholds(['bus_stopped_minutes' => 5]));

        $this->assertSame([], $alerts);
        $this->assertNull(Cache::get('platform.monitoring.bus_stopped_since'));
    }

    #[Test]
    public function evaluate_waits_before_firing_bus_stopped_alert(): void
    {
        Cache::forget('platform.monitoring.bus_stopped_since');

        $evaluator = new BusStoppedAlertEvaluator();
        $thresholds = new MonitoringAlertThresholds(['bus_stopped_minutes' => 5]);

        $firstPass = $evaluator->evaluate(BusStatus::STOPPED, $thresholds);
        $this->assertSame([], $firstPass);

        Cache::put('platform.monitoring.bus_stopped_since', now()->subMinutes(6)->timestamp, 3600);
        $secondPass = $evaluator->evaluate(BusStatus::STOPPED, $thresholds);

        $this->assertCount(1, $secondPass);
        $this->assertSame('BusStopped', $secondPass[0]->name);
    }
}
