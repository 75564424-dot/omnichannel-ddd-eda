<?php

declare(strict_types=1);

namespace Tests\Unit\Monitoring;

use App\Monitoring\Application\Services\DatabaseCapacityChecker;
use App\Monitoring\Application\Services\Evaluators\InfrastructureAlertEvaluator;
use App\Monitoring\Application\Services\MonitoringAlertThresholds;
use App\Monitoring\Application\Services\QueueDepthChecker;
use App\Monitoring\Domain\ValueObjects\AlertSeverity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class InfrastructureAlertEvaluatorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function evaluate_fires_queue_backlog_when_jobs_exceed_threshold(): void
    {
        config([
            'queue.default' => 'database',
            'platform_monitoring.queues.names' => ['default'],
        ]);

        for ($i = 0; $i < 5; $i++) {
            DB::table('jobs')->insert([
                'queue' => 'default',
                'payload' => '{}',
                'attempts' => 0,
                'reserved_at' => null,
                'available_at' => now()->timestamp,
                'created_at' => now()->timestamp,
            ]);
        }

        $evaluator = new InfrastructureAlertEvaluator(
            new DatabaseCapacityChecker(),
            new QueueDepthChecker(),
        );
        $thresholds = new MonitoringAlertThresholds([
            'database_usage_percent' => 100.0,
            'queue_depth_max' => 3,
        ]);

        $alerts = $evaluator->evaluate($thresholds);

        $this->assertCount(1, $alerts);
        $this->assertSame('QueueBacklog', $alerts[0]->name);
        $this->assertSame(AlertSeverity::P2, $alerts[0]->severity);
        $this->assertSame(5, $alerts[0]->currentValue);
    }

    #[Test]
    public function evaluate_returns_empty_when_infrastructure_is_healthy(): void
    {
        config([
            'queue.default' => 'database',
            'platform_monitoring.queues.names' => ['default'],
            'platform_monitoring.database.max_size_mb' => 10240,
        ]);

        $evaluator = new InfrastructureAlertEvaluator(
            new DatabaseCapacityChecker(),
            new QueueDepthChecker(),
        );

        $this->assertSame([], $evaluator->evaluate(new MonitoringAlertThresholds([
            'database_usage_percent' => 99.0,
            'queue_depth_max' => 1000,
        ])));
    }
}
