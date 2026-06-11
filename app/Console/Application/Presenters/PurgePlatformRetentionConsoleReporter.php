<?php

declare(strict_types=1);

namespace App\Console\Application\Presenters;

use Illuminate\Console\Command;

final class PurgePlatformRetentionConsoleReporter
{
    public function reportInvalidTable(Command $command): int
    {
        $command->error('Invalid --table value.');

        return Command::FAILURE;
    }

    /**
     * @param array<string, array{days: int, deleted: int, cutoff: string|null, skipped?: bool}> $statsByTable
     */
    public function reportPurgeResults(Command $command, array $statsByTable, bool $dryRun): int
    {
        foreach ($statsByTable as $name => $stats) {
            if ($stats['skipped'] ?? false) {
                $command->line("  [skip] {$name} — table not present");

                continue;
            }

            $command->line(sprintf(
                '  %s: %s %d rows (retention %d days, cutoff %s)',
                $name,
                $dryRun ? 'would delete' : 'deleted',
                $stats['deleted'],
                $stats['days'],
                $stats['cutoff'] ?? 'n/a',
            ));
        }

        $command->info($dryRun ? 'Dry run complete.' : 'Retention purge complete.');

        return Command::SUCCESS;
    }
}
