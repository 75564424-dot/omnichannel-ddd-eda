<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Metrics;

use App\Control\Infrastructure\Models\SimulationRunModel;
use Carbon\CarbonInterface;

/**
 * Facade for simulation run metrics capture, analysis, and presentation.
 */
final class SimulationRunMetricsCollector
{
    public function __construct(
        private readonly SimulationMetricsBaselineCapture $baselineCapture,
        private readonly SimulationRunReportBuilder $reportBuilder,
    ) {}

    /** @return array<string, mixed> */
    public function captureEnvironmentBaseline(): array
    {
        return $this->baselineCapture->captureEnvironmentBaseline();
    }

    /**
     * @param list<string> $eventIds
     * @param array<string, mixed> $baselineBefore
     * @param array<string, mixed> $baselineAfter
     *
     * @return array<string, mixed>
     */
    public function buildReport(
        SimulationRunModel $run,
        array $eventIds,
        ?CarbonInterface $startedAt,
        ?CarbonInterface $finishedAt,
        array $baselineBefore,
        array $baselineAfter,
    ): array {
        return $this->reportBuilder->buildReport(
            $run,
            $eventIds,
            $startedAt,
            $finishedAt,
            $baselineBefore,
            $baselineAfter,
        );
    }

    /** @return array<string, mixed> */
    public function presentationForListItem(SimulationRunModel $run): array
    {
        $planned = max(1, (int) $run->planned_total);
        $progress = min(100, (int) round(((int) $run->progress_current / $planned) * 100));

        return [
            'id'                => $run->id,
            'tenant_id'         => $run->tenant_id,
            'tenant_name'       => $run->tenant?->name ?? '—',
            'tenant_slug'       => $run->tenant?->slug ?? '—',
            'fixture_slug'      => $run->fixture_slug,
            'status'            => $run->status,
            'events_per_minute' => $run->events_per_minute,
            'duration_minutes'  => $run->duration_minutes,
            'planned_total'     => $run->planned_total,
            'published'         => $run->published,
            'progress_percent'  => $progress,
            'created_at'        => $run->created_at?->format('d/m/Y H:i:s'),
            'started_at'        => $run->started_at?->format('d/m/Y H:i:s'),
            'finished_at'       => $run->finished_at?->format('d/m/Y H:i:s'),
            'error_message'     => $run->error_message,
            'can_view_report'   => in_array($run->status, [
                SimulationRunModel::STATUS_COMPLETED,
                SimulationRunModel::STATUS_FAILED,
            ], true) && is_array($run->metrics) && $run->metrics !== [],
        ];
    }

    /** @return array<string, mixed> */
    public function presentationForRun(SimulationRunModel $run): array
    {
        $metrics = is_array($run->metrics) ? $run->metrics : [];

        return [
            'run' => [
                'id'                => $run->id,
                'status'            => $run->status,
                'tenant_id'         => $run->tenant_id,
                'tenant_name'       => $run->tenant?->name,
                'tenant_slug'       => $run->tenant?->slug,
                'fixture_slug'      => $run->fixture_slug,
                'events_per_minute' => $run->events_per_minute,
                'duration_minutes'  => $run->duration_minutes,
                'planned_total'     => $run->planned_total,
                'published'         => $run->published,
                'progress_current'  => $run->progress_current,
                'progress_percent'  => $run->planned_total > 0
                    ? min(100, (int) round(($run->progress_current / $run->planned_total) * 100))
                    : 0,
                'started_at'        => $run->started_at?->toDateTimeString(),
                'finished_at'       => $run->finished_at?->toDateTimeString(),
                'error_message'     => $run->error_message,
                'created_at'        => $run->created_at?->toDateTimeString(),
            ],
            'metrics' => $metrics,
        ];
    }
}
