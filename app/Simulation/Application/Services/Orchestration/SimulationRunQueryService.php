<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Orchestration;


use App\Simulation\Application\Services\Handoff\SimulationRunHandoffSync;
use App\Simulation\Application\Services\Metrics\SimulationRunMetricsCollector;
use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class SimulationRunQueryService
{
    public function __construct(
        private readonly SimulationRunHandoffSync $handoffSync,
        private readonly SimulationRunStaleGuard $staleGuard,
        private readonly SimulationRunMetricsCollector $metricsCollector,
        private readonly SimulationRunOrchestrator $orchestrator,
    ) {}

    public function syncActiveRuns(): void
    {
        $this->handoffSync->syncActiveRuns();
        $this->staleGuard->failExpiredRuns();
    }

    /** @return LengthAwarePaginator<int, SimulationRunModel> */
    public function paginateRuns(?string $tenantId, int $perPage = 25): LengthAwarePaginator
    {
        $query = SimulationRunModel::query()
            ->with('tenant')
            ->orderByDesc('created_at');

        if ($tenantId !== null && $tenantId !== '') {
            $query->where('tenant_id', $tenantId);
        }

        return $query
            ->paginate($perPage)
            ->through(fn (SimulationRunModel $run) => $this->metricsCollector->presentationForListItem($run));
    }

    /** @return list<array{id: string, name: string, slug: string}> */
    public function tenantFilterOptions(): array
    {
        return TenantModel::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (TenantModel $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
            ])
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $validated
     *
     * @return array{run_id: string, tenant_name: string, eta_minutes: int}
     */
    public function startRun(array $validated, ?int $userId): array
    {
        $tenant = TenantModel::query()->findOrFail($validated['tenant_id']);
        $planned = (int) $validated['events_per_minute'] * (int) $validated['duration_minutes'];

        if (isset($validated['total_events']) && (int) $validated['total_events'] !== $planned) {
            throw new \InvalidArgumentException("Total de eventos debe ser {$planned} (eventos/min × minutos).");
        }

        $started = $this->orchestrator->start(
            tenant: $tenant,
            eventsPerMinute: (int) $validated['events_per_minute'],
            durationMinutes: (int) $validated['duration_minutes'],
            prepareFirst: (bool) ($validated['prepare_first'] ?? true),
            userId: $userId,
        );

        return [
            'run_id' => $started['run_id'],
            'tenant_name' => (string) $tenant->name,
            'eta_minutes' => (int) $validated['duration_minutes'],
        ];
    }

    public function resolveUserId(?User $user): ?int
    {
        return $user instanceof User ? (int) $user->getKey() : null;
    }

    /** @return array<string, mixed> */
    public function statusPayload(SimulationRunModel $run): array
    {
        $run = $this->handoffSync->syncRun($run);
        $run = $this->staleGuard->failRunIfExpired($run) ?? $run->fresh(['tenant']);

        return $this->metricsCollector->presentationForRun($run);
    }

    /** @return array<string, mixed> */
    public function cancelRun(SimulationRunModel $run, ?int $userId): array
    {
        $cancelled = $this->orchestrator->cancel($run, $userId);

        return $this->metricsCollector->presentationForRun($cancelled);
    }

    /** @return array<string, mixed> */
    public function reportPayload(SimulationRunModel $run): array
    {
        $run->load('tenant');
        $payload = $this->metricsCollector->presentationForRun($run);

        if (! in_array($run->status, [
            SimulationRunModel::STATUS_COMPLETED,
            SimulationRunModel::STATUS_FAILED,
        ], true) || empty($payload['metrics'])) {
            abort(404, 'El reporte no está disponible para esta simulación.');
        }

        return $payload;
    }
}
