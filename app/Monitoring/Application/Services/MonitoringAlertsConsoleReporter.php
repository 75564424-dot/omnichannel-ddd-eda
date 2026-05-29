<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services;

use App\Monitoring\Domain\ValueObjects\AlertSeverity;
use App\Monitoring\Domain\ValueObjects\MonitoringAlert;
use App\Shared\Logging\PlatformStructuredLogger;
use Illuminate\Console\Command;

final class MonitoringAlertsConsoleReporter
{
    public function __construct(
        private readonly PlatformStructuredLogger $logger,
    ) {}

    /**
     * @param list<MonitoringAlert> $alerts
     */
    public function report(Command $command, array $alerts): int
    {
        if ($command->option('json')) {
            $command->line(json_encode(array_map(fn ($a) => $a->toArray(), $alerts), JSON_THROW_ON_ERROR));

            return $alerts === [] ? Command::SUCCESS : Command::FAILURE;
        }

        if ($alerts === []) {
            $command->info('No monitoring alerts fired.');

            return Command::SUCCESS;
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
                $this->logger->error('Monitoring alert fired', $alert->toArray());
                $command->error($line);
            } else {
                $this->logger->warning('Monitoring alert fired', $alert->toArray());
                $command->warn($line);
            }
        }

        return $this->hasP1($alerts) ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @param list<MonitoringAlert> $alerts
     */
    private function hasP1(array $alerts): bool
    {
        foreach ($alerts as $alert) {
            if ($alert->severity === AlertSeverity::P1) {
                return true;
            }
        }

        return false;
    }
}
