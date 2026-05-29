<?php

declare(strict_types=1);

namespace App\Console\Commands\Ops;

use App\Shared\Platform\Services\DemoIdentityResetService;
use Illuminate\Console\Command;

final class ResetDemoIdentityCommand extends Command
{
    protected $signature = 'platform:reset-demo-identity';

    protected $description = 'Remove extra tenants/users; keep acme-retail, admin@local and saas@local';

    public function handle(DemoIdentityResetService $resetService): int
    {
        try {
            $result = $resetService->reset();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($result['deleted_reports'] > 0) {
            $this->line("Reportes de incidentes eliminados (otros tenants): {$result['deleted_reports']}");
        }
        $this->line("Usuarios eliminados: {$result['deleted_users']}");
        $this->line("Empresas (tenants) eliminadas: {$result['deleted_tenants']}");

        $this->newLine();
        $this->info("Listo. Empresa: {$result['tenant_name']} ({$result['tenant_slug']})");
        $this->table(['Email', 'Rol', 'Empresa'], $result['operators']);
        $this->comment('Contraseñas según PLATFORM_ADMIN_PASSWORD y PLATFORM_SAAS_ADMIN_PASSWORD en .env');

        return self::SUCCESS;
    }
}
