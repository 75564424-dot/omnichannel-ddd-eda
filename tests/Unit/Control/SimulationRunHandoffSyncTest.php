<?php

declare(strict_types=1);

namespace Tests\Unit\Control;

use App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore;
use App\Simulation\Application\Services\Handoff\SimulationRunHandoffSync;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationRunHandoffSyncTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_syncs_progress_from_handoff_file_to_run_row(): void
    {
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
            'events_per_minute' => 10,
            'duration_minutes'  => 1,
            'planned_total'     => 10,
            'prepare_first'     => true,
            'progress_current'  => 0,
            'published'         => 0,
        ]);

        $store = app(SimulationRunHandoffStore::class);
        $store->write($run, $tenant, ['producers' => []]);
        $store->updateProgress($run->id, 4, 10);

        app(SimulationRunHandoffSync::class)->syncRun($run);

        $run->refresh();
        $this->assertSame(4, $run->progress_current);
        $this->assertSame(4, $run->published);
    }

    #[Test]
    public function it_completes_run_from_handoff_terminal_state_without_http(): void
    {
        config(['platform.control_plane' => true]);

        $tenant = TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas-retail',
            'status' => 'active',
            'settings' => [],
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
            'progress_current'  => 10,
            'published'         => 10,
            'metrics'           => ['resources' => ['baseline_before' => ['cpu' => 1]]],
        ]);

        $store = app(SimulationRunHandoffStore::class);
        $store->write($run, $tenant, ['producers' => []]);
        $store->markTerminal($run->id, 'completed', [
            'published'     => 10,
            'queue_matches' => 10,
            'event_ids'     => ['evt-1', 'evt-2'],
        ]);

        app(SimulationRunHandoffSync::class)->syncRun($run);

        $run->refresh();
        $this->assertSame(SimulationRunModel::STATUS_COMPLETED, $run->status);
        $this->assertNotNull($run->finished_at);
        $this->assertNull($store->readForSync($run->id));
    }
}

