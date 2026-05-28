<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Control\Infrastructure\Jobs\RunTenantSimulationJob;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use RuntimeException;

final class SimulationRunOrchestrator
{
    public function __construct(
        private readonly TenantSimulationAutomationService $automation,
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

        $plannedTotal = $eventsPerMinute * $durationMinutes;
        $fixtureSlug  = $this->automation->resolveFixtureSlug($tenant);

        $run = SimulationRunModel::query()->create([
            'id'                  => Uuid::uuid4()->toString(),
            'tenant_id'           => $tenant->id,
            'started_by_user_id'  => $userId,
            'status'              => SimulationRunModel::STATUS_PENDING,
            'fixture_slug'        => $fixtureSlug,
            'events_per_minute'   => $eventsPerMinute,
            'duration_minutes'    => $durationMinutes,
            'planned_total'       => $plannedTotal,
            'prepare_first'       => $prepareFirst,
        ]);

        RunTenantSimulationJob::dispatch($run->id)->afterResponse();

        return [
            'run_id' => $run->id,
            'status' => $run->status,
        ];
    }

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
        if ($tenant === null) {
            $this->markFailed($run, 'Tenant no encontrado.');

            return;
        }

        $metricsCollector = app(SimulationRunMetricsCollector::class);

        $run->update([
            'status'     => SimulationRunModel::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        $baselineBefore = $metricsCollector->captureEnvironmentBaseline();

        try {
            set_time_limit(max(300, $run->duration_minutes * 90));

            $result = $this->automation->run(
                tenant: $tenant,
                eventsPerMinute: $run->events_per_minute,
                durationMinutes: $run->duration_minutes,
                totalEvents: $run->planned_total,
                skipPrepare: ! $run->prepare_first,
                onProgress: function (int $current, int $total, array $eventIds) use ($run): void {
                    SimulationRunModel::query()->where('id', $run->id)->update([
                        'progress_current' => $current,
                        'published'        => $current,
                        'event_ids'        => $eventIds,
                    ]);
                },
            );

            $baselineAfter = $metricsCollector->captureEnvironmentBaseline();
            $eventIds = $result['event_ids'];

            $report = $metricsCollector->buildReport(
                $run,
                $eventIds,
                $run->started_at,
                now(),
                $baselineBefore,
                $baselineAfter,
            );

            $run->update([
                'status'        => SimulationRunModel::STATUS_COMPLETED,
                'finished_at'   => now(),
                'published'     => $result['published'],
                'queue_matches' => $result['queue_matches'],
                'progress_current' => $result['published'],
                'metrics'       => $report,
                'event_ids'     => $eventIds,
            ]);

            $this->syncTenantLastSimulation($tenant, $run, $result);
        } catch (\Throwable $e) {
            $this->markFailed($run, $e->getMessage(), $baselineBefore, $metricsCollector);
        }
    }

    private function markFailed(
        SimulationRunModel $run,
        string $message,
        ?array $baselineBefore = null,
        ?SimulationRunMetricsCollector $collector = null,
    ): void {
        $metrics = null;
        if ($baselineBefore !== null && $collector !== null) {
            $metrics = [
                'summary' => [
                    'status'       => SimulationRunModel::STATUS_FAILED,
                    'error'        => $message,
                    'started_at'   => $run->started_at?->toDateTimeString(),
                    'finished_at'  => now()->toDateTimeString(),
                ],
                'resources' => [
                    'baseline_before' => $baselineBefore,
                ],
            ];
        }

        $run->update([
            'status'        => SimulationRunModel::STATUS_FAILED,
            'finished_at'   => now(),
            'error_message' => Str::limit($message, 2000),
            'metrics'       => $metrics,
        ]);
    }

    /** @param array<string, mixed> $result */
    private function syncTenantLastSimulation(TenantModel $tenant, SimulationRunModel $run, array $result): void
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['last_simulation'] = [
            'run_id'            => $run->id,
            'ran_at'            => now()->toDateTimeString(),
            'fixture_slug'      => $run->fixture_slug,
            'events_per_minute' => $run->events_per_minute,
            'duration_minutes'  => $run->duration_minutes,
            'planned_total'     => $run->planned_total,
            'published'         => $result['published'],
            'queue_matches'     => $result['queue_matches'],
            'has_report'        => true,
        ];
        $tenant->update(['settings' => $settings]);
    }
}
