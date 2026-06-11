<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Support\Str;

final class LocalFleetAdminCredentialsResolver
{
    /**
     * @param array{name?: string, email?: string, password?: string}|null $admin
     *
     * @return array{name: string, email: string, password: string}
     */
    public function resolve(TenantModel $tenant, ?array $admin, string $defaultPassword): array
    {
        if ($admin !== null && ($admin['email'] ?? '') !== '') {
            return [
                'name'     => (string) ($admin['name'] ?? 'Admin '.$tenant->name),
                'email'    => (string) $admin['email'],
                'password' => (string) ($admin['password'] ?? $defaultPassword),
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
                'password' => $defaultPassword,
            ];
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $email = (string) ($settings['primary_admin_email'] ?? 'admin@'.Str::slug($tenant->slug).'-local');

        return [
            'name'     => 'Admin '.$tenant->name,
            'email'    => $email,
            'password' => $defaultPassword,
        ];
    }

    public function primaryOperatorEmail(TenantModel $tenant): string
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
}
