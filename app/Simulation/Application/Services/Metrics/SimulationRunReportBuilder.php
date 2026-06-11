<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Metrics;

use App\Control\Application\Services\Tenants\TenantPresentationService;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Simulation\Application\Services\Metrics\Support\SimulationRunReportReliabilityMapper;
use App\Simulation\Application\Services\Metrics\Support\SimulationRunReportSummaryMapper;
use App\Simulation\Application\Services\Metrics\Support\SimulationRunReportThroughputMapper;
use Carbon\CarbonInterface;

final class SimulationRunReportBuilder
{
    public function __construct(
        private readonly SimulationQueueMetricsAnalyzer $queueMetrics,
        private readonly TenantPresentationService $tenantPresentation,
        private readonly SimulationRunReportSummaryMapper $summaryMapper,
        private readonly SimulationRunReportThroughputMapper $throughputMapper,
        private readonly SimulationRunReportReliabilityMapper $reliabilityMapper,
    ) {}

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
        $queueStats = $this->queueMetrics->queueStatsForEvents($eventIds);
        $timing = $this->queueMetrics->interEventTiming($eventIds, $run->events_per_minute);

        $durationSec = ($startedAt && $finishedAt)
            ? max(1, $finishedAt->diffInSeconds($startedAt))
            : max(1, $run->duration_minutes * 60);
        $published = (int) $run->published;

        return [
            'summary' => $this->summaryMapper->map($run, $startedAt, $finishedAt, $durationSec),
            'throughput' => $this->throughputMapper->map($run, $durationSec, $published, $timing),
            'latency' => $this->reliabilityMapper->mapLatency($queueStats, $baselineAfter),
            'reliability' => $this->reliabilityMapper->mapReliability($queueStats, $published),
            'resources' => $this->reliabilityMapper->mapResources($baselineBefore, $baselineAfter),
            'consumption' => $this->tenantPresentation->consumptionForTenant($run->tenant_id),
        ];
    }
}
