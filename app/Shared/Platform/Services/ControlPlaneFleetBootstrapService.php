<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner;
use App\Shared\Platform\LocalFleet\LocalFleetTenantMirror;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class ControlPlaneFleetBootstrapService
{
    public function __construct(
        private readonly LocalFleetInstanceProvisioner $provisioner,
        private readonly LocalFleetTenantMirror $mirror,
    ) {}

    /**
     * @return list<string> Human-readable status lines
     */
    public function importLegacyTenants(string $legacyPath): array
    {
        $lines = [];

        if (! is_file($legacyPath)) {
            return ["Legacy database not found: {$legacyPath}"];
        }

        foreach ([['acme-retail', 'admin@local'], ['pruebas-retail', 'prueba@prueba']] as [$slug, $email]) {
            $imported = $this->importLegacyTenant($legacyPath, $slug, $email);
            if ($imported !== null) {
                $lines[] = $imported;
            }
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    public function provisionAllClientSilos(): array
    {
        if (! $this->provisioner->isEnabled()) {
            return ['PLATFORM_LOCAL_FLEET_AUTO_PROVISION=false — skipping silos.'];
        }

        $lines = [];
        TenantModel::query()
            ->whereNotIn('slug', [(string) config('platform.local_fleet.control_plane_slug', 'platform')])
            ->orderBy('name')
            ->each(function (TenantModel $tenant) use (&$lines): void {
                $lines[] = "Silo → {$tenant->slug}";
                if ($this->provisioner->isProvisioned($tenant)) {
                    $this->mirror->mirror($tenant->fresh());
                    $lines[] = '  mirror OK';
                } else {
                    $result = $this->provisioner->provision($tenant);
                    $lines[] = '  '.$result->message;
                }
            });

        return $lines;
    }

    public function templateTenant(): ?TenantModel
    {
        return TenantModel::query()->where('slug', 'pruebas-retail')->first();
    }

    private function importLegacyTenant(string $legacyPath, string $slug, string $adminEmail): ?string
    {
        config(['database.connections.legacy_import' => [
            'driver' => 'sqlite',
            'database' => $legacyPath,
            'prefix' => '',
        ]]);

        $legacy = DB::connection('legacy_import');
        $row = $legacy->table('tenants')->where('slug', $slug)->first();
        if ($row === null) {
            DB::purge('legacy_import');

            return "Legacy tenant «{$slug}» not found.";
        }

        $settings = json_decode((string) ($row->settings ?? '{}'), true);
        if (! is_array($settings)) {
            $settings = [];
        }

        $existing = TenantModel::query()->withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $tenant = TenantModel::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'id' => $existing?->id ?? Uuid::uuid4()->toString(),
                'name' => (string) $row->name,
                'status' => (string) ($row->status ?? 'active'),
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

        DB::purge('legacy_import');

        return "Imported «{$slug}» from legacy.";
    }

    private function upsertOperator(
        TenantModel $tenant,
        string $email,
        string $name,
        string $role,
        ?string $passwordHash,
    ): void {
        $payload = [
            'tenant_id' => $tenant->id,
            'name' => $name,
            'platform_role' => $role,
            'updated_at' => now(),
        ];

        if ($passwordHash !== null && $passwordHash !== '') {
            $payload['password'] = $passwordHash;
        }

        $existing = DB::table('users')->where('email', $email)->first();

        if ($existing === null) {
            DB::table('users')->insert(array_merge($payload, [
                'email' => $email,
                'created_at' => now(),
                'password' => $passwordHash ?? bcrypt('client-local-dev'),
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
