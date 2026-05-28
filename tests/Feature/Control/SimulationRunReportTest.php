<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Control\Infrastructure\Jobs\RunTenantSimulationJob;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationRunReportTest extends TestCase
{
    use RefreshDatabase;

    private function seedAcmeTenant(): TenantModel
    {
        return TenantModel::query()->create([
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
    }

    private function saasUser(): User
    {
        return User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);
    }

    #[Test]
    public function simulation_start_creates_run_and_can_complete_with_report(): void
    {
        config([
            'platform.client_slug' => 'acme-retail',
            'platform_auth.web_auth_enabled' => true,
        ]);

        $tenant = $this->seedAcmeTenant();
        $saas   = $this->saasUser();

        $this->actingAs($saas)
            ->post('/control/companies/simulation', [
                'tenant_id'         => $tenant->id,
                'events_per_minute' => 2,
                'duration_minutes'  => 1,
                'total_events'      => 2,
                'prepare_first'     => true,
            ])
            ->assertRedirect(route('control.companies.index'))
            ->assertSessionHas('active_simulation_run_id');

        $runId = session('active_simulation_run_id');
        $this->assertNotEmpty($runId);

        RunTenantSimulationJob::dispatchSync($runId);

        $run = SimulationRunModel::query()->findOrFail($runId);
        $this->assertSame(SimulationRunModel::STATUS_COMPLETED, $run->status);
        $this->assertIsArray($run->metrics);
        $this->assertArrayHasKey('summary', $run->metrics);
        $this->assertArrayHasKey('throughput', $run->metrics);

        $this->actingAs($saas)
            ->get("/control/simulations/{$runId}/status")
            ->assertOk()
            ->assertJsonPath('run.status', SimulationRunModel::STATUS_COMPLETED)
            ->assertJsonStructure(['run' => ['progress_percent'], 'metrics' => ['summary']]);

        $this->actingAs($saas)
            ->get("/control/simulations/{$runId}/report")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Control/Simulation/Report')
                ->has('metrics.summary')
                ->has('metrics.throughput'));
    }

    #[Test]
    public function report_is_not_available_while_run_is_pending(): void
    {
        config(['platform_auth.web_auth_enabled' => true]);

        $tenant = $this->seedAcmeTenant();
        $saas   = $this->saasUser();

        $run = SimulationRunModel::query()->create([
            'id'                => '22222222-2222-2222-2222-222222222222',
            'tenant_id'         => $tenant->id,
            'status'            => SimulationRunModel::STATUS_PENDING,
            'fixture_slug'      => 'acmepos',
            'events_per_minute' => 10,
            'duration_minutes'  => 1,
            'planned_total'     => 10,
            'prepare_first'     => true,
        ]);

        $this->actingAs($saas)
            ->get("/control/simulations/{$run->id}/report")
            ->assertNotFound();
    }

    #[Test]
    public function saas_admin_can_list_simulation_history(): void
    {
        config(['platform_auth.web_auth_enabled' => true]);

        $tenant = $this->seedAcmeTenant();
        $saas   = $this->saasUser();

        SimulationRunModel::query()->create([
            'id'                => '33333333-3333-3333-3333-333333333333',
            'tenant_id'         => $tenant->id,
            'status'            => SimulationRunModel::STATUS_COMPLETED,
            'fixture_slug'      => 'acmepos',
            'events_per_minute' => 10,
            'duration_minutes'  => 1,
            'planned_total'     => 10,
            'published'         => 10,
            'prepare_first'     => true,
            'metrics'           => ['summary' => ['published' => 10]],
        ]);

        $this->actingAs($saas)
            ->get('/control/simulations')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Control/Simulation/Index')
                ->has('runs.data', 1)
                ->where('runs.data.0.can_view_report', true));

        $this->actingAs($saas)
            ->get('/control/simulations?tenant_id='.$tenant->id)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('filters.tenant_id', $tenant->id));
    }
}
