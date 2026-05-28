<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\InstanceDeploymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class InstanceDeploymentServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function tenant_not_bound_when_slug_differs_from_instance(): void
    {
        config([
            'platform.client_slug' => 'acme-retail',
            'platform.deployment_mode' => 'instance_per_client',
            'platform.control_plane' => false,
            'platform.multi_tenant_portal_login' => false,
        ]);

        $tenant = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        $service = $this->app->make(InstanceDeploymentService::class);

        $this->assertFalse($service->isTenantBoundToThisInstance($tenant));
        $this->assertFalse($service->portalLoginAllowedForTenant($tenant));
        $this->assertNotNull($service->operatorBlockReason($tenant));
    }

    #[Test]
    public function cross_tenant_portal_allowed_when_multi_tenant_flag_enabled(): void
    {
        config([
            'platform.client_slug' => 'acme-retail',
            'platform.multi_tenant_portal_login' => true,
            'platform.control_plane' => true,
        ]);

        $tenant = TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        $service = $this->app->make(InstanceDeploymentService::class);

        $this->assertTrue($service->portalLoginAllowedForTenant($tenant));
        $this->assertTrue($service->operatorsManageableOnThisHost($tenant));
    }
}
