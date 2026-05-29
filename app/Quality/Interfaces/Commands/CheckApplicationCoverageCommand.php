<?php

declare(strict_types=1);

namespace App\Quality\Interfaces\Commands;

use App\Quality\Application\Services\Coverage\ApplicationCoverageGateService;
use App\Quality\Application\Services\QualityCoverageConsoleReporter;
use Illuminate\Console\Command;

final class CheckApplicationCoverageCommand extends Command
{
    protected $signature = 'platform:quality-coverage
                            {--clover= : Path to clover.xml (defaults to platform_quality.coverage.clover_path)}
                            {--min= : Minimum coverage percent}
                            {--json : Output result as JSON}';

    protected $description = 'Verifies Application-layer coverage against platform quality thresholds (Plan_Calidad)';

    public function handle(
        ApplicationCoverageGateService $gate,
        QualityCoverageConsoleReporter $reporter,
    ): int {
        $clover = $this->option('clover');
        $min    = $this->option('min');

        try {
            $result = $gate->evaluate(
                is_string($clover) && $clover !== '' ? $clover : null,
                is_numeric($min) ? (float) $min : null,
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return $reporter->report($this, $result);
    }
}
