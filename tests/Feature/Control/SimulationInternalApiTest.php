<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationInternalApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function internal_progress_endpoint_updates_run_without_csrf(): void
    {
        config([
            'platform.control_plane' => true,
            'platform.simulation.internal_token' => 'test-internal-token',
        ]);

        $tenant = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        $run = SimulationRunModel::query()->create([
            'id'                => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'tenant_id'         => $tenant->id,
            'status'            => SimulationRunModel::STATUS_RUNNING,
            'fixture_slug'      => 'tenant-catalog',
            'events_per_minute' => 20,
            'duration_minutes'  => 2,
            'planned_total'     => 40,
            'prepare_first'     => true,
            'progress_current'  => 0,
            'published'         => 0,
        ]);

        $this->patchJson('/control/internal/simulation-runs/'.$run->id.'/progress', [
            'progress_current' => 5,
            'planned_total'    => 40,
        ], [
            'X-Simulation-Internal-Token' => 'test-internal-token',
        ])
            ->assertOk()
            ->assertJsonPath('data.progress_current', 5);

        $run->refresh();
        $this->assertSame(5, $run->progress_current);
        $this->assertSame(5, $run->published);
    }
}
