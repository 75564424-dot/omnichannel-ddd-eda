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
}

