<?php

declare(strict_types=1);

namespace Tests\Unit\Simulation\Metrics;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Simulation\Application\Services\Metrics\Support\SimulationRunReportDurationFormatter;
use App\Simulation\Application\Services\Metrics\Support\SimulationRunReportSummaryMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationRunReportSummaryMapperTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_maps_summary_with_human_duration_and_publish_rate(): void
    {
        $tenant = TenantModel::query()->create([
            'id'       => '22222222-2222-2222-2222-222222222222',
            'name'     => 'Beta Corp',
            'slug'     => 'beta-corp',
            'status'   => 'active',
            'settings' => [],
        ]);

        $run = SimulationRunModel::query()->create([
            'id'                => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
            'tenant_id'         => $tenant->id,
            'status'            => SimulationRunModel::STATUS_COMPLETED,
            'fixture_slug'      => 'beta-fixture',
            'events_per_minute' => 10,
            'duration_minutes'  => 1,
            'planned_total'     => 10,
            'published'         => 8,
            'queue_matches'     => 8,
            'prepare_first'     => false,
        ]);
        $run->setRelation('tenant', $tenant);

        $startedAt = now()->subSeconds(90);
        $finishedAt = now();

        $summary = (new SimulationRunReportSummaryMapper(new SimulationRunReportDurationFormatter()))
            ->map($run, $startedAt, $finishedAt, 90);

        $this->assertSame('Beta Corp', $summary['tenant_name']);
        $this->assertSame('beta-corp', $summary['tenant_slug']);
        $this->assertSame(80.0, $summary['publish_rate_pct']);
        $this->assertSame('1m 30s', $summary['duration_human']);
        $this->assertSame(8, $summary['published']);
    }
}
