<?php

declare(strict_types=1);

namespace Tests\Feature\Platform;

use App\Control\Application\Services\Tenants\ProvisionNewTenantService;
use App\Control\Application\UseCases\Lifecycle\StartTenantServiceUseCase;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\Contracts\LocalFleetProcessSupervisorInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ResetLocalEnvironmentCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['platform.control_plane' => true]);
    }

    #[Test]
    public function it_clears_simulation_history_and_handoffs_on_control_plane(): void
    {
        config(['platform.control_plane' => true]);

        $tenant = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Demo',
            'slug'   => 'demo-co',
            'status' => 'active',
            'settings' => ['last_simulation' => ['published' => 1]],
        ]);

        SimulationRunModel::query()->create([
            'id'                => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'tenant_id'         => $tenant->id,
            'status'            => SimulationRunModel::STATUS_COMPLETED,
            'fixture_slug'      => 'tenant-catalog',
            'events_per_minute' => 2,
            'duration_minutes'  => 1,
            'planned_total'     => 2,
            'prepare_first'     => true,
        ]);

        $this->artisan('platform:reset-local', ['--force' => true, '--env' => 'testing'])
            ->assertSuccessful();

        $this->assertSame(0, SimulationRunModel::query()->count());
        $tenant->refresh();
        $this->assertArrayNotHasKey('last_simulation', $tenant->settings ?? []);
    }

    #[Test]
    public function it_rejects_reset_when_not_on_control_plane_host(): void
    {
        config(['platform.control_plane' => false]);

        $this->artisan('platform:reset-local', ['--force' => true, '--env' => 'testing'])
            ->assertFailed();
    }

    #[Test]
    public function purge_tenants_a_removes_orphan_control_plane_rows_not_only_fleet_registry(): void
    {
        config([
            'platform.control_plane' => true,
            'platform.client_slug' => 'platform',
        ]);

        TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Platform',
            'slug'   => 'platform',
            'status' => 'active',
            'settings' => [],
        ]);

        TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Orphan',
            'slug'   => 'orphan-co',
            'status' => 'active',
            'settings' => [],
        ]);

        $registryPath = storage_path('framework/testing/fleet-registry-'.uniqid('', true).'.json');
        file_put_contents($registryPath, json_encode(['instances' => []], JSON_THROW_ON_ERROR));
        config(['platform.local_fleet.registry_path' => $registryPath]);
        $this->rebindFleetRegistry($registryPath);

        $this->artisan('platform:reset-local', [
            '--force' => true,
            '--purge-tenants' => true,
            '--env' => 'testing',
        ])->assertSuccessful();

        $this->assertSame(1, TenantModel::query()->count());
        $this->assertSame('platform', TenantModel::query()->value('slug'));
        $this->assertNull(TenantModel::withTrashed()->where('slug', 'orphan-co')->first());
    }

    #[Test]
    public function purge_tenants_z_recycles_soft_deleted_historical_slug_pruebas(): void
    {
        config([
            'platform.control_plane' => true,
            'platform.client_slug' => 'platform',
            'platform.local_fleet.auto_provision' => true,
        ]);

        TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Platform',
            'slug'   => 'platform',
            'status' => 'active',
            'settings' => [],
        ]);

        $historical = TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas',
            'status' => 'active',
            'settings' => [],
        ]);
        $historical->delete();

        $registryPath = storage_path('framework/testing/fleet-registry-'.uniqid('', true).'.json');
        file_put_contents($registryPath, json_encode(['instances' => []], JSON_THROW_ON_ERROR));
        config(['platform.local_fleet.registry_path' => $registryPath]);
        $this->rebindFleetRegistry($registryPath, enableAutoProvision: true);

        $this->mock(LocalFleetProcessSupervisorInterface::class, function ($mock): void {
            $mock->shouldReceive('ensureRunning')->once()->andReturn(true);
        });

        $this->artisan('platform:reset-local', [
            '--force' => true,
            '--purge-tenants' => true,
            '--env' => 'testing',
        ])->assertSuccessful();

        $this->assertNull(TenantModel::withTrashed()->where('slug', 'pruebas')->first());

        $result = $this->app->make(ProvisionNewTenantService::class)->provision([
            'company_name' => 'Pruebas Co',
            'slug' => 'pruebas',
            'plan' => 'starter',
            'modules' => ['tenant-catalog'],
            'admin_name' => 'Admin Pruebas',
            'admin_email' => 'admin@pruebas.local',
            'admin_password' => 'password123',
        ]);

        $tenant = $result['tenant']->fresh();
        $this->assertSame('pruebas', $tenant->slug);
        $this->assertIsArray($tenant->settings['deployment']['local_instance'] ?? null);

        $this->app->make(StartTenantServiceUseCase::class)->execute($tenant);
        $this->assertSame('running', $tenant->fresh()->settings['deployment']['lifecycle'] ?? null);
    }

    protected function tearDown(): void
    {
        foreach (glob(base_path('.env.client-pruebas')) ?: [] as $path) {
            @unlink($path);
        }
        foreach (glob(base_path('database/instances/pruebas.sqlite*')) ?: [] as $path) {
            @unlink($path);
        }
        $modulesDir = config_path('modules/instances/pruebas');
        if (is_dir($modulesDir)) {
            foreach (glob($modulesDir.'/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($modulesDir);
        }

        parent::tearDown();
    }

    private function rebindFleetRegistry(string $registryPath, bool $enableAutoProvision = false): void
    {
        if ($enableAutoProvision) {
            config(['platform.local_fleet.auto_provision' => true]);
        }

        $this->app->forgetInstance(\App\Shared\Platform\LocalFleet\LocalFleetRegistry::class);
        $this->app->forgetInstance(\App\Shared\Platform\LocalFleet\LocalFleetInstanceProvisioner::class);
        $this->app->singleton(
            \App\Shared\Platform\LocalFleet\LocalFleetRegistry::class,
            fn () => new \App\Shared\Platform\LocalFleet\LocalFleetRegistry($registryPath, 8001),
        );
    }
}
