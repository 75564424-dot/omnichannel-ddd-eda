<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Progress;


use App\Simulation\Application\Services\Metrics\SimulationRunMetricsCollector;
use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Control\Infrastructure\Models\SimulationRunModel;
use Illuminate\Support\Str;

final class SimulationRunInternalApiService
{
    public function __construct(
        private readonly TenantModuleCatalogService $moduleCatalog,
        private readonly SimulationRunMetricsCollector $metricsCollector,
        private readonly SimulationRunFailureHandler $failureHandler,
        private readonly SimulationRunCompletionService $completionService,
    ) {}

    public function findRun(string $run): SimulationRunModel
    {
        $model = SimulationRunModel::query()->with('tenant')->find($run);
        if ($model === null) {
            abort(404);
        }

        return $model;
    }

    /** @return array<string, mixed> */
    public function showPayload(SimulationRunModel $model): array
    {
        $tenant = $model->tenant;
        if ($tenant === null) {
            abort(404);
        }

        return [
            'id' => $model->id,
            'tenant_id' => $model->tenant_id,
            'tenant_slug' => $tenant->slug,
            'status' => $model->status,
            'events_per_minute' => $model->events_per_minute,
            'duration_minutes' => $model->duration_minutes,
            'planned_total' => $model->planned_total,
            'prepare_first' => $model->prepare_first,
            'fixture_slug' => $model->fixture_slug,
            'modules_catalog' => $this->moduleCatalog->getCatalog($tenant),
        ];
    }

    /** @return array{progress_current: int, planned_total: int, percent: int} */
    public function recordProgress(SimulationRunModel $model, int $current, int $total): array
    {
        $model->update([
            'status' => SimulationRunModel::STATUS_RUNNING,
            'progress_current' => max(0, $current),
            'published' => max(0, $current),
            'started_at' => $model->started_at ?? now(),
        ]);

        $total = max(0, $total > 0 ? $total : $model->planned_total);

        return [
            'progress_current' => max(0, $current),
            'planned_total' => $total,
            'percent' => $total > 0 ? (int) round((max(0, $current) / $total) * 100) : 0,
        ];
    }

    public function complete(SimulationRunModel $model, array $eventIds, int $published, int $queueMatches): SimulationRunModel
    {
        $tenant = $model->tenant;
        if ($tenant === null) {
            abort(404);
        }

        $baselineBefore = is_array($model->metrics['resources']['baseline_before'] ?? null)
            ? $model->metrics['resources']['baseline_before']
            : $this->metricsCollector->captureEnvironmentBaseline();

        return $this->completionService->complete(
            $model,
            $tenant,
            $eventIds,
            $published,
            $queueMatches,
            $baselineBefore,
        );
    }

    public function fail(SimulationRunModel $model, string $message, array $context): void
    {
        $this->failureHandler->handle(
            $model,
            Str::limit($message, 2000),
            $context,
        );
    }

    /** @return list<string> */
    public function normalizeEventIds(mixed $eventIds): array
    {
        if (! is_array($eventIds)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $eventIds)));
    }
}
