<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Shared\Platform\Services\PlatformRetentionService;
use Illuminate\Console\Command;

final class PurgePlatformRetentionCommand extends Command
{
    protected $signature = 'platform:purge-retention
                            {--dry-run : Report rows that would be deleted without deleting}
                            {--table= : Purge only one table (message_queue, event_logs, observability_metrics, trace_logs, event_store, audit_logs)}';

    protected $description = 'Purges aged rows from message_queue, event_logs, observability_metrics, trace_logs, event_store, audit_logs';

    public function handle(PlatformRetentionService $retention): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $table  = $this->option('table');
        $table  = is_string($table) && $table !== '' ? $table : null;

        if ($table !== null && ! in_array($table, [
            'message_queue',
            'event_logs',
            'observability_metrics',
            'trace_logs',
            'event_store',
            'audit_logs',
        ], true)) {
            $this->error('Invalid --table value.');

            return self::FAILURE;
        }

        $results = $retention->purge($dryRun, $table);

        foreach ($results as $name => $stats) {
            if ($stats['skipped'] ?? false) {
                $this->line("  [skip] {$name} — table not present");

                continue;
            }

            $action = $dryRun ? 'would delete' : 'deleted';
            $this->line(sprintf(
                '  %s: %s %d rows (retention %d days, cutoff %s)',
                $name,
                $action,
                $stats['deleted'],
                $stats['days'],
                $stats['cutoff'] ?? 'n/a',
            ));
        }

        $this->info($dryRun ? 'Dry run complete.' : 'Retention purge complete.');

        return self::SUCCESS;
    }
}
