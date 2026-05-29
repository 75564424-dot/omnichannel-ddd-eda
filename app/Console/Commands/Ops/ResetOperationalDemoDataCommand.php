<?php

declare(strict_types=1);

namespace App\Console\Commands\Ops;

use App\Shared\Platform\Services\OperationalDataResetService;
use Illuminate\Console\Command;

final class ResetOperationalDemoDataCommand extends Command
{
    protected $signature = 'demo:reset-operational
                            {--force : Run without confirmation}
                            {--with-queues : Truncate jobs and failed_jobs}
                            {--with-cache : Truncate cache and cache_locks}
                            {--with-sessions : Truncate sessions}';

    protected $description = 'Empties platform observability tables (feed, bus queue, metrics, node snapshots)';

    public function handle(OperationalDataResetService $resetService): int
    {
        if (! $this->option('force') && ! $this->confirm('¿Vaciar tablas operativas de la plataforma (feed, bus, métricas)?', false)) {
            $this->warn('Cancelado.');

            return self::FAILURE;
        }

        $cleared = $resetService->reset(
            (bool) $this->option('with-queues'),
            (bool) $this->option('with-cache'),
            (bool) $this->option('with-sessions'),
        );

        $operationalCount = count(array_filter($cleared, fn ($t) => ! str_contains($t, '(colas)') && ! str_contains($t, 'cache') && $t !== 'sessions'));
        $this->info('Tablas operativas vaciadas: '.$operationalCount.'.');

        foreach ($cleared as $table) {
            $this->line("  · {$table}");
        }

        $this->newLine();
        $this->comment('Núcleo sin módulos de negocio: use migrate:fresh si necesita esquema desde cero.');

        return self::SUCCESS;
    }
}
