<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Control\Infrastructure\Jobs\RunTenantSimulationJob;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * Starts simulation runs (fleet worker or in-process job) and executes CP-hosted runs.
 */
final class SimulationRunOrchestrator
{
    public function __construct(
        private readonly TenantSimulationAutomationService $automation,
        private readonly LocalFleetSimulationRunner $localFleetRunner,
        private readonly SimulationRunFailureHandler $failureHandler,
        private readonly SimulationRunMetricsCollector $metricsCollector,
        private readonly SimulationRunCompletionService $completionService,
        private readonly SimulationStaleRunReplacer $staleRunReplacer,
    ) {}

    /**
     * @return array{run_id: string, status: string}
     */
    public function start(
        TenantModel $tenant,
        int $eventsPerMinute,
        int $durationMinutes,
        bool $prepareFirst,
        ?int $userId,
    ): array {
        $reason = $this->automation->simulationBlockReason($tenant);
        if ($reason !== null) {
            throw new RuntimeException($reason);
        }

        $this->staleRunReplacer->replaceActiveForTenant($tenant->id);

        $plannedTotal = $eventsPerMinute * $durationMinutes;
        $fixtureSlug  = $this->localFleetRunner->shouldRunOnClientSilo($tenant)
            ? 'tenant-catalog'
            : $this->automation->resolveFixtureSlug($tenant);

        $run = SimulationRunModel::query()->create([
            'id'                 => Uuid::uuid4()->toString(),
            'tenant_id'          => $tenant->id,
            'started_by_user_id' => $userId,
            'status'             => SimulationRunModel::STATUS_PENDING,
            'fixture_slug'       => $fixtureSlug,
            'events_per_minute'  => $eventsPerMinute,
            'duration_minutes'   => $durationMinutes,
            'planned_total'      => $plannedTotal,
            'prepare_first'      => $prepareFirst,
        ]);

        if ($this->localFleetRunner->shouldRunOnClientSilo($tenant)) {
            $this->localFleetRunner->dispatchToClientSilo($run->id);
        } else {
            RunTenantSimulationJob::dispatch($run->id)->afterResponse();
        }

        return [
            'run_id' => $run->id,
            'status' => $run->status,
        ];
    }

    /**
     * In-process execution when simulation is not delegated to a client silo worker.
     */
    public function executeRun(string $runId): void
    {
        $run = SimulationRunModel::query()->with('tenant')->find($runId);
        if ($run === null) {
            return;
        }

        if (! in_array($run->status, [SimulationRunModel::STATUS_PENDING, SimulationRunModel::STATUS_RUNNING], true)) {
            return;
        }

        $tenant = $run->tenant;
        if ($tenant !== null && $this->localFleetRunner->shouldRunOnClientSilo($tenant)
            && ! $this->localFleetRunner->isClientSiloProcess()) {
            return;
        }

        if ($tenant === null) {
            $this->failureHandler->handle($run, SimulationMessages::TENANT_NOT_FOUND);

            return;
        }

        $run->update([
            'status'     => SimulationRunModel::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        $baselineBefore = $this->metricsCollector->captureEnvironmentBaseline();

        try {
            set_time_limit(max(300, $run->duration_minutes * 90));

            $result = $this->automation->run(
                tenant: $tenant,
                eventsPerMinute: $run->events_per_minute,
                durationMinutes: $run->duration_minutes,
                totalEvents: $run->planned_total,
                skipPrepare: ! $run->prepare_first,
                onProgress: fn (int $current, int $total) => $this->updateProgress($run, $current),
            );

            $this->completionService->complete(
                $run,
                $tenant,
                $result['event_ids'],
                $result['published'],
                $result['queue_matches'],
                $baselineBefore,
            );
        } catch (\Throwable $e) {
            $this->failureHandler->handle($run, $e->getMessage(), [
                'baseline_before' => $baselineBefore,
            ]);
        }
    }

    private function updateProgress(SimulationRunModel $run, int $current): void
    {
        SimulationRunModel::query()->where('id', $run->id)->update([
            'status'           => SimulationRunModel::STATUS_RUNNING,
            'progress_current' => $current,
            'published'        => $current,
            'started_at'       => $run->started_at ?? now(),
        ]);
    }
}
