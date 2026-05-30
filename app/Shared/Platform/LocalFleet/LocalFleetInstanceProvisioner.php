<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\InstanceDeploymentService;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

final class LocalFleetInstanceProvisioner
{
    public function __construct(
        private readonly LocalFleetRegistry $registry,
        private readonly LocalFleetEnvBuilder $envBuilder,
        private readonly LocalFleetTenantMirror $tenantMirror,
        private readonly InstanceDeploymentService $deployment,
        private readonly bool $enabled,
        private readonly string $defaultAdminPassword,
        private readonly string $controlPlaneSlug,
    ) {}

    public function isEnabled(): bool
    {
        return $this->enabled && $this->deployment->isControlPlaneRegistry();
    }

    public function isProvisioned(TenantModel $tenant): bool
    {
        return $this->registry->isProvisioned($tenant->slug);
    }

    /**
     * @param array{name?: string, email?: string, password?: string}|null $admin
     */
    public function provision(TenantModel $tenant, ?array $admin = null): LocalFleetProvisionResult
    {
        if (! $this->isEnabled()) {
            return new LocalFleetProvisionResult(
                provisioned: false,
                instance: [],
                localInstance: [],
                message: 'Local fleet auto-provision is disabled or this host is not the control plane.',
            );
        }

        if ($this->shouldSkipTenant($tenant)) {
            return new LocalFleetProvisionResult(
                provisioned: false,
                instance: [],
                localInstance: [],
                message: 'Tenant skipped for local fleet (control-plane slug).',
            );
        }

        $admin = $this->resolveAdmin($tenant, $admin);
        $existing = $this->registry->findBySlug($tenant->slug);

        $instanceRow = $this->registry->upsert([
            'id'            => $existing['id'] ?? $this->envBuilder->instanceEnvId($tenant->slug),
            'label'         => $tenant->name,
            'slug'          => $tenant->slug,
            'port'          => $existing['port'] ?? null,
            'tenantId'      => $tenant->id,
            'adminEmail'    => $admin['email'],
            'adminPassword' => $admin['password'],
            'adminName'     => $admin['name'],
            'provisionedAt' => now()->toIso8601String(),
        ]);

        $envId = (string) $instanceRow['id'];
        $appKey = $this->resolveAppKey($envId);
        $this->envBuilder->ensureSqliteFile($tenant->slug);
        $envPath = base_path($this->envBuilder->envFileName($tenant->slug));
        file_put_contents($envPath, $this->envBuilder->build($instanceRow, $appKey));

        $this->runInstanceBootstrap($envId);
        $this->tenantMirror->mirror($tenant->fresh());

        $primaryEmail = $this->primaryOperatorEmail($tenant);

        $localInstance = [
            'app_url'  => 'http://127.0.0.1:'.(int) $instanceRow['port'],
            'port'     => (int) $instanceRow['port'],
            'env_file' => basename($envPath),
            'env_id'   => $envId,
            'db_path'  => 'database/instances/'.Str::slug($tenant->slug).'.sqlite',
        ];

        $this->markTenantProvisioned($tenant, $localInstance, $primaryEmail);

        return new LocalFleetProvisionResult(
            provisioned: true,
            instance: $instanceRow,
            localInstance: $localInstance,
            message: 'Instancia local aislada creada en '.$localInstance['app_url'],
        );
    }

    /** @return list<LocalFleetProvisionResult> */
    public function syncAllTenants(): array
    {
        $results = [];

        TenantModel::query()
            ->orderBy('name')
            ->get()
            ->each(function (TenantModel $tenant) use (&$results): void {
                if ($this->shouldSkipTenant($tenant)) {
                    return;
                }

                if ($this->isProvisioned($tenant)) {
                    return;
                }

                $results[] = $this->provision($tenant);
            });

        return $results;
    }

    public function allClientTenantsProvisioned(): bool
    {
        $pending = TenantModel::query()
            ->get()
            ->filter(fn (TenantModel $tenant): bool => ! $this->shouldSkipTenant($tenant))
            ->filter(fn (TenantModel $tenant): bool => ! $this->isProvisioned($tenant));

        return $pending->isEmpty();
    }

