<?php

declare(strict_types=1);

namespace App\Http\Controllers\Control;

use App\Control\Application\Services\SimulationRunHandoffProgressSync;
use App\Control\Application\Services\SimulationRunMetricsCollector;
use App\Control\Application\Services\SimulationRunOrchestrator;
use App\Control\Application\Services\SimulationRunStaleGuard;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class SimulationRunController
{
    public function __construct(
        private readonly SimulationRunOrchestrator $orchestrator,
        private readonly SimulationRunMetricsCollector $metricsCollector,
        private readonly SimulationRunStaleGuard $staleGuard,
        private readonly SimulationRunHandoffProgressSync $handoffProgressSync,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('platform.manage-users');

        $this->handoffProgressSync->syncActiveRuns();
        $this->staleGuard->failExpiredRuns();

        $tenantId = $request->string('tenant_id')->toString();
        $tenantId = $tenantId !== '' ? $tenantId : null;

        $query = SimulationRunModel::query()
            ->with('tenant')
            ->orderByDesc('created_at');

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $runs = $query
            ->paginate(25)
            ->withQueryString()
            ->through(fn (SimulationRunModel $run) => $this->metricsCollector->presentationForListItem($run));

        $tenants = TenantModel::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (TenantModel $t) => [
                'id'   => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
            ])
            ->values()
            ->all();

        return Inertia::render('Control/Simulation/Index', [
            'runs'    => $runs,
            'filters' => ['tenant_id' => $tenantId],
            'tenants' => $tenants,
            'highlight_run_id' => $request->string('run')->toString() ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('platform.manage-users');

        $validated = $request->validate([
            'tenant_id'         => ['required', 'uuid', 'exists:tenants,id'],
            'events_per_minute' => ['required', 'integer', 'min:1', 'max:600'],
            'duration_minutes'  => ['required', 'integer', 'min:1', 'max:120'],
            'total_events'      => ['nullable', 'integer', 'min:1', 'max:50000'],
            'prepare_first'     => ['sometimes', 'boolean'],
        ]);

        $tenant = TenantModel::query()->findOrFail($validated['tenant_id']);
        $planned = (int) $validated['events_per_minute'] * (int) $validated['duration_minutes'];

        if (isset($validated['total_events']) && (int) $validated['total_events'] !== $planned) {
            return back()->withErrors([
                'simulation' => "Total de eventos debe ser {$planned} (eventos/min × minutos).",
            ])->withInput();
        }

        $user = $request->user();
        $userId = $user instanceof User ? (int) $user->getKey() : null;

        try {
            $started = $this->orchestrator->start(
                tenant: $tenant,
                eventsPerMinute: (int) $validated['events_per_minute'],
                durationMinutes: (int) $validated['duration_minutes'],
                prepareFirst: (bool) ($validated['prepare_first'] ?? true),
                userId: $userId,
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['simulation' => $e->getMessage()])->withInput();
        }

        $etaMin = (int) $validated['duration_minutes'];

        return redirect()
            ->route('control.simulations.index', ['run' => $started['run_id']])
            ->with('message', "Simulación iniciada para {$tenant->name}. Duración estimada ~{$etaMin} min.")
            ->with('active_simulation_run_id', $started['run_id']);
    }

    public function status(SimulationRunModel $run): JsonResponse
    {
        Gate::authorize('platform.manage-users');

        $this->handoffProgressSync->syncRun($run);
        $this->staleGuard->failExpiredRuns();

        $payload = $this->metricsCollector->presentationForRun($run->fresh(['tenant']));

        return response()->json($payload);
    }

    public function report(SimulationRunModel $run): Response
    {
        Gate::authorize('platform.manage-users');

        $run->load('tenant');
        $payload = $this->metricsCollector->presentationForRun($run);

        if (! in_array($run->status, [
            SimulationRunModel::STATUS_COMPLETED,
            SimulationRunModel::STATUS_FAILED,
        ], true) || empty($payload['metrics'])) {
            abort(404, 'El reporte no está disponible para esta simulación.');
        }

        return Inertia::render('Control/Simulation/Report', $payload);
    }
}
