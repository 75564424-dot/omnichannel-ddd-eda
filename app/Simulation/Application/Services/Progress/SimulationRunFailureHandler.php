<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Progress;


use App\Simulation\Application\Services\Metrics\SimulationRunMetricsCollector;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\ClientIncidentReportModel;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * Marks a simulation run as failed, builds a diagnostic report, and registers a control-plane incident.
 */
final class SimulationRunFailureHandler
{
    public function __construct(
        private readonly SimulationRunMetricsCollector $metricsCollector,
    ) {}

    /**
     * @param array<string, mixed> $context
     */
    public function handle(SimulationRunModel $run, string $errorMessage, array $context = []): void
    {
        $run = $run->fresh(['tenant']);
        if ($run === null) {
            return;
        }

        if ($run->status === SimulationRunModel::STATUS_FAILED
            && $run->error_message === Str::limit($errorMessage, 2000)) {
            return;
        }

        $message = Str::limit($errorMessage, 2000);
        $metrics = $this->buildFailureMetrics($run, $message, $context);

        $run->update([
            'status'        => SimulationRunModel::STATUS_FAILED,
            'finished_at'   => now(),
            'error_message' => $message,
            'metrics'       => $metrics,
        ]);

        $this->registerIncident($run->fresh(['tenant']), $message, $context, $metrics);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    private function buildFailureMetrics(
        SimulationRunModel $run,
        string $message,
        array $context,
    ): array {
        $baselineBefore = is_array($run->metrics['resources']['baseline_before'] ?? null)
            ? $run->metrics['resources']['baseline_before']
            : $this->metricsCollector->captureEnvironmentBaseline();

        $baselineAfter = $this->metricsCollector->captureEnvironmentBaseline();
        $published = max((int) $run->published, (int) $run->progress_current);
        $eventIds = is_array($run->event_ids) ? $run->event_ids : [];

        $report = $this->metricsCollector->buildReport(
            $run,
            $eventIds,
            $run->started_at ?? $run->created_at ?? now(),
            now(),
            $baselineBefore,
            $baselineAfter,
        );

        $report['summary']['status'] = SimulationRunModel::STATUS_FAILED;
        $report['summary']['error'] = $message;
        $report['summary']['published'] = $published;
        $report['failure'] = [
            'worker_log_excerpt' => isset($context['worker_log']) ? Str::limit((string) $context['worker_log'], 4000) : null,
            'handoff_used'       => (bool) ($context['handoff_used'] ?? false),
            'instance_slug'      => $context['instance_slug'] ?? null,
            'expected_slug'      => $context['expected_slug'] ?? null,
        ];

        return $report;
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $metrics
     */
    private function registerIncident(
        SimulationRunModel $run,
        string $message,
        array $context,
        array $metrics,
    ): void {
        if (! config('platform.control_plane', false)) {
            return;
        }

        $tenant = $run->tenant;
        $runId = $run->id;

        $exists = ClientIncidentReportModel::query()
            ->where('diagnostic_log->run_id', $runId)
            ->exists();

        if ($exists) {
            return;
        }

        ClientIncidentReportModel::query()->create([
            'id'             => Uuid::uuid4()->toString(),
            'tenant_id'      => $tenant?->id,
            'user_id'        => $run->started_by_user_id,
            'reporter_name'  => 'Sistema (simulación)',
            'reporter_email' => 'simulation@control-plane',
            'tenant_name'    => $tenant?->name,
            'tenant_slug'    => $tenant?->slug,
            'subject'        => 'Simulación fallida — '.($tenant?->name ?? 'tenant'),
            'description'    => $message,
            'severity'       => 'high',
            'status'         => 'open',
            'page_url'       => '/control/simulations?run='.$runId,
            'diagnostic_log' => [
                'source'      => 'simulation_failure',
                'run_id'      => $runId,
                'run_status'  => SimulationRunModel::STATUS_FAILED,
                'fixture_slug'=> $run->fixture_slug,
                'planned_total' => $run->planned_total,
                'published'   => $run->published,
                'context'     => $context,
                'metrics'     => $metrics['summary'] ?? [],
            ],
        ]);
    }
}
