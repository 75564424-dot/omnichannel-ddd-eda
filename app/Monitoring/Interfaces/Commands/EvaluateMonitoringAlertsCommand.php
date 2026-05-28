<?php

declare(strict_types=1);

namespace App\Monitoring\Interfaces\Commands;

use App\Monitoring\Application\Services\AlertEvaluationService;
use App\Monitoring\Domain\ValueObjects\AlertSeverity;
use App\Shared\Logging\PlatformStructuredLogger;
use Illuminate\Console\Command;

final class EvaluateMonitoringAlertsCommand extends Command
{
    protected $signature = 'platform:monitoring-evaluate
                            {--json : Output fired alerts as JSON}';

    protected $description = 'Evaluates monitoring alert rules and logs actionable alerts (Plan_Monitoreo)';

    public function handle(
        AlertEvaluationService $evaluator,
        PlatformStructuredLogger $logger,
    ): int {
        $alerts = $evaluator->evaluate();

        if ($this->option('json')) {
            $this->line(json_encode(array_map(fn ($a) => $a->toArray(), $alerts), JSON_THROW_ON_ERROR));

            return $alerts === [] ? self::SUCCESS : self::FAILURE;
        }

        if ($alerts === []) {
            $this->info('No monitoring alerts fired.');

            return self::SUCCESS;
        }

        foreach ($alerts as $alert) {
            $line = sprintf('[%s] %s: %s (current=%s threshold=%s)',
                $alert->severity->value,
                $alert->name,
                $alert->message,
                $alert->currentValue,
                $alert->threshold,
            );

            if ($alert->severity === AlertSeverity::P1) {
                $logger->error('Monitoring alert fired', $alert->toArray());
                $this->error($line);
            } else {
                $logger->warning('Monitoring alert fired', $alert->toArray());
                $this->warn($line);
            }
        }

        $hasP1 = false;
        foreach ($alerts as $alert) {
            if ($alert->severity === AlertSeverity::P1) {
                $hasP1 = true;
                break;
            }
        }

        return $hasP1 ? self::FAILURE : self::SUCCESS;
    }
}
