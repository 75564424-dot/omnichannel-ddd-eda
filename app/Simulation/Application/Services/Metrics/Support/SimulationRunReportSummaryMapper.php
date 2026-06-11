<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Metrics\Support;

use App\Control\Infrastructure\Models\SimulationRunModel;
use Carbon\CarbonInterface;

final class SimulationRunReportSummaryMapper
{
    public function __construct(
        private readonly SimulationRunReportDurationFormatter $durationFormatter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function map(
        SimulationRunModel $run,
        ?CarbonInterface $startedAt,
        ?CarbonInterface $finishedAt,
        int $durationSec,
    ): array {
        $planned = max(1, (int) $run->planned_total);
        $published = (int) $run->published;

        return [
            'tenant_name'       => $run->tenant?->name ?? '—',
            'tenant_slug'       => $run->tenant?->slug ?? '—',
            'fixture_slug'      => $run->fixture_slug,
            'status'            => $run->status,
            'started_at'        => $startedAt?->toDateTimeString(),
            'finished_at'       => $finishedAt?->toDateTimeString(),
            'duration_seconds'  => $durationSec,
            'duration_human'    => $this->durationFormatter->format($durationSec),
            'planned_total'     => $planned,
            'published'         => $published,
            'publish_rate_pct'  => round(($published / $planned) * 100, 1),
            'queue_matches'     => (int) $run->queue_matches,
        ];
    }
}
