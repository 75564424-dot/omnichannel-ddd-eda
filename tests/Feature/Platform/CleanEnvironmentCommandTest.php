<?php

declare(strict_types=1);

namespace Tests\Feature\Platform;

use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CleanEnvironmentCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['platform.control_plane' => true]);
    }

    #[Test]
    public function clean_environment_runs_full_purge_and_passes_audit_with_isolated_registry(): void
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

        $historical = TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas',
            'status' => 'active',
            'settings' => [],
        ]);
        $historical->delete();

        $registryPath = storage_path('framework/testing/fleet-registry-'.uniqid('', true).'.json');
        File::put($registryPath, json_encode(['instances' => []], JSON_THROW_ON_ERROR));
        config(['platform.local_fleet.registry_path' => $registryPath]);
        $this->rebindFleetRegistry($registryPath);

        $this->artisan('platform:clean-environment', [
            '--force' => true,
            '--env' => 'testing',
        ])->assertSuccessful();

        $this->assertNull(TenantModel::withTrashed()->where('slug', 'pruebas')->first());
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

    #[Test]
    public function verify_reports_soft_deleted_tenant_as_survivor(): void
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

        $historical = TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas',
            'status' => 'active',
            'settings' => [],
        ]);
        $historical->delete();

        $this->artisan('platform:clean-environment', [
            '--verify' => true,
            '--env' => 'testing',
        ])->assertFailed();
    }

    private function rebindFleetRegistry(string $registryPath): void
    {
        $this->app->forgetInstance(\App\Shared\Platform\LocalFleet\LocalFleetRegistry::class);
        $this->app->singleton(
            \App\Shared\Platform\LocalFleet\LocalFleetRegistry::class,
            fn () => new \App\Shared\Platform\LocalFleet\LocalFleetRegistry($registryPath, 8001),
        );
    }
}
