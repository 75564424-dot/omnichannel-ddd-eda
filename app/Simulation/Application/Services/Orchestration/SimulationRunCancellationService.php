<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Orchestration;

use App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore;
use App\Simulation\Application\Services\Metrics\SimulationRunMetricsCollector;
use App\Simulation\Application\Services\Runtime\SimulationPulseService;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Security\Contracts\AuditLogWriterInterface;
use RuntimeException;

/**
 * Cancels an active simulation run and signals detached workers to stop.
 */
final class SimulationRunCancellationService
{
    public function __construct(
        private readonly SimulationRunHandoffStore $handoffStore,
        private readonly SimulationRunMetricsCollector $metricsCollector,
        private readonly SimulationPulseService $simulationPulse,
        private readonly AuditLogWriterInterface $auditLogWriter,
    ) {}

    public function cancel(SimulationRunModel $run, ?int $userId): SimulationRunModel
    {
        $run = $run->fresh(['tenant']);
        if ($run === null) {
            throw new RuntimeException('Simulación no encontrada.');
        }

        if ($run->status !== SimulationRunModel::STATUS_RUNNING) {
            throw new RuntimeException('Solo se pueden cancelar simulaciones en curso.');
        }

        $published = max((int) $run->published, (int) $run->progress_current);
        $baselineBefore = is_array($run->metrics['resources']['baseline_before'] ?? null)
            ? $run->metrics['resources']['baseline_before']
            : $this->metricsCollector->captureEnvironmentBaseline();
        $baselineAfter = $this->metricsCollector->captureEnvironmentBaseline();

        $report = $this->metricsCollector->buildReport(
            $run,
            is_array($run->event_ids) ? $run->event_ids : [],
            $run->started_at ?? now(),
            now(),
            $baselineBefore,
            $baselineAfter,
        );
        $report['summary']['status'] = SimulationRunModel::STATUS_CANCELLED;
        $report['summary']['published'] = $published;
        $report['cancellation'] = [
            'cancelled_by_user_id' => $userId,
            'cancelled_at'         => now()->toIso8601String(),
        ];

        $this->handoffStore->requestCancel($run->id, $userId);
        $this->handoffStore->markTerminal($run->id, 'cancelled', [
            'published' => $published,
            'cancelled_by_user_id' => $userId,
        ]);
        $this->handoffStore->forget($run->id);

        $run->update([
            'status'           => SimulationRunModel::STATUS_CANCELLED,
            'finished_at'      => now(),
            'progress_current' => $published,
            'published'        => $published,
            'metrics'          => $report,
            'error_message'    => null,
        ]);

        $this->simulationPulse->clear();

        $this->auditLogWriter->record(
            action: 'simulation.cancelled',
            entityType: 'simulation_run',
            entityId: $run->id,
            changes: [
                'tenant_id' => $run->tenant_id,
                'published' => $published,
                'planned_total' => $run->planned_total,
            ],
            actorType: $userId !== null ? 'user' : 'system',
            actorId: $userId !== null ? (string) $userId : null,
        );

        return $run->fresh(['tenant']) ?? $run;
    }
}
