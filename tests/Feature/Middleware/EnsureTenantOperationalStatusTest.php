<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EnsureTenantOperationalStatusTest extends TestCase
{
    use RefreshDatabase;

    private function bindInstanceContext(string $tenantId): void
    {
        $context = $this->createMock(InstanceTenantContextInterface::class);
        $context->method('tenantId')->willReturn($tenantId);
        $this->app->instance(InstanceTenantContextInterface::class, $context);
    }

    public function test_feature_flag_can_disable_enforcement(): void
    {
        config([
            'platform.control_plane' => false,
            'platform.lifecycle_v15' => false,
            'platform_auth.web_auth_enabled' => true,
        ]);

        TenantModel::query()->create([
            'id' => 't-1',
            'slug' => 'acme-test',
            'name' => 'Acme Test',
            'status' => 'suspended',
            'settings' => [],
        ]);
        $this->bindInstanceContext('t-1');

        $this->get('/login')->assertOk();
    }

    public function test_skips_health_and_asset_paths_even_when_suspended(): void
    {
        config([
            'platform.control_plane' => false,
            'platform.lifecycle_v15' => true,
            'platform_auth.web_auth_enabled' => false,
        ]);

        TenantModel::query()->create([
            'id' => 't-1',
            'slug' => 'acme-test',
            'name' => 'Acme Test',
            'status' => 'suspended',
            'settings' => [],
        ]);
        $this->bindInstanceContext('t-1');

        $this->get('/up')->assertOk();
        $this->get('/health/ready')->assertOk();
    }

    public function test_blocks_web_portal_with_inertia_page_when_suspended(): void
    {
        config([
            'platform.control_plane' => false,
            'platform.lifecycle_v15' => true,
            'platform_auth.web_auth_enabled' => true,
        ]);

        TenantModel::query()->create([
            'id' => 't-1',
            'slug' => 'acme-test',
            'name' => 'Acme Test',
            'status' => 'suspended',
            'settings' => [
                'deployment' => ['lifecycle' => 'running'],
            ],
        ]);
        $this->bindInstanceContext('t-1');

        $this->get('/login')
            ->assertStatus(503)
            ->assertSee('Tenant\\/Suspended', escape: false)
            ->assertSee('temporalmente suspendido', escape: false);
    }

    public function test_blocks_api_with_problem_details_when_suspended(): void
    {
        config([
            'platform.control_plane' => false,
            'platform.lifecycle_v15' => true,
            'platform_auth.web_auth_enabled' => false,
        ]);

        TenantModel::query()->create([
            'id' => 't-1',
            'slug' => 'acme-test',
            'name' => 'Acme Test',
            'status' => 'suspended',
            'settings' => [
                'deployment' => ['lifecycle' => 'running'],
            ],
        ]);
        $this->bindInstanceContext('t-1');

        $this->getJson('/api/middleware/status')
            ->assertStatus(403)
            ->assertJsonFragment([
                'type' => 'tenant_suspended',
                'status' => 403,
            ]);
    }
}
