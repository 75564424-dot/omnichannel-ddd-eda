<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;
use App\Shared\Platform\LocalFleet\LocalFleetTenantMirror;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * Imports legacy tenants into control plane and seeds Retail Norte / Sur from Pruebas Retail.
 */
final class BootstrapControlPlaneFleetCommand extends Command
{
    protected $signature = 'platform:fleet:bootstrap-control-plane
                            {--legacy= : Path to legacy sqlite (default database/database.sqlite)}
                            {--provision : Also create/re-sync local client silos}';

    protected $description = 'Import acme/pruebas from legacy DB, create retail-norte/sur, optional local silos';

    /** @var list<array{slug: string, name: string, admin_email: string, admin_name: string}> */
    private array $demoRetailCompanies = [];

    public function handle(
        LocalFleetInstanceProvisioner $provisioner,
        LocalFleetTenantMirror $mirror,
    ): int {
        $legacyPath = (string) ($this->option('legacy') ?: base_path('database/database.sqlite'));

        if (! is_file($legacyPath)) {
            $this->warn("Legacy database not found: {$legacyPath}");
        } else {
            $this->importLegacyTenant($legacyPath, 'acme-retail', 'admin@local');
            $this->importLegacyTenant($legacyPath, 'pruebas-retail', 'prueba@prueba');
        }

        $template = TenantModel::query()->where('slug', 'pruebas-retail')->first();
        if ($template === null) {
            $this->error('Tenant pruebas-retail is required as template. Import legacy DB first or create via Provisioning.');

            return self::FAILURE;
        }

        $templateOperator = User::query()
            ->where('tenant_id', $template->id)
            ->where('platform_role', 'platform_admin')
            ->orderBy('created_at')
            ->first();

        $passwordHash = $templateOperator !== null
            ? (string) DB::table('users')->where('id', $templateOperator->id)->value('password')
            : null;

        foreach ($this->demoRetailCompanies as $company) {
            $tenant = $this->upsertCloneTenant($template, $company['slug'], $company['name']);
            $this->upsertOperator(
                $tenant,
                $company['admin_email'],
                $company['admin_name'],
                'platform_admin',
                $passwordHash,
            );
            $this->info("Tenant «{$company['slug']}» listo en control plane.");
        }

        if ($this->option('provision')) {
            if (! $provisioner->isEnabled()) {
                $this->warn('PLATFORM_LOCAL_FLEET_AUTO_PROVISION=false — skipping silos.');

                return self::SUCCESS;
            }

            TenantModel::query()
                ->whereNotIn('slug', [(string) config('platform.local_fleet.control_plane_slug', 'platform')])
                ->orderBy('name')
                ->each(function (TenantModel $tenant) use ($provisioner, $mirror): void {
                    $this->line("Silo → {$tenant->slug}");
                    if ($provisioner->isProvisioned($tenant)) {
                        $mirror->mirror($tenant->fresh());
                        $this->line('  mirror OK');
                    } else {
                        $result = $provisioner->provision($tenant);
                        $this->line('  '.$result->message);
                    }
                });
        }

        $this->newLine();
        $this->info('Control plane fleet ready. Panel: http://127.0.0.1:8000/control/companies');

        return self::SUCCESS;
    }

    private function importLegacyTenant(string $legacyPath, string $slug, string $adminEmail): void
    {
        config(['database.connections.legacy_import' => [
            'driver'   => 'sqlite',
            'database' => $legacyPath,
            'prefix'   => '',
        ]]);

        $legacy = DB::connection('legacy_import');

        $row = $legacy->table('tenants')->where('slug', $slug)->first();
        if ($row === null) {
            $this->warn("Legacy tenant «{$slug}» not found.");

            return;
        }

        $settings = json_decode((string) ($row->settings ?? '{}'), true);
        if (! is_array($settings)) {
            $settings = [];
        }

        $existing = TenantModel::query()->where('slug', $slug)->first();

        $tenant = TenantModel::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'id'       => $existing?->id ?? Uuid::uuid4()->toString(),
                'name'     => (string) $row->name,
                'status'   => (string) ($row->status ?? 'active'),
                'settings' => $settings,
            ],
        );

        $legacyUser = $legacy->table('users')
            ->where('email', $adminEmail)
            ->where('tenant_id', $row->id)
            ->first();

        if ($legacyUser !== null) {
            $this->upsertOperator(
                $tenant,
                (string) $legacyUser->email,
                (string) $legacyUser->name,
                (string) $legacyUser->platform_role,
                (string) $legacyUser->password,
            );
        }

        $this->info("Imported «{$slug}» from legacy.");
        DB::purge('legacy_import');
    }

    private function upsertCloneTenant(TenantModel $template, string $slug, string $name): TenantModel
    {
        $settings = is_array($template->settings) ? $template->settings : [];
        $settings['plan'] = $settings['plan'] ?? 'growth';
        $settings['modules'] = $settings['modules'] ?? ['middleware'];
        $settings['primary_admin_email'] = null;

        if (is_array($settings['modules_catalog'] ?? null)) {
            $catalog = $settings['modules_catalog'];
            if (is_array($catalog['middleware'] ?? null)) {
                $catalog['middleware']['description'] = 'Catálogo para '.$name;
            }
            $catalog['service_contact_message'] = 'Catálogo de '.$name.' — gestionado desde control SaaS.';
            $settings['modules_catalog'] = $catalog;
        }

        $slugNormalized = Str::slug($slug);
        $existing = TenantModel::query()->where('slug', $slugNormalized)->first();

        return TenantModel::query()->updateOrCreate(
            ['slug' => $slugNormalized],
            [
                'id'       => $existing?->id ?? Uuid::uuid4()->toString(),
                'name'     => $name,
                'status'   => 'active',
                'settings' => $settings,
            ],
        );
    }

    private function upsertOperator(
        TenantModel $tenant,
        string $email,
        string $name,
        string $role,
        ?string $passwordHash,
    ): void {
        $payload = [
            'tenant_id'     => $tenant->id,
            'name'          => $name,
            'platform_role' => $role,
            'updated_at'    => now(),
        ];

        if ($passwordHash !== null && $passwordHash !== '') {
            $payload['password'] = $passwordHash;
        }

        $existing = DB::table('users')->where('email', $email)->first();

        if ($existing === null) {
            DB::table('users')->insert(array_merge($payload, [
                'email'      => $email,
                'created_at' => now(),
                'password'   => $passwordHash ?? bcrypt('client-local-dev'),
            ]));

            $settings = is_array($tenant->settings) ? $tenant->settings : [];
            $settings['primary_admin_email'] = $email;
            $tenant->update(['settings' => $settings]);

            return;
        }

        DB::table('users')->where('email', $email)->update(array_merge($payload, [
            'password' => $passwordHash ?? $existing->password,
        ]));
    }
}
