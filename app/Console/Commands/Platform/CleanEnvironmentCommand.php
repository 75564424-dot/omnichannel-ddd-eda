<?php

declare(strict_types=1);

namespace App\Console\Commands\Platform;

use App\Shared\Platform\Services\LocalEnvironmentAuditService;
use App\Shared\Platform\Services\LocalEnvironmentResetService;
use Illuminate\Console\Command;

final class CleanEnvironmentCommand extends Command
{
    protected $signature = 'platform:clean-environment
                            {--verify : Solo auditar el entorno sin limpiar}
                            {--force : Omitir confirmación}';

    protected $description = 'Limpieza oficial completa del entorno local (tenants cliente, silos, fleet, simulaciones, runtime)';

    public function handle(
        LocalEnvironmentResetService $resetService,
        LocalEnvironmentAuditService $auditService,
    ): int {
        if (! config('platform.control_plane', false)) {
            $this->error('Ejecute en control plane: php artisan platform:clean-environment --env=control-plane ...');

            return self::FAILURE;
        }

        if ($this->option('verify')) {
            return $this->reportAudit($auditService);
        }

        if (! $this->option('force') && ! $this->confirm(
            '¿Limpiar por completo el entorno local (tenants cliente, SQLite, fleet, simulaciones)?',
            false,
        )) {
            $this->warn('Cancelado.');

            return self::FAILURE;
        }

        try {
            $lines = $resetService->reset(
                purgeTenants: true,
                keepSimulationHistory: false,
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Entorno local limpiado.');
        foreach ($lines as $line) {
            $this->line('  · '.$line);
        }

        $issues = $auditService->findSurvivors();
        if ($issues !== []) {
            $this->newLine();
            $this->error('La auditoría post-limpieza detectó referencias residuales:');
            foreach ($issues as $issue) {
                $this->line('  · '.$issue);
            }
            $this->newLine();
            $this->comment('Detenga npm run instances:serve antes de limpiar si hay SQLite bloqueadas.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Auditoría post-limpieza: entorno limpio.');
        $this->comment('Preservado: esquema, migraciones, control plane, operador SaaS.');
        $this->comment('Reinicie npm run instances:serve y provisione desde /control/provisioning.');

        return self::SUCCESS;
    }

    private function reportAudit(LocalEnvironmentAuditService $auditService): int
    {
        $issues = $auditService->findSurvivors();

        if ($issues === []) {
            $this->info('Entorno limpio: no se detectaron referencias residuales de tenants cliente.');

            return self::SUCCESS;
        }

        $this->warn('Referencias residuales detectadas:');
        foreach ($issues as $issue) {
            $this->line('  · '.$issue);
        }

        return self::FAILURE;
    }
}
