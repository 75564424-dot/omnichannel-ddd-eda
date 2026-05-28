<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TenantOperatorDeploymentGuardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function cannot_create_operator_on_registry_host_for_unbound_tenant_without_demo_flags(): void
    {
        config([
            'platform.client_slug' => 'acme-retail',
            'platform.control_plane' => false,
            'platform.multi_tenant_portal_login' => false,
            'platform_auth.web_auth_enabled' => true,
        ]);

        $tenant = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Pruebas Retail',
            'slug'   => 'pruebas-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        $saas = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->actingAs($saas)
            ->post("/control/companies/{$tenant->id}/operators", [
                'name'          => 'Op',
                'email'         => 'op@pruebas.local',
                'password'      => 'password123',
                'platform_role' => 'platform_admin',
            ])
            ->assertSessionHasErrors('operator');
    }
}
