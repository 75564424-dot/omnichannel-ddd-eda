<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Control\Infrastructure\Jobs\RunTenantSimulationJob;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CompanySimulationAutomationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function saas_admin_can_run_simulation_from_companies_index(): void
    {
        config([
            'platform.control_plane' => true,
            'platform.client_slug' => 'acme-retail',
            'platform.local_fleet.auto_provision' => false,
            'platform_auth.web_auth_enabled' => true,
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $tenant = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Acme Retail Middleware',
            'slug'   => 'acme-retail',
            'status' => 'active',
            'settings' => [
                'modules_catalog' => [
                    'middleware' => ['id' => 'middleware', 'name' => 'Bus', 'description' => '', 'role' => 'routing'],
                    'producers' => [
                        ['id' => 'acme_pos', 'name' => 'POS', 'event_types_emitted' => ['AcmePOS.Sale.Completed'], 'channels' => []],
                    ],
                    'subscribers' => [
                        ['id' => 'acme_reporting', 'name' => 'Reporting', 'event_types_consumed' => ['AcmePOS.Sale.Completed']],
                    ],
                ],
            ],
        ]);

        $saas = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->actingAs($saas)
            ->post('/control/companies/simulation', [
                'tenant_id'         => $tenant->id,
                'events_per_minute' => 2,
                'duration_minutes'  => 1,
                'total_events'      => 2,
                'prepare_first'     => true,
            ])
            ->assertRedirect(route('control.simulations.index', ['run' => session('active_simulation_run_id')]))
            ->assertSessionHas('message')
            ->assertSessionHas('active_simulation_run_id');

        $runId = session('active_simulation_run_id');
        RunTenantSimulationJob::dispatchSync($runId);

        $run = SimulationRunModel::query()->findOrFail($runId);
        $this->assertSame(SimulationRunModel::STATUS_COMPLETED, $run->status);

        $tenant->refresh();
        $last = $tenant->settings['last_simulation'] ?? null;
        $this->assertIsArray($last);
        $this->assertSame(2, $last['published'] ?? 0);
        $this->assertTrue($last['has_report'] ?? false);
    }

    #[Test]
    public function control_plane_marks_tenants_without_explicit_catalog_as_not_simulatable(): void
    {
        config([
            'platform.client_slug'     => 'platform',
            'platform.control_plane'   => true,
            'platform_auth.web_auth_enabled' => true,
        ]);

        TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Acme',
            'slug'   => 'acme-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->actingAs(User::query()->where('email', 'saas@local')->first())
            ->get('/control/companies')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Control/Companies/Index')
                ->where('tenants.0.can_simulate', false)
                ->where('tenants.1.can_simulate', false)
                ->where('tenants.0.simulate_block_reason', 'No hay catálogo de módulos configurado explícitamente para esta empresa.'));
    }

    #[Test]
    public function companies_index_includes_simulation_panel_props(): void
    {
        config([
            'platform.control_plane' => true,
            'platform.client_slug' => 'acme-retail',
            'platform_auth.web_auth_enabled' => true,
        ]);

        TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Acme',
            'slug'   => 'acme-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->actingAs(User::query()->where('email', 'saas@local')->first())
            ->get('/control/companies')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Control/Companies/Index')
                ->has('simulation_defaults')
                ->has('tenants', 1)
                ->where('tenants.0.can_simulate', false));
    }

    #[Test]
    public function simulation_post_is_rejected_when_tenant_has_no_explicit_catalog(): void
    {
        config([
            'platform.control_plane' => true,
            'platform.client_slug' => 'platform',
            'platform_auth.web_auth_enabled' => true,
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $tenant = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Tenant Test Branding',
            'slug'   => 'tenant-test-branding',
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
            ->post('/control/companies/simulation', [
                'tenant_id'         => $tenant->id,
                'events_per_minute' => 2,
                'duration_minutes'  => 1,
                'prepare_first'     => true,
            ])
            ->assertSessionHasErrors('simulation');

        $this->assertSame(0, SimulationRunModel::query()->count());
    }
}
