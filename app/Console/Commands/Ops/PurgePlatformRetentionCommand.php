<?php

declare(strict_types=1);

namespace App\Console\Commands\Ops;

use App\Console\Application\Presenters\PurgePlatformRetentionConsoleReporter;
use App\Console\Application\Services\Ops\PurgePlatformRetentionTableValidator;
use App\Shared\Platform\Services\PlatformRetentionService;
use Illuminate\Console\Command;

final class PurgePlatformRetentionCommand extends Command
{
    protected $signature = 'platform:purge-retention
                            {--dry-run : Report rows that would be deleted without deleting}
                            {--table= : Purge only one table (message_queue, event_logs, observability_metrics, trace_logs, event_store, audit_logs)}';

    protected $description = 'Purges aged rows from message_queue, event_logs, observability_metrics, trace_logs, event_store, audit_logs';

    public function handle(
        PlatformRetentionService $retention,
        PurgePlatformRetentionTableValidator $tableValidator,
        PurgePlatformRetentionConsoleReporter $reporter,
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $table = $tableValidator->normalize($this->option('table'));

        if (! $tableValidator->isValid($table)) {
            return $reporter->reportInvalidTable($this);
        }

        return $reporter->reportPurgeResults(
            $this,
            $retention->purge($dryRun, $table),
            $dryRun,
        );
    }
}
