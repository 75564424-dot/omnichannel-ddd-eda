<?php

declare(strict_types=1);

namespace App\Console\Application\Presenters;

use App\Console\Application\Services\Simulation\SimulateClientCommandOptions;
use Illuminate\Console\Command;

final class SimulateClientConsoleReporter
{
    public function reportFixtureNotFound(Command $command, string $slug, array $availableSlugs): int
    {
        $command->error("Fixture [{$slug}] not found. Available: ".implode(', ', $availableSlugs));

        return Command::FAILURE;
    }

    public function reportApplyFixtureWarning(Command $command): void
    {
        $command->warn('Fixture copied to config/. Run `php artisan config:clear` if config was cached.');
    }

    public function reportPublishPlan(Command $command, SimulateClientCommandOptions $options, array $plan): void
    {
        if ($options->perMinute === null) {
            return;
        }

        $intervalSec = round(($plan['interval_microseconds'] ?? 0) / 1_000_000, 1);
        $command->info(sprintf(
            'Publishing %d event(s) at %d/min (~%ss between events)%s',
            $plan['total'],
            $options->perMinute,
            $intervalSec,
            $options->durationMinutes > 1 || $plan['total'] > $options->perMinute
                ? sprintf(' for ~%d minute(s)', (int) ceil($plan['total'] / $options->perMinute))
                : '',
        ));
    }

    public function reportSimulationFailed(Command $command, string $message): int
    {
        $command->error($message);

        return Command::FAILURE;
    }

    /**
     * @param array<string, mixed> $result
     */
    public function reportSimulationResult(Command $command, string $slug, array $result): int
    {
        if ($result['validation_errors'] !== []) {
            $command->error('Catalog validation failed:');
            foreach ($result['validation_errors'] as $error) {
                $command->line("  - {$error}");
            }

            return Command::FAILURE;
        }

        if ($result['sync'] !== null) {
            $command->info(sprintf(
                'Registry sync OK — producers: %d, consumers: %d',
                $result['sync']['producer_bindings'] ?? 0,
                $result['sync']['consumer_bindings'] ?? 0,
            ));
        }

        if ($result['published'] > 0) {
            $command->info("Published {$result['published']} events for client [{$slug}].");
            $command->line('Queue entries matched: '.$result['queue_matches'].' / '.$result['published']);

            if ($result['queue_matches'] < $result['published']) {
                $command->warn('Not all published events appear in queue snapshot — verify sync and subscriptions.');
            }
        } else {
            $command->info("Client [{$slug}] fixture applied — no events published (events=0).");
        }

        return Command::SUCCESS;
    }
}
