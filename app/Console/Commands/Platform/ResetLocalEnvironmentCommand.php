<?php

declare(strict_types=1);

namespace App\Console\Commands\Platform;

use App\Shared\Platform\Services\LocalEnvironmentResetService;
use Illuminate\Console\Command;

final class ResetLocalEnvironmentCommand extends Command
{
    protected $signature = 'platform:reset-local
                            {--purge-tenants : Remove all client fleet tenants, silos, .env files and clear fleet-registry.json}
                            {--keep-simulation-history : Fail stale runs only; keep simulation_runs rows}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Reset local fleet runtime data (simulations, handoffs, operational tables) for reproducible audits';

    public function handle(LocalEnvironmentResetService $resetService): int
    {
        if (! config('platform.control_plane', false)) {
            $this->error('Ejecute en control plane: php artisan platform:reset-local --env=control-plane ...');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm(
            '¿Resetear datos operativos locales (simulaciones, colas, métricas, handoffs)?',
            false,
        )) {
            $this->warn('Cancelado.');

            return self::FAILURE;
        }

        if ($this->option('purge-tenants') && ! $this->option('force')
            && ! $this->confirm('¿Eliminar también TODOS los tenants cliente del fleet (silos, .env, registry)?', false)) {
            $this->warn('Cancelado.');

            return self::FAILURE;
        }

        try {
            $lines = $resetService->reset(
                purgeTenants: (bool) $this->option('purge-tenants'),
                keepSimulationHistory: (bool) $this->option('keep-simulation-history'),
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Entorno local reseteado.');
        foreach ($lines as $line) {
            $this->line('  · '.$line);
        }

        $this->newLine();
        $this->comment('Preservado: esquema, migraciones, configuración base, operador SaaS.');
        if ($this->option('purge-tenants')) {
            $this->comment('Provisione tenants nuevos desde /control/provisioning antes de simular.');
        } else {
            $this->comment('Reinicie npm run instances:serve si el fleet estaba activo.');
        }

        return self::SUCCESS;
    }
}
