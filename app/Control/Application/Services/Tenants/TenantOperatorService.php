<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Tenants;

use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\InstanceDeploymentService;
use Illuminate\Contracts\Hashing\Hasher;

final class TenantOperatorService
{
    public function __construct(
        private readonly InstanceDeploymentService $deployment,
        private readonly Hasher $hasher,
    ) {}

    public function operatorBlockReason(TenantModel $tenant): ?string
    {
        return $this->deployment->operatorBlockReason($tenant);
    }

    /** @return list<string> */
    public function instanceRoleValues(): array
    {
        return array_map(
            fn (PlatformRole $r) => $r->value,
            array_filter(PlatformRole::cases(), fn (PlatformRole $r) => $r->isInstanceOperator()),
        );
    }

    public function createOperator(
        TenantModel $tenant,
        string $name,
        string $email,
        string $password,
        string $platformRole,
    ): User {
        $user = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'email' => $email,
            'password' => $this->hasher->make($password),
            'platform_role' => $platformRole,
        ]);

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        if (($settings['primary_admin_email'] ?? null) === null) {
            $settings['primary_admin_email'] = $email;
            $tenant->update(['settings' => $settings]);
        }

        return $user;
    }

    public function updateOperatorRole(User $user, TenantModel $tenant, string $platformRole): void
    {
        $this->assertTenantOperator($tenant, $user);
        $user->update(['platform_role' => $platformRole]);
    }

    public function updateOperatorPassword(User $user, TenantModel $tenant, string $password): void
    {
        $this->assertTenantOperator($tenant, $user);
        $user->update(['password' => $this->hasher->make($password)]);
    }

    public function assertTenantOperator(TenantModel $tenant, User $user): void
    {
        if (! $user->belongsToTenant($tenant->id)) {
            abort(404);
        }

        $role = PlatformRole::tryFromString((string) $user->getAttribute('platform_role'));
        if ($role === null || ! $role->isInstanceOperator()) {
            abort(403);
        }
    }
}
