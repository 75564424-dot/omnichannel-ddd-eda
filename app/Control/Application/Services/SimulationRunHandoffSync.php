<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;

/**
 * Merges worker handoff progress and terminal state (completed/failed) into control-plane rows.
 *
 * Local fleet workers write completion to disk when HTTP to the control plane is unavailable.
 */
final class SimulationRunHandoffSync
{
    public function __construct(
        private readonly SimulationRunHandoffStore $handoffStore,
        private readonly SimulationRunCompletionService $completionService,
        private readonly SimulationRunFailureHandler $failureHandler,
        private readonly SimulationRunMetricsCollector $metricsCollector,
    ) {}

    public function syncRun(SimulationRunModel $run): SimulationRunModel
    {
        if (! in_array($run->status, [
            SimulationRunModel::STATUS_PENDING,
            SimulationRunModel::STATUS_RUNNING,
        ], true)) {
            return $run;
        }

        $handoff = $this->handoffStore->readForSync($run->id);
        if ($handoff === null) {
            return $run;
        }

        $terminal = (string) ($handoff['terminal_status'] ?? '');

        return match ($terminal) {
            'completed' => $this->applyCompleted($run, $handoff),
            'failed'    => $this->applyFailed($run, $handoff),
            default     => $this->syncProgress($run, $handoff),
        };
    }

    public function syncActiveRuns(): void
    {
        $runs = SimulationRunModel::query()
            ->whereIn('status', [SimulationRunModel::STATUS_PENDING, SimulationRunModel::STATUS_RUNNING])
            ->get();

        foreach ($runs as $run) {
            $this->syncRun($run);
        }
    }

    /**
     * @param array<string, mixed> $handoff
     */
    private function applyCompleted(SimulationRunModel $run, array $handoff): SimulationRunModel
    {
        $payload = is_array($handoff['terminal_payload'] ?? null) ? $handoff['terminal_payload'] : [];
        $tenant = $run->tenant ?? TenantModel::query()->find($run->tenant_id);

        if ($tenant === null) {
            return $run;
        }

        $eventIds = is_array($payload['event_ids'] ?? null)
            ? array_values(array_filter(array_map('strval', $payload['event_ids'])))
            : [];
        $published = (int) ($payload['published'] ?? count($eventIds));
        $queueMatches = (int) ($payload['queue_matches'] ?? 0);
        $planned = max(1, (int) $run->planned_total);

        if ($published < $planned) {
            $this->failureHandler->handle(
                $run,
                "Solo se publicaron {$published} de {$planned} eventos en el silo cliente.",
                ['source' => 'handoff_terminal', 'published' => $published, 'planned_total' => $planned],
            );
            $this->handoffStore->forget($run->id);

            return $run->fresh(['tenant']) ?? $run;
        }

        $baselineBefore = is_array($run->metrics['resources']['baseline_before'] ?? null)
            ? $run->metrics['resources']['baseline_before']
            : $this->metricsCollector->captureEnvironmentBaseline();

        $completed = $this->completionService->complete(
            $run,
            $tenant,
            $eventIds,
            $published,
            $queueMatches,
            $baselineBefore,
        );

        $this->handoffStore->forget($run->id);

        return $completed;
    }

    /**
     * @param array<string, mixed> $handoff
     */
    private function applyFailed(SimulationRunModel $run, array $handoff): SimulationRunModel
    {
        $payload = is_array($handoff['terminal_payload'] ?? null) ? $handoff['terminal_payload'] : [];
        $message = (string) ($payload['error_message'] ?? 'La simulación falló en el silo cliente.');
        $context = is_array($payload['context'] ?? null) ? $payload['context'] : [];

        $this->failureHandler->handle($run, $message, $context);
        $this->handoffStore->forget($run->id);

        return $run->fresh(['tenant']) ?? $run;
    }

    /**
     * @param array<string, mixed> $handoff
     */
    private function syncProgress(SimulationRunModel $run, array $handoff): SimulationRunModel
    {
        $current = (int) ($handoff['progress_current'] ?? 0);

        if ($current <= (int) $run->progress_current) {
            return $run;
        }

        $run->update([
            'status'           => SimulationRunModel::STATUS_RUNNING,
            'progress_current' => $current,
            'published'        => $current,
            'started_at'       => $run->started_at ?? now(),
        ]);

        return $run->fresh(['tenant']) ?? $run;
    }
}
