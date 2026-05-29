<?php

declare(strict_types=1);

namespace App\Console\Commands\Simulation;

use App\Shared\Platform\Services\ClientFixtureLoader;
use App\Shared\Platform\Services\ClientSimulationService;
use Illuminate\Console\Command;

final class SimulateClientCommand extends Command
{
    protected $signature = 'platform:simulate-client
                            {slug : Client fixture slug (e.g. retailco, acmepos)}
                            {--events=10 : Number of events to publish (or total when using --per-minute with --duration-minutes)}
                            {--per-minute= : Spread publishes at this rate (events/min); default interval 60/rate seconds}
                            {--duration-minutes=1 : With --per-minute, run for this many minutes (total = rate × minutes)}
                            {--apply-fixture : Copy fixture files into config/ before simulation}
                            {--skip-sync : Skip registry sync step}
                            {--skip-validate : Skip catalog validation}';

    protected $description = 'Runs an end-to-end simulated client: validate catalog, sync registry, publish sample events';

    public function handle(
        ClientSimulationService $simulation,
        ClientFixtureLoader $fixtures,
    ): int {
        $slug = strtolower(trim((string) $this->argument('slug')));

        if (! $fixtures->exists($slug)) {
            $this->error("Fixture [{$slug}] not found. Available: ".implode(', ', $fixtures->availableSlugs()));

            return self::FAILURE;
        }

        if ($this->option('apply-fixture')) {
            $fixtures->applyToFilesystem($slug);
            $this->warn('Fixture copied to config/. Run `php artisan config:clear` if config was cached.');
        }

        $events = max(0, (int) $this->option('events'));
        $perMinute = $this->option('per-minute');
        $perMinute = $perMinute !== null && $perMinute !== '' ? max(1, (int) $perMinute) : null;
        $durationMinutes = max(1, (int) $this->option('duration-minutes'));

        $plan = ClientSimulationService::resolvePublishPlan($events, $perMinute, $durationMinutes);
        if ($perMinute !== null) {
            $intervalSec = round(($plan['interval_microseconds'] ?? 0) / 1_000_000, 1);
            $this->info(sprintf(
                'Publishing %d event(s) at %d/min (~%ss between events)%s',
                $plan['total'],
                $perMinute,
                $intervalSec,
                $durationMinutes > 1 || $plan['total'] > $perMinute
                    ? sprintf(' for ~%d minute(s)', (int) ceil($plan['total'] / $perMinute))
                    : '',
            ));
        }

        try {
            $result = $simulation->simulate(
                slug: $slug,
                events: $events,
                applyFixture: false,
                skipValidate: (bool) $this->option('skip-validate'),
                skipSync: (bool) $this->option('skip-sync'),
                eventsPerMinute: $perMinute,
                durationMinutes: $perMinute !== null ? $durationMinutes : null,
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($result['validation_errors'] !== []) {
            $this->error('Catalog validation failed:');
            foreach ($result['validation_errors'] as $error) {
                $this->line("  - {$error}");
            }

            return self::FAILURE;
        }

        if ($result['sync'] !== null) {
            $this->info(sprintf(
                'Registry sync OK — producers: %d, consumers: %d',
                $result['sync']['producer_bindings'] ?? 0,
                $result['sync']['consumer_bindings'] ?? 0,
            ));
        }

        if ($result['published'] > 0) {
            $this->info("Published {$result['published']} events for client [{$slug}].");
            $this->line('Queue entries matched: '.$result['queue_matches'].' / '.$result['published']);

            if ($result['queue_matches'] < $result['published']) {
                $this->warn('Not all published events appear in queue snapshot — verify sync and subscriptions.');
            }
        } else {
            $this->info("Client [{$slug}] fixture applied — no events published (events=0).");
        }

        return self::SUCCESS;
    }
}
