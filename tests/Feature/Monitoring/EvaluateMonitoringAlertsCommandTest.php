<?php

declare(strict_types=1);

namespace Tests\Feature\Monitoring;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EvaluateMonitoringAlertsCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function monitoring_evaluate_returns_success_when_no_alerts(): void
    {
        $this->artisan('platform:monitoring-evaluate')
            ->assertSuccessful();
    }

    #[Test]
    public function monitoring_evaluate_json_outputs_array(): void
    {
        $this->artisan('platform:monitoring-evaluate', ['--json' => true])
            ->assertSuccessful();

        $this->artisan('platform:monitoring-evaluate --json')
            ->expectsOutputToContain('[')
            ->assertSuccessful();
    }
}
