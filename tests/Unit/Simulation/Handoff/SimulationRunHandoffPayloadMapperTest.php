<?php

declare(strict_types=1);

namespace Tests\Unit\Simulation\Handoff;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Simulation\Application\Services\Handoff\Support\SimulationRunHandoffPayloadMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationRunHandoffPayloadMapperTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_builds_dispatch_payload_with_deadline_and_progress_defaults(): void
    {
        $tenant = TenantModel::query()->create([
            'id'       => '11111111-1111-1111-1111-111111111111',
            'name'     => 'Acme',
            'slug'     => 'acme-retail',
            'status'   => 'active',
            'settings' => [],
        ]);

        $run = SimulationRunModel::query()->create([
            'id'                => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'tenant_id'         => $tenant->id,
            'status'            => SimulationRunModel::STATUS_PENDING,
            'fixture_slug'      => 'tenant-catalog',
            'events_per_minute' => 30,
            'duration_minutes'  => 2,
            'planned_total'     => 60,
            'prepare_first'     => true,
        ]);

        $payload = (new SimulationRunHandoffPayloadMapper())->buildDispatchPayload(
            $run,
            $tenant,
            ['producers' => []],
        );

        $this->assertSame('acme-retail', $payload['tenant_slug']);
        $this->assertSame(30, $payload['events_per_minute']);
        $this->assertSame(60, $payload['planned_total']);
        $this->assertSame('dispatched', $payload['phase']);
        $this->assertSame(0, $payload['progress_current']);
        $this->assertNotEmpty($payload['deadline_at']);
    }

    #[Test]
    public function it_applies_progress_percent_without_exceeding_one_hundred(): void
    {
        $mapper = new SimulationRunHandoffPayloadMapper();

        $updated = $mapper->applyProgress(['run_id' => 'run-1'], 30, 60, 'publishing');

        $this->assertSame(30, $updated['progress_current']);
        $this->assertSame(50, $updated['progress_percent']);
        $this->assertSame('publishing', $updated['phase']);
    }
}
