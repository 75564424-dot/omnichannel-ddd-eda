<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Platform;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetAdminCredentialsResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LocalFleetAdminCredentialsResolverTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function resolve_prefers_explicit_admin_payload(): void
    {
        $tenant = TenantModel::query()->create([
            'id'       => '11111111-1111-1111-1111-111111111111',
            'name'     => 'Acme',
            'slug'     => 'acme-retail',
            'status'   => 'active',
            'settings' => [],
        ]);

        $admin = (new LocalFleetAdminCredentialsResolver())->resolve($tenant, [
            'name'     => 'Ops Lead',
            'email'    => 'ops@acme.test',
            'password' => 'secret-123',
        ], 'fallback-password');

        $this->assertSame('ops@acme.test', $admin['email']);
        $this->assertSame('secret-123', $admin['password']);
    }

    #[Test]
    public function resolve_uses_platform_admin_operator_when_present(): void
    {
        $tenant = TenantModel::query()->create([
            'id'       => '22222222-2222-2222-2222-222222222222',
            'name'     => 'Beta',
            'slug'     => 'beta-retail',
            'status'   => 'active',
            'settings' => [],
        ]);

        User::factory()->create([
            'tenant_id'     => $tenant->id,
            'email'         => 'admin@beta.test',
            'name'          => 'Beta Admin',
            'platform_role' => 'platform_admin',
        ]);

        $admin = (new LocalFleetAdminCredentialsResolver())->resolve($tenant, null, 'fallback-password');

        $this->assertSame('admin@beta.test', $admin['email']);
        $this->assertSame('fallback-password', $admin['password']);
    }
}
