<?php

declare(strict_types=1);

namespace Tests\Unit\Simulation;

use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Simulation\Application\Services\Execution\SimulationTenantEligibilityChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationTenantEligibilityCheckerTest extends TestCase
{
    use RefreshDatabase;

    private function checker(): SimulationTenantEligibilityChecker
    {
        config([
            'platform.control_plane' => true,
            'platform.local_fleet.auto_provision' => false,
        ]);

        return $this->app->make(SimulationTenantEligibilityChecker::class);
    }

    private function createTenant(array $settings = [], string $status = 'active'): TenantModel
    {
        return TenantModel::query()->create([
            'id'       => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'slug'     => 'tenant-test-sample',
            'name'     => 'Tenant Test Sample',
            'status'   => $status,
            'settings' => $settings,
        ]);
    }

    #[Test]
    public function it_blocks_tenant_without_explicit_modules_catalog(): void
    {
        $tenant = $this->createTenant(['plan' => 'starter']);

        $reason = $this->checker()->simulationBlockReason($tenant);

        $this->assertSame(
            'No hay catálogo de módulos configurado explícitamente para esta empresa.',
            $reason,
        );
    }

    #[Test]
    public function it_blocks_tenant_with_only_middleware_catalog(): void
    {
        $tenant = $this->createTenant([
            'modules_catalog' => [
                'middleware' => ['id' => 'middleware', 'name' => 'Bus', 'description' => '', 'role' => 'routing'],
                'producers' => [],
                'subscribers' => [],
            ],
        ]);

        $reason = $this->checker()->simulationBlockReason($tenant);

        $this->assertSame(
            'El catálogo no define productores; solo middleware no es suficiente para simular.',
            $reason,
        );
    }

    #[Test]
    public function it_blocks_tenant_with_producer_without_event_types(): void
    {
        $tenant = $this->createTenant([
            'modules_catalog' => [
                'middleware' => ['id' => 'middleware', 'name' => 'Bus', 'description' => '', 'role' => 'routing'],
                'producers' => [
                    ['id' => 'pos_a', 'name' => 'POS', 'event_types_emitted' => [], 'channels' => []],
                ],
                'subscribers' => [],
            ],
        ]);

        $reason = $this->checker()->simulationBlockReason($tenant);

        $this->assertSame(
            'Ningún productor define tipos de evento emitidos (event_types_emitted).',
            $reason,
        );
    }

    #[Test]
    public function it_allows_tenant_with_explicit_catalog_and_event_types(): void
    {
        $tenant = $this->createTenant([
            'modules_catalog' => [
                'middleware' => ['id' => 'middleware', 'name' => 'Bus', 'description' => '', 'role' => 'routing'],
                'producers' => [
                    ['id' => 'pos_a', 'name' => 'POS', 'event_types_emitted' => ['Order.Created'], 'channels' => []],
                ],
                'subscribers' => [],
            ],
        ]);

        $this->assertNull($this->checker()->simulationBlockReason($tenant));
        $this->assertTrue($this->checker()->canSimulateTenant($tenant));
    }

    #[Test]
    public function it_does_not_treat_default_fixture_as_simulation_source(): void
    {
        $tenant = $this->createTenant();

        $this->assertFalse($this->checker()->canSimulateTenant($tenant));
    }
}
