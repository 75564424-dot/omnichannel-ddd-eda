<?php

declare(strict_types=1);

namespace Tests\Unit\Control;

use App\Control\Application\Services\SimulationRunHandoffStore;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationRunHandoffStoreTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_writes_and_reads_handoff_payload(): void
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
            'status'            => SimulationRunModel::STATUS_PENDING,
            'fixture_slug'      => 'tenant-catalog',
            'events_per_minute' => 30,
            'duration_minutes'  => 2,
            'planned_total'     => 60,
            'prepare_first'     => true,
        ]);

        $store = app(SimulationRunHandoffStore::class);
        $catalog = ['producers' => [['event_types_emitted' => ['demo.event']]]];

        $store->write($run, $tenant, $catalog);
        $read = $store->read($run->id);

        $this->assertIsArray($read);
        $this->assertSame('pruebas-retail', $read['tenant_slug']);
        $this->assertSame(30, $read['events_per_minute']);
        $this->assertSame(60, $read['planned_total']);

        $store->forget($run->id);
        $this->assertNull($store->read($run->id));
    }
}
