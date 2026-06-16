<?php

declare(strict_types=1);

namespace Tests\Integration\Platform;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use App\Shared\Platform\LocalFleet\Contracts\LocalFleetProcessSupervisorInterface;
use App\Shared\Platform\LocalFleet\Contracts\LocalFleetTenantMirrorInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantLifecycleIntegrationFlowTest extends TestCase
{
    use RefreshDatabase;

    private function bindInstanceContext(string $tenantId): void
    {
        $context = $this->createMock(InstanceTenantContextInterface::class);
        $context->method('tenantId')->willReturn($tenantId);
        $this->app->instance(InstanceTenantContextInterface::class, $context);
    }

    public function test_integration_flow_start_suspend_restore_affects_portal_and_api(): void
    {
        config([
            'platform_auth.web_auth_enabled' => false,
            'platform.lifecycle_v15' => true,
        ]);

        $tenant = TenantModel::query()->create([
            'id' => 't-1',
            'slug' => 'acme-flow',
            'name' => 'Acme Flow',
            'status' => 'active',
            'settings' => [
                'deployment' => [
                    'lifecycle' => 'provisioned',
                    'local_instance' => [
                        'port' => 18001,
                        'env_id' => 'client-acme-flow',
                        'app_url' => 'http://127.0.0.1:18001',
                    ],
                ],
            ],
        ]);

        $this->bindInstanceContext($tenant->id);

        $this->mock(LocalFleetProcessSupervisorInterface::class, function ($mock): void {
            $mock->shouldReceive('ensureRunning')->andReturn(true);
            $mock->shouldReceive('isRunning')->andReturn(true);
            $mock->shouldReceive('stop')->andReturn(true);
        });

        $this->mock(LocalFleetTenantMirrorInterface::class, function ($mock): void {
            $mock->shouldReceive('mirror')->andReturnNull();
        });

        // Provision -> Start (control plane endpoint)
        config(['platform.control_plane' => true]);
        $this->post(route('control.companies.lifecycle.start', ['tenant' => $tenant->id]))
            ->assertRedirect();
        $tenant->refresh();
        $this->assertSame('running', (string) ($tenant->settings['deployment']['lifecycle'] ?? ''));

        $operator = User::query()->create([
            'name' => 'Instance Operator',
            'email' => 'operator@local',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
            'platform_role' => 'platform_admin',
        ]);

        // Silo: Health ready + portal OK
        config(['platform.control_plane' => false]);
        $this->get('/health/ready')->assertOk();
        $this->actingAs($operator)->get('/dashboard')->assertOk();

        // Suspend (control plane)
        config(['platform.control_plane' => true]);
        $this->post(route('control.companies.lifecycle.suspend', ['tenant' => $tenant->id]))
            ->assertRedirect();
        $tenant->refresh();
        $this->assertSame('suspended', $tenant->status);

        // Silo: Portal blocked + API blocked
        config(['platform.control_plane' => false]);
        $this->actingAs($operator)->get('/dashboard')->assertStatus(503)->assertSee('Tenant\\/Suspended', escape: false);
        $this->getJson('/api/middleware/status')->assertStatus(403)->assertJsonFragment(['type' => 'tenant_suspended']);

        // Restore (control plane)
        config(['platform.control_plane' => true]);
        $this->post(route('control.companies.lifecycle.restore', ['tenant' => $tenant->id]))
            ->assertRedirect();
        $tenant->refresh();
        $this->assertSame('active', $tenant->status);

        // Silo: Access restored
        config(['platform.control_plane' => false]);
        $this->actingAs($operator)->get('/dashboard')->assertOk();
        $this->getJson('/api/middleware/status')->assertOk();
    }
}
