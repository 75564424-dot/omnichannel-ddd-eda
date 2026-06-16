<?php

declare(strict_types=1);

namespace App\Console\Commands\Simulation;

use App\Simulation\Application\Services\Prepare\SimulationInstancePrepareService;
use Illuminate\Console\Command;

final class PrepareSimulationCommand extends Command
{
    protected $signature = 'platform:simulation:prepare
                            {--slug=acmepos : Fixture slug to validate against}
                            {--apply-fixture : Copy fixture into config/modules before sync}';

    protected $description = 'Prepare Acme instance: sync module registry and mark simulation ready (no events published)';

    public function handle(SimulationInstancePrepareService $prepareService): int
    {
        try {
            $result = $prepareService->prepare(
                (string) $this->option('slug'),
                (bool) $this->option('apply-fixture'),
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($this->option('apply-fixture')) {
            $this->warn('Fixture applied to config/. Run config:clear if cached.');
        }

        if ($result['catalog_applied']) {
            $this->info('modules_config.json updated from tenant catalog.');
        }
        $this->info(sprintf(
            'Registry sync — producers: %d, consumers: %d',
            $result['producer_bindings'],
            $result['consumer_bindings'],
        ));
        $this->info(sprintf(
            'Simulation prepared for [%s] (%d modules in catalog).',
            $result['instance_slug'],
            $result['module_count'],
        ));
        $this->line('When ready (10 evt/min, 1 min): php artisan platform:simulate-client '.$result['fixture_slug'].' --per-minute=10 --duration-minutes=1');
        $this->line('Burst smoke (10 at once): php artisan platform:simulate-client '.$result['fixture_slug'].' --events=10');

        return self::SUCCESS;
    }
}

