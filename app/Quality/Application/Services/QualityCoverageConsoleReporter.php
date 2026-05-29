<?php

declare(strict_types=1);

namespace App\Quality\Application\Services;

use App\Quality\Domain\ValueObjects\CoverageGateResult;
use Illuminate\Console\Command;

final class QualityCoverageConsoleReporter
{
    public function report(Command $command, CoverageGateResult $result): int
    {
        if ($command->option('json')) {
            $command->line(json_encode($result->toArray(), JSON_THROW_ON_ERROR));

            return $result->passed ? Command::SUCCESS : Command::FAILURE;
        }

        $command->line(sprintf(
            'Application layer coverage: %.2f%% (%d/%d statements)',
            $result->percent,
            $result->coveredStatements,
            $result->totalStatements,
        ));

        if ($result->passed) {
            $command->info(sprintf('Coverage gate passed (>= %.0f%%).', $result->minPercent));

            return Command::SUCCESS;
        }

        $command->error(sprintf(
            'Coverage gate failed: %.2f%% < %.0f%%',
            $result->percent,
            $result->minPercent,
        ));

        return Command::FAILURE;
    }
}
