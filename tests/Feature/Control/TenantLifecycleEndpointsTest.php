<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\Contracts\LocalFleetProcessSupervisorInterface;
use App\Shared\Platform\LocalFleet\Contracts\LocalFleetTenantMirrorInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantLifecycleEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private function createTenant(string $id = 'tenant-1', string $slug = 'tenant-1'): TenantModel
    {
        return TenantModel::query()->create([
            'id' => $id,
            'slug' => $slug,
            'name' => 'Tenant Test',
            'status' => 'active',
            'settings' => [
                'deployment' => [
                    'lifecycle' => 'provisioned',
                    'local_instance' => [
                        'port' => 18001,
                        'env_id' => 'client-tenant-1',
                        'app_url' => 'http://127.0.0.1:18001',
                    ],
                ],
            ],
        ]);
    }

    private function createSaasAdmin(): User
    {
        return User::query()->create([
            'name' => 'SaaS Admin',
            'email' => 'saas@local',
            // Password irrelevant for actingAs()
            'password' => bcrypt('password'),
            'tenant_id' => null,
            'platform_role' => 'saas_admin',
        ]);
    }

    private function createNonSaasUser(): User
    {
        return User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@local',
            'password' => bcrypt('password'),
            'tenant_id' => null,
            'platform_role' => 'platform_admin',
        ]);
    }

    public function test_control_plane_requires_saas_admin_when_web_auth_enabled(): void
    {
        config([
            'platform.control_plane' => true,
            'platform_auth.web_auth_enabled' => true,
        ]);

        $tenant = $this->createTenant();

        $this->post(route('control.companies.lifecycle.start', ['tenant' => $tenant->id]))
            ->assertRedirect(route('login'));

        $this->actingAs($this->createNonSaasUser())
            ->post(route('control.companies.lifecycle.start', ['tenant' => $tenant->id]))
            ->assertRedirect(route('dashboard'));
    }

    public function test_lifecycle_start_suspend_restore_and_status_endpoints_work_for_saas_admin(): void
    {
        config([
            'platform.control_plane' => true,
            'platform_auth.web_auth_enabled' => true,
        ]);

        $tenant = $this->createTenant();

        $this->mock(LocalFleetProcessSupervisorInterface::class, function ($mock): void {
            $mock->shouldReceive('ensureRunning')->andReturn(true);
            $mock->shouldReceive('isRunning')->andReturn(true);
            $mock->shouldReceive('stop')->andReturn(true);
        });

        $this->mock(LocalFleetTenantMirrorInterface::class, function ($mock): void {
            $mock->shouldReceive('mirror')->andReturnNull();
        });

        $user = $this->createSaasAdmin();

        $this->actingAs($user)
            ->post(route('control.companies.lifecycle.start', ['tenant' => $tenant->id]))
            ->assertRedirect();

        $tenant->refresh();
        $this->assertSame('active', $tenant->status);
        $this->assertSame('running', (string) ($tenant->settings['deployment']['lifecycle'] ?? ''));

        $this->actingAs($user)
            ->get(route('control.companies.lifecycle.status', ['tenant' => $tenant->id]))
            ->assertOk()
            ->assertJsonFragment([
                'lifecycle' => 'running',
                'status' => 'active',
            ]);

        $this->actingAs($user)
            ->post(route('control.companies.lifecycle.suspend', ['tenant' => $tenant->id]))
            ->assertRedirect();

        $tenant->refresh();
        $this->assertSame('suspended', $tenant->status);

        $this->actingAs($user)
            ->post(route('control.companies.lifecycle.restore', ['tenant' => $tenant->id]))
            ->assertRedirect();

        $tenant->refresh();
        $this->assertSame('active', $tenant->status);

        $this->actingAs($user)
            ->get(route('control.companies.lifecycle.status', ['tenant' => $tenant->id]))
            ->assertOk()
            ->assertJsonFragment([
                'status' => 'active',
            ]);
    }
}
