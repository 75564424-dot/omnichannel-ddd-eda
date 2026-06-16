<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Progress;


use App\Simulation\Application\Services\Metrics\SimulationRunMetricsCollector;
use App\Simulation\Application\Services\Prepare\SimulationTenantSettingsSync;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;

/**
 * Marks a simulation run as completed and builds its metrics report.
 */
final class SimulationRunCompletionService
{
    public function __construct(
        private readonly SimulationRunMetricsCollector $metricsCollector,
        private readonly SimulationTenantSettingsSync $tenantSettingsSync,
    ) {}

    /**
     * @param list<string> $eventIds
     * @param array<string, mixed> $baselineBefore
     */
    public function complete(
        SimulationRunModel $run,
        TenantModel $tenant,
        array $eventIds,
        int $published,
        int $queueMatches,
        array $baselineBefore,
    ): SimulationRunModel {
        $baselineAfter = $this->metricsCollector->captureEnvironmentBaseline();

        $report = $this->metricsCollector->buildReport(
            $run,
            $eventIds,
            $run->started_at ?? now(),
            now(),
            $baselineBefore,
            $baselineAfter,
        );

        $run->update([
            'status'           => SimulationRunModel::STATUS_COMPLETED,
            'finished_at'      => now(),
            'published'        => $published,
            'queue_matches'    => $queueMatches,
            'progress_current' => $published,
            'event_ids'        => $eventIds,
            'metrics'          => $report,
            'error_message'    => null,
        ]);

        $this->tenantSettingsSync->recordLastRun($tenant, $run->fresh() ?? $run, [
            'published'     => $published,
            'queue_matches' => $queueMatches,
        ]);

        return $run->fresh(['tenant']) ?? $run;
    }
}
