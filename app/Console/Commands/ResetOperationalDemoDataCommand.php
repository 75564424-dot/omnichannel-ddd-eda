<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Middleware\Application\Services\SimulationPulseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Clears operational / observability tables used by middleware and dashboard (event feed, bus, metrics).
 * Skips missing tables. Does not re-seed domain data (no business modules in core).
 */
final class ResetOperationalDemoDataCommand extends Command
{
    protected $signature = 'demo:reset-operational
                            {--force : Run without confirmation}
                            {--with-queues : Truncate jobs and failed_jobs}
                            {--with-cache : Truncate cache and cache_locks}
                            {--with-sessions : Truncate sessions}';

    protected $description = 'Empties platform observability tables (feed, bus queue, metrics, node snapshots)';

    /** @var list<string> */
    private array $operationalTables = [
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

    public function handle(SimulationPulseService $simulationPulse): int
    {
        if (! $this->option('force') && ! $this->confirm('¿Vaciar tablas operativas de la plataforma (feed, bus, métricas)?', false)) {
            $this->warn('Cancelado.');

            return self::FAILURE;
        }

        $cleared = 0;

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($this->operationalTables as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }
                DB::table($table)->delete();
                $this->line("  · {$table}");
                $cleared++;
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->info("Tablas operativas vaciadas: {$cleared}.");

        $simulationPulse->clear();
        $this->line('  · middleware.simulation_pulse (cache)');

        if ($this->option('with-queues')) {
            foreach (['jobs', 'failed_jobs'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                    $this->line("  · {$table} (colas)");
                }
            }
        }

        if ($this->option('with-cache')) {
            foreach (['cache_locks', 'cache'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                    $this->line("  · {$table}");
                }
            }
        }

        if ($this->option('with-sessions') && Schema::hasTable('sessions')) {
            DB::table('sessions')->delete();
            $this->line('  · sessions');
        }

        $this->newLine();
        $this->comment('Núcleo sin módulos de negocio: use migrate:fresh si necesita esquema desde cero.');

        return self::SUCCESS;
    }
}