    private function shouldSkipTenant(TenantModel $tenant): bool
    {
        return Str::slug($tenant->slug) === Str::slug($this->controlPlaneSlug);
    }

    /**
     * @param array{name?: string, email?: string, password?: string}|null $admin
     * @return array{name: string, email: string, password: string}
     */
    private function resolveAdmin(TenantModel $tenant, ?array $admin): array
    {
        if ($admin !== null && ($admin['email'] ?? '') !== '') {
            return [
                'name'     => (string) ($admin['name'] ?? 'Admin '.$tenant->name),
                'email'    => (string) $admin['email'],
                'password' => (string) ($admin['password'] ?? $this->defaultAdminPassword),
            ];
        }

        $operator = User::query()
            ->where('tenant_id', $tenant->id)
            ->where('platform_role', 'platform_admin')
            ->orderBy('created_at')
            ->first();

        if ($operator !== null) {
            return [
                'name'     => (string) $operator->getAttribute('name'),
                'email'    => (string) $operator->getAttribute('email'),
                'password' => $this->defaultAdminPassword,
            ];
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $email = (string) ($settings['primary_admin_email'] ?? 'admin@'.Str::slug($tenant->slug).'-local');

        return [
            'name'     => 'Admin '.$tenant->name,
            'email'    => $email,
            'password' => $this->defaultAdminPassword,
        ];
    }

    private function resolveAppKey(string $envId): string
    {
        $envPath = base_path('.env.'.$envId);
        if (is_file($envPath)) {
            $contents = (string) file_get_contents($envPath);
            if (preg_match('/^APP_KEY=(.+)$/m', $contents, $matches) === 1) {
                $key = trim($matches[1]);
                if ($key !== '') {
                    return $key;
                }
            }
        }

        return 'base64:'.base64_encode(random_bytes(32));
    }

    private function runInstanceBootstrap(string $envId): void
    {
        $this->runArtisanProcess($envId, 'migrate', ['--force' => true]);
        $this->runArtisanProcess($envId, 'platform:instance:bootstrap', ['--skip-admin' => true]);
        $this->runArtisanProcess($envId, 'db:seed', [
            '--class' => 'Database\\Seeders\\MiddlewareDatabaseSeeder',
            '--force' => true,
        ]);
    }

    private function primaryOperatorEmail(TenantModel $tenant): string
    {
        $email = User::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('platform_role', ['platform_admin', 'bus_operator', 'dashboard_viewer'])
            ->orderBy('created_at')
            ->value('email');

        if (is_string($email) && $email !== '') {
            return $email;
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];

        return (string) ($settings['primary_admin_email'] ?? 'admin@'.Str::slug($tenant->slug).'-local');
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function runArtisanProcess(string $envId, string $command, array $arguments = []): void
    {
        $params = ['php', 'artisan', $command];

        foreach ($arguments as $key => $value) {
            if (is_int($key)) {
                $params[] = (string) $value;
            } elseif ($value === true) {
                $params[] = '--'.$key;
            } elseif ($value !== false && $value !== null) {
                $params[] = '--'.$key.'='.$value;
            }
        }

        $params[] = '--env='.$envId;

        $process = new Process($params, base_path());
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()));
        }
    }

    /**
     * @param array<string, mixed> $localInstance
     */
    private function markTenantProvisioned(TenantModel $tenant, array $localInstance, string $adminEmail): void
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['primary_admin_email'] = $adminEmail;
        $settings['app_url'] = $localInstance['app_url'];
        $settings['deployment'] = [
            'mode'                 => 'instance_per_client',
            'status'               => 'active_on_instance',
            'lifecycle'            => 'provisioned',
            'required_client_slug' => $tenant->slug,
            'local_instance'       => $localInstance,
            'provisioned_at'       => now()->toIso8601String(),
        ];

        $tenant->update(['settings' => $settings]);
    }
}
