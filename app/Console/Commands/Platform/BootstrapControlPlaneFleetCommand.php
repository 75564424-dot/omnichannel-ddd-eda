<?php

declare(strict_types=1);

namespace App\Console\Commands\Platform;

use App\Shared\Platform\Services\ControlPlaneFleetBootstrapService;
use Illuminate\Console\Command;

final class BootstrapControlPlaneFleetCommand extends Command
{
    protected $signature = 'platform:fleet:bootstrap-control-plane
                            {--legacy= : Path to legacy sqlite (default database/database.sqlite)}
                            {--provision : Also create/re-sync local client silos}';

    protected $description = 'Import acme/pruebas from legacy DB, create retail-norte/sur, optional local silos';

    public function handle(ControlPlaneFleetBootstrapService $bootstrap): int
    {
        $legacyPath = (string) ($this->option('legacy') ?: base_path('database/database.sqlite'));

        foreach ($bootstrap->importLegacyTenants($legacyPath) as $line) {
            str_starts_with($line, 'Legacy database not found')
                ? $this->warn($line)
                : (str_contains($line, 'not found') ? $this->warn($line) : $this->info($line));
        }

        if ($bootstrap->templateTenant() === null) {
            $this->error('Tenant pruebas-retail is required as template. Import legacy DB first or create via Provisioning.');

            return self::FAILURE;
        }

        if ($this->option('provision')) {
            foreach ($bootstrap->provisionAllClientSilos() as $line) {
                str_contains($line, 'skipping') ? $this->warn($line) : $this->line($line);
            }
        }

        $this->newLine();
        $this->info('Control plane fleet ready. Panel: http://127.0.0.1:8000/control/companies');

        return self::SUCCESS;
    }
}
