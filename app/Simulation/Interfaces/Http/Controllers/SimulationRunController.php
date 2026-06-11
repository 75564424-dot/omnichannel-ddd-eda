<?php

declare(strict_types=1);

namespace App\Simulation\Interfaces\Http\Controllers;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Simulation\Application\Services\Orchestration\SimulationRunQueryService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class SimulationRunController
{
    public function __construct(
        private readonly SimulationRunQueryService $runs,
        private readonly Gate $gate,
    ) {}

    public function index(Request $request): Response
    {
        $this->gate->authorize('platform.manage-users');

        $this->runs->syncActiveRuns();

        $tenantId = $request->string('tenant_id')->toString();
        $tenantId = $tenantId !== '' ? $tenantId : null;

        return Inertia::render('Control/Simulation/Index', [
            'runs' => $this->runs->paginateRuns($tenantId)->withQueryString(),
            'filters' => ['tenant_id' => $tenantId],
            'tenants' => $this->runs->tenantFilterOptions(),
            'highlight_run_id' => $request->string('run')->toString() ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->gate->authorize('platform.manage-users');

        $validated = $request->validate([
            'tenant_id' => ['required', 'uuid', 'exists:tenants,id'],
            'events_per_minute' => ['required', 'integer', 'min:1', 'max:600'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'total_events' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'prepare_first' => ['sometimes', 'boolean'],
        ]);

        try {
            $started = $this->runs->startRun($validated, $this->runs->resolveUserId($request->user()));
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['simulation' => $e->getMessage()])->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors(['simulation' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('control.simulations.index', ['run' => $started['run_id']])
            ->with('message', "Simulación iniciada para {$started['tenant_name']}. Duración estimada ~{$started['eta_minutes']} min.")
            ->with('active_simulation_run_id', $started['run_id']);
    }

    public function status(SimulationRunModel $run): JsonResponse
    {
        $this->gate->authorize('platform.manage-users');

        return response()->json($this->runs->statusPayload($run));
    }

    public function cancel(Request $request, SimulationRunModel $run): JsonResponse
    {
        $this->gate->authorize('platform.manage-users');

        try {
            $payload = $this->runs->cancelRun($run, $this->runs->resolveUserId($request->user()));
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($payload);
    }

    public function report(SimulationRunModel $run): Response
    {
        $this->gate->authorize('platform.manage-users');

        return Inertia::render('Control/Simulation/Report', $this->runs->reportPayload($run));
    }
}
