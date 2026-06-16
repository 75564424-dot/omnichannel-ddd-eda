<?php

declare(strict_types=1);

namespace App\Console\Commands\Simulation;

use App\Console\Application\Presenters\SimulateClientConsoleReporter;
use App\Console\Application\Services\Simulation\SimulateClientCommandOptions;
use App\Console\Application\Services\Simulation\SimulateClientOrchestrator;
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
        SimulateClientOrchestrator $orchestrator,
        SimulateClientConsoleReporter $reporter,
    ): int {
        $options = SimulateClientCommandOptions::fromCommand($this);

        $missingSlugs = $orchestrator->missingFixtureSlugs($options->slug);
        if ($missingSlugs !== null) {
            return $reporter->reportFixtureNotFound($this, $options->slug, $missingSlugs);
        }

        if ($options->applyFixture) {
            $orchestrator->applyFixtureToFilesystem($options->slug);
            $reporter->reportApplyFixtureWarning($this);
        }

        $reporter->reportPublishPlan($this, $options, $options->publishPlan());

        try {
            $result = $orchestrator->simulate($options);
        } catch (\Throwable $e) {
            return $reporter->reportSimulationFailed($this, $e->getMessage());
        }

        return $reporter->reportSimulationResult($this, $options->slug, $result);
    }
}
