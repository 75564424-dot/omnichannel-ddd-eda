<?php

declare(strict_types=1);

namespace Tests\Unit\Control;

use App\Simulation\Application\Services\Worker\SimulationRunWorkerMonitor;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationRunWorkerMonitorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function max_wall_clock_minutes_scales_with_planned_events(): void
    {
        $tenant = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        $small = SimulationRunModel::query()->create([
            'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'tenant_id' => $tenant->id,
            'status' => SimulationRunModel::STATUS_RUNNING,
            'fixture_slug' => 'tenant-catalog',
            'events_per_minute' => 10,
            'duration_minutes' => 1,
            'planned_total' => 10,
            'prepare_first' => true,
        ]);

        $large = SimulationRunModel::query()->create([
            'id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
            'tenant_id' => $tenant->id,
            'status' => SimulationRunModel::STATUS_RUNNING,
            'fixture_slug' => 'tenant-catalog',
            'events_per_minute' => 20,
            'duration_minutes' => 2,
            'planned_total' => 40,
            'prepare_first' => true,
        ]);

        $monitor = app(SimulationRunWorkerMonitor::class);

        $this->assertGreaterThan(
            $monitor->maxWallClockMinutes($small),
            $monitor->maxWallClockMinutes($large),
        );
        $this->assertGreaterThanOrEqual(10, $monitor->maxWallClockMinutes($large));
    }

    #[Test]
    public function dispatched_handoff_without_worker_log_is_not_treated_as_alive(): void
    {
        $tenant = TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas',
            'status' => 'active',
            'settings' => [],
        ]);

        $run = SimulationRunModel::query()->create([
            'id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
            'tenant_id' => $tenant->id,
            'status' => SimulationRunModel::STATUS_RUNNING,
            'fixture_slug' => 'tenant-catalog',
            'events_per_minute' => 10,
            'duration_minutes' => 1,
            'planned_total' => 10,
            'prepare_first' => true,
            'started_at' => now()->subMinutes(4),
        ]);

        $store = app(\App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore::class);
        $store->write($run, $tenant, ['producers' => [['id' => 'p1', 'event_types_emitted' => ['Ev.Test']]]]);

        $monitor = app(SimulationRunWorkerMonitor::class);
        $this->assertFalse($monitor->isLikelyAlive($run));
    }

    #[Test]
    public function publishing_handoff_with_stale_progress_is_not_treated_as_alive(): void
    {
        $tenant = TenantModel::query()->create([
            'id'     => '33333333-3333-3333-3333-333333333333',
            'name'   => 'Pruebas',
            'slug'   => 'pruebas',
            'status' => 'active',
            'settings' => [],
        ]);

        $run = SimulationRunModel::query()->create([
            'id' => 'dddddddd-dddd-dddd-dddd-dddddddddddd',
            'tenant_id' => $tenant->id,
            'status' => SimulationRunModel::STATUS_RUNNING,
            'fixture_slug' => 'tenant-catalog',
            'events_per_minute' => 10,
            'duration_minutes' => 1,
            'planned_total' => 10,
            'prepare_first' => true,
            'started_at' => now()->subMinutes(5),
            'progress_current' => 1,
        ]);

        $store = app(\App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore::class);
        $store->write($run, $tenant, ['producers' => [['id' => 'p1', 'event_types_emitted' => ['Ev.Test']]]]);
        $store->updateProgress($run->id, 1, 10, 'publishing');

        $handoff = $store->read($run->id);
        $this->assertIsArray($handoff);
        $handoff['progress_at'] = now()->subMinutes(4)->toIso8601String();
        app(\App\Simulation\Application\Services\Handoff\Support\SimulationRunHandoffFileGateway::class)
            ->write($run->id, $handoff);

        $monitor = app(SimulationRunWorkerMonitor::class);
        $this->assertFalse($monitor->isLikelyAlive($run));
    }
}

