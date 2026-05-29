<?php

declare(strict_types=1);

namespace App\Http\Controllers\Control;

use App\Control\Application\Services\SimulationRunCompletionService;
use App\Control\Application\Services\SimulationRunFailureHandler;
use App\Control\Application\Services\SimulationRunMetricsCollector;
use App\Control\Application\Services\TenantModuleCatalogService;
use App\Control\Infrastructure\Models\SimulationRunModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class SimulationRunInternalController
{
    public function __construct(
        private readonly TenantModuleCatalogService $moduleCatalog,
        private readonly SimulationRunMetricsCollector $metricsCollector,
        private readonly SimulationRunFailureHandler $failureHandler,
        private readonly SimulationRunCompletionService $completionService,
    ) {}

    public function show(string $run): JsonResponse
    {
        $model = $this->findRun($run);
        $tenant = $model->tenant;
        if ($tenant === null) {
            abort(404);
        }

        return response()->json([
            'data' => [
                'id'                => $model->id,
                'tenant_id'         => $model->tenant_id,
                'tenant_slug'       => $tenant->slug,
                'status'            => $model->status,
                'events_per_minute' => $model->events_per_minute,
                'duration_minutes'  => $model->duration_minutes,
                'planned_total'     => $model->planned_total,
                'prepare_first'     => $model->prepare_first,
                'fixture_slug'      => $model->fixture_slug,
                'modules_catalog'   => $this->moduleCatalog->getCatalog($tenant),
            ],
        ]);
    }

    public function progress(Request $request, string $run): JsonResponse
    {
        $model = $this->findRun($run);

        $current = max(0, (int) $request->input('progress_current', 0));
        $total   = max(0, (int) $request->input('planned_total', $model->planned_total));

        $model->update([
            'status'           => SimulationRunModel::STATUS_RUNNING,
            'progress_current' => $current,
            'published'        => $current,
            'started_at'       => $model->started_at ?? now(),
        ]);

        return response()->json([
            'data' => [
                'progress_current' => $current,
                'planned_total'    => $total,
                'percent'          => $total > 0 ? (int) round(($current / $total) * 100) : 0,
            ],
        ]);
    }

    public function complete(Request $request, string $run): JsonResponse
    {
        $model = $this->findRun($run);
        $tenant = $model->tenant;
        if ($tenant === null) {
            abort(404);
        }

        $eventIds = $request->input('event_ids', []);
        if (! is_array($eventIds)) {
            $eventIds = [];
        }
        $eventIds = array_values(array_filter(array_map('strval', $eventIds)));

        $published = (int) $request->input('published', count($eventIds));
        $queueMatches = (int) $request->input('queue_matches', 0);

        $baselineBefore = is_array($model->metrics['resources']['baseline_before'] ?? null)
            ? $model->metrics['resources']['baseline_before']
            : $this->metricsCollector->captureEnvironmentBaseline();

        $model = $this->completionService->complete(
            $model,
            $tenant,
            $eventIds,
            $published,
            $queueMatches,
            $baselineBefore,
        );

        return response()->json(['data' => ['status' => $model->status]]);
    }

    public function fail(Request $request, string $run): JsonResponse
    {
        $model = $this->findRun($run);
        $message = Str::limit((string) $request->input('error_message', 'Simulation failed.'), 2000);

        $context = $request->input('context', []);
        if (! is_array($context)) {
            $context = [];
        }

        $this->failureHandler->handle($model, $message, $context);

        return response()->json(['data' => ['status' => $model->fresh()->status]]);
    }

    private function findRun(string $run): SimulationRunModel
    {
        $model = SimulationRunModel::query()->with('tenant')->find($run);
        if ($model === null) {
            abort(404);
        }

        return $model;
    }
}
