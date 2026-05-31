<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Control\Domain\Policies\TenantLifecyclePolicy;
use App\Control\Application\UseCases\Lifecycle\StartTenantServiceUseCase;
use App\Control\Application\UseCases\Lifecycle\SuspendTenantServiceUseCase;
use App\Control\Application\UseCases\Lifecycle\RestoreTenantServiceUseCase;
use App\Http\Middleware\EnsureTenantOperationalStatus;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use App\Shared\Platform\LocalFleet\LocalFleetProcessSupervisor;
use App\Shared\Platform\LocalFleet\LocalFleetTenantMirror;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class TenantLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_lifecycle_policy_rules(): void
    {
        // 1. canStart
        $this->assertTrue(TenantLifecyclePolicy::canStart('active', 'provisioned'));
        $this->assertTrue(TenantLifecyclePolicy::canStart('active', 'stopped'));
        $this->assertFalse(TenantLifecyclePolicy::canStart('suspended', 'provisioned'));

        // 2. canSuspend
        $this->assertTrue(TenantLifecyclePolicy::canSuspend('active', 'running'));
        $this->assertFalse(TenantLifecyclePolicy::canSuspend('suspended', 'running'));

        // 3. canRestore
        $this->assertTrue(TenantLifecyclePolicy::canRestore('suspended', 'stopped'));
        $this->assertFalse(TenantLifecyclePolicy::canRestore('active', 'running'));
    }

    public function test_infer_lifecycle_default_cases(): void
    {
        $tenant = new TenantModel();
        $this->assertEquals('provisioned', TenantLifecyclePolicy::inferLifecycle($tenant));

        $tenant->settings = [
            'deployment' => [
                'lifecycle' => 'running',
            ],
        ];
        $this->assertEquals('running', TenantLifecyclePolicy::inferLifecycle($tenant));

        $tenant->settings = [
            'deployment' => [
                'status' => 'active_on_instance',
            ],
        ];
        $this->assertEquals('running', TenantLifecyclePolicy::inferLifecycle($tenant));
    }

    public function test_middleware_blocks_suspended_tenant_in_silo(): void
    {
        config(['platform.control_plane' => false]);

        // Mock the InstanceTenantContextInterface
        $context = $this->createMock(InstanceTenantContextInterface::class);
        $context->method('tenantId')->willReturn('1234-5678');

        // Create the suspended tenant in DB
        $tenant = TenantModel::query()->create([
            'id' => '1234-5678',
            'name' => 'Acme Test',
            'slug' => 'acme-test',
            'status' => 'suspended',
            'settings' => [
                'deployment' => [
                    'lifecycle' => 'running',
                ],
            ],
        ]);

        $middleware = new EnsureTenantOperationalStatus($context);

        // Prepare request
        $request = Request::create('/dashboard', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('Allowed');
        });

        $this->assertEquals(503, $response->getStatusCode());
        $this->assertStringContainsString('Tenant\/Suspended', $response->getContent());
        $this->assertStringContainsString('temporalmente suspendido', $response->getContent());
    }

    public function test_middleware_allows_active_tenant_in_silo(): void
    {
        config(['platform.control_plane' => false]);

        // Mock the InstanceTenantContextInterface
        $context = $this->createMock(InstanceTenantContextInterface::class);
        $context->method('tenantId')->willReturn('1234-5678');

        // Create the active tenant in DB
        $tenant = TenantModel::query()->create([
            'id' => '1234-5678',
            'name' => 'Acme Test',
            'slug' => 'acme-test',
            'status' => 'active',
            'settings' => [
                'deployment' => [
                    'lifecycle' => 'running',
                ],
            ],
        ]);

        $middleware = new EnsureTenantOperationalStatus($context);

        // Prepare request
        $request = Request::create('/dashboard', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('Allowed');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Allowed', $response->getContent());
    }
}
