<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use App\Shared\Infrastructure\Models\TenantModel;

final class LocalFleetSyncService
{
    public function __construct(
        private readonly LocalFleetInstanceProvisioner $provisioner,
        private readonly LocalFleetTenantMirror $mirror,
    ) {}

    /**
     * @return array{provisioned: int, lines: list<string>}
     */
    public function sync(?string $slug, bool $force): array
    {
        $tenants = TenantModel::query()
            ->when(is_string($slug) && $slug !== '', fn ($q) => $q->where('slug', $slug))
            ->orderBy('name')
            ->get();

        if ($tenants->isEmpty()) {
            return ['provisioned' => 0, 'lines' => ['No tenants matched.']];
        }

        $lines = [];
        $provisioned = 0;

        foreach ($tenants as $tenant) {
            $lines = array_merge($lines, $this->syncTenant($tenant, $force, $provisioned));
        }

        return ['provisioned' => $provisioned, 'lines' => $lines];
    }

    public function isEnabled(): bool
    {
        return $this->provisioner->isEnabled();
    }

    /**
     * @param-out int $provisioned
     * @return list<string>
     */
    private function syncTenant(TenantModel $tenant, bool $force, int &$provisioned): array
    {
        if ($this->provisioner->isProvisioned($tenant) && ! $force) {
            return ["  skip {$tenant->slug} (already provisioned)"];
        }

        if ($this->provisioner->isProvisioned($tenant) && $force) {
            $this->mirror->mirror($tenant->fresh());

            return [
                "Re-sync {$tenant->name} ({$tenant->slug})…",
                '  → mirror OK',
            ];
        }

        $result = $this->provisioner->provision($tenant);
        if ($result->provisioned) {
            ++$provisioned;

            return [
                "Provisioning {$tenant->name} ({$tenant->slug})…",
                '  → '.$result->appUrl(),
            ];
        }

        return [
            "Provisioning {$tenant->name} ({$tenant->slug})…",
            '  → '.($result->message ?? 'skipped'),
        ];
    }
}
