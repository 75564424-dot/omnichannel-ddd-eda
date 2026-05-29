<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use App\Simulation\Application\Services\Runtime\SimulationPulseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class OperationalDataResetService
{
    /** @var list<string> */
    private const OPERATIONAL_TABLES = [
        'event_feed_projections',
        'observability_metrics',
        'channel_status_snapshots',
        'dead_letter_queue',
        'message_queue',
        'outbox_messages',
        'event_logs',
        'event_store',
        'registered_modules',
        'retries',
        'audit_logs',
        'trace_logs',
        'webhook_responses',
        'webhook_requests',
        'notifications',
        'transactions',
        'workflow_steps',
        'workflows',
        'processing_jobs',
    ];

    public function __construct(
        private readonly SimulationPulseService $simulationPulse,
    ) {}

    /**
     * @return list<string> Cleared table labels for CLI output
     */
    public function reset(bool $withQueues, bool $withCache, bool $withSessions): array
    {
        $cleared = [];

        Schema::disableForeignKeyConstraints();

        try {
            foreach (self::OPERATIONAL_TABLES as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }
                DB::table($table)->delete();
                $cleared[] = $table;
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->simulationPulse->clear();
        $cleared[] = 'middleware.simulation_pulse (cache)';

        if ($withQueues) {
            foreach (['jobs', 'failed_jobs'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                    $cleared[] = "{$table} (colas)";
                }
            }
        }

        if ($withCache) {
            foreach (['cache_locks', 'cache'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                    $cleared[] = $table;
                }
            }
        }

        if ($withSessions && Schema::hasTable('sessions')) {
            DB::table('sessions')->delete();
            $cleared[] = 'sessions';
        }

        return $cleared;
    }
}
