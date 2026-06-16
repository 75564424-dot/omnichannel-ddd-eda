<?php

declare(strict_types=1);

namespace App\Monitoring\Interfaces\Commands;

use App\Monitoring\Application\Services\AlertEvaluationService;
use App\Monitoring\Application\Services\MonitoringAlertsConsoleReporter;
use Illuminate\Console\Command;

final class EvaluateMonitoringAlertsCommand extends Command
{
    protected $signature = 'platform:monitoring-evaluate
                            {--json : Output fired alerts as JSON}';

    protected $description = 'Evaluates monitoring alert rules and logs actionable alerts (Plan_Monitoreo)';

    public function handle(
        AlertEvaluationService $evaluator,
        MonitoringAlertsConsoleReporter $reporter,
    ): int {
        return $reporter->report($this, $evaluator->evaluate());
    }
}
