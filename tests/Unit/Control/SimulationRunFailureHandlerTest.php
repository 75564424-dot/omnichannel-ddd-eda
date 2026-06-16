<?php

declare(strict_types=1);

namespace Tests\Unit\Control;

use App\Simulation\Application\Services\Progress\SimulationRunFailureHandler;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\ClientIncidentReportModel;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationRunFailureHandlerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_registers_incident_and_failure_report_on_control_plane(): void
    {
        config(['platform.control_plane' => true]);

        $tenant = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Pruebas Retail',
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
        ]);

        app(SimulationRunFailureHandler::class)->handle(
            $run,
            'Tenant slug no coincide con instancia.',
            ['instance_slug' => 'platform', 'expected_slug' => 'pruebas-retail'],
        );

        $run->refresh();
        $this->assertSame(SimulationRunModel::STATUS_FAILED, $run->status);
        $this->assertIsArray($run->metrics);
        $this->assertSame('failed', $run->metrics['summary']['status'] ?? null);

        $incident = ClientIncidentReportModel::query()
            ->where('diagnostic_log->run_id', $run->id)
            ->first();

        $this->assertNotNull($incident);
        $this->assertStringContainsString('Simulación fallida', $incident->subject);
    }
}

