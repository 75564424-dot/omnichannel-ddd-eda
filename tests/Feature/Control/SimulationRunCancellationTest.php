<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationRunCancellationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function saas_admin_can_cancel_running_simulation(): void
    {
        config([
            'platform.control_plane' => true,
            'platform_auth.web_auth_enabled' => true,
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $tenant = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas',
            'status' => 'active',
            'settings' => [],
        ]);

        $saas = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $run = SimulationRunModel::query()->create([
            'id'                => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'tenant_id'         => $tenant->id,
            'status'            => SimulationRunModel::STATUS_RUNNING,
            'fixture_slug'      => 'tenant-catalog',
            'events_per_minute' => 10,
            'duration_minutes'  => 1,
            'planned_total'     => 10,
            'prepare_first'     => true,
            'progress_current'  => 2,
            'published'         => 2,
            'started_at'        => now(),
        ]);

        $this->actingAs($saas)
            ->postJson('/control/simulations/'.$run->id.'/cancel')
            ->assertOk()
            ->assertJsonPath('run.status', SimulationRunModel::STATUS_CANCELLED)
            ->assertJsonPath('run.published', 2);

        $run->refresh();
        $this->assertSame(SimulationRunModel::STATUS_CANCELLED, $run->status);
        $this->assertNotNull($run->finished_at);
    }

    #[Test]
    public function status_poll_marks_stuck_run_as_failed(): void
    {
        config([
            'platform.control_plane' => true,
            'platform.simulation.no_progress_timeout_minutes' => 5,
            'platform.simulation.startup_grace_minutes' => 1,
            'platform_auth.web_auth_enabled' => true,
        ]);

        $tenant = TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Stuck',
            'slug'   => 'stuck-tenant',
            'status' => 'active',
            'settings' => [],
        ]);

        User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $run = SimulationRunModel::query()->create([
            'id'                => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
            'tenant_id'         => $tenant->id,
            'status'            => SimulationRunModel::STATUS_RUNNING,
            'fixture_slug'      => 'tenant-catalog',
            'events_per_minute' => 10,
            'duration_minutes'  => 1,
            'planned_total'     => 10,
            'prepare_first'     => true,
            'progress_current'  => 0,
            'published'         => 0,
            'started_at'        => now()->subMinutes(6),
        ]);

        $this->actingAs(User::query()->where('email', 'saas@local')->first())
            ->getJson('/control/simulations/'.$run->id.'/status')
            ->assertOk()
            ->assertJsonPath('run.status', SimulationRunModel::STATUS_FAILED);

        $run->refresh();
        $this->assertSame(SimulationRunModel::STATUS_FAILED, $run->status);
        $this->assertNotNull($run->error_message);
    }
}
