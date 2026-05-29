<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Execution;


use App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore;
use App\Simulation\Application\Services\Prepare\SimulationDiagnosticsReader;
use App\Simulation\Application\Services\Progress\SimulationProgressReporter;
use App\Simulation\Application\Services\Progress\SimulationRunControlPlaneClient;
use App\Simulation\Application\Services\Runtime\SimulationPulseService;
use App\Simulation\Application\Services\Worker\SimulationWorkerTenantBootstrap;
use App\Simulation\Domain\ValueObjects\SimulationMessages;
use App\Simulation\Application\DTOs\SimulationRunExecutionResult;
use Illuminate\Http\Client\ConnectionException;
use Throwable;

final class ExecuteSimulationRunOnInstanceService
{
    public function __construct(
        private readonly SimulationRunControlPlaneClient $controlPlane,
        private readonly SimulationProgressReporter $progressReporter,
        private readonly TenantSimulationAutomationService $automation,
        private readonly SimulationPulseService $simulationPulse,
        private readonly SimulationRunHandoffStore $handoffStore,
        private readonly SimulationDiagnosticsReader $diagnosticsReader,
        private readonly SimulationWorkerTenantBootstrap $tenantBootstrap,
    ) {}

    public function execute(string $runId): SimulationRunExecutionResult
    {
        if (config('platform.control_plane', false)) {
            return $this->failEarly($runId, SimulationMessages::WORKER_WRONG_HOST, false);
        }

        $spec = $this->handoffStore->read($runId);
        $usedHandoff = $spec !== null;
        if (! $usedHandoff) {
            try {
                $spec = $this->controlPlane->fetchRun($runId);
            } catch (Throwable $e) {
                return $this->failEarly($runId, $e->getMessage(), false);
            }
        }

        $tenantSlug = (string) ($spec['tenant_slug'] ?? '');
        $instanceSlug = (string) config('platform.client_slug', '');
        $this->tenantBootstrap->bindForTenantSlug($tenantSlug);

        if ($tenantSlug === '') {
            return $this->failEarly($runId, 'Handoff sin tenant_slug.', $usedHandoff);
        }

        if (! $usedHandoff && ($instanceSlug === '' || $tenantSlug !== $instanceSlug)) {
            return $this->failEarly(
                $runId,
                "Tenant slug «{$tenantSlug}» no coincide con esta instancia «{$instanceSlug}».",
                false,
                $tenantSlug,
                $instanceSlug,
            );
        }

        $eventsPerMinute = (int) ($spec['events_per_minute'] ?? 0);
        $durationMinutes = max(1, (int) ($spec['duration_minutes'] ?? 1));
        $plannedTotal = (int) ($spec['planned_total'] ?? 0);
        $prepareFirst = (bool) ($spec['prepare_first'] ?? true);
        $catalog = is_array($spec['modules_catalog'] ?? null) ? $spec['modules_catalog'] : [];
        $effectiveTotal = $plannedTotal > 0 ? $plannedTotal : max(1, $eventsPerMinute * $durationMinutes);

        set_time_limit(max(600, $durationMinutes * 120 + 120));
        $this->handoffStore->updateProgress($runId, 0, $effectiveTotal, 'starting');

        try {
            $this->controlPlane->reportProgress($runId, 0, $effectiveTotal);

            $result = $this->automation->runOnClientSilo(
                tenantSlug: $tenantSlug,
                modulesCatalog: $catalog,
                eventsPerMinute: $eventsPerMinute,
                durationMinutes: $durationMinutes,
                totalEvents: $plannedTotal,
                skipPrepare: ! $prepareFirst,
                onProgress: $this->progressReporter->forRun($runId, $plannedTotal),
            );

            $published = (int) $result['published'];
            if ($published < $effectiveTotal) {
                throw new \RuntimeException(
                    "Solo se publicaron {$published} de {$effectiveTotal} eventos planificados.",
                );
            }

            $completionPayload = [
                'published' => $published,
                'queue_matches' => $result['queue_matches'],
                'event_ids' => $result['event_ids'],
            ];

            $this->handoffStore->markTerminal($runId, 'completed', $completionPayload);

            $warning = null;
            try {
                $this->controlPlane->markCompleted($runId, $completionPayload);
            } catch (ConnectionException $e) {
                $warning = 'Control plane no alcanzable; el estado final quedó en handoff: '.$e->getMessage();
            } catch (Throwable $e) {
                $warning = 'No se notificó al control plane por HTTP: '.$e->getMessage();
            }

            return $warning !== null
                ? SimulationRunExecutionResult::completedWithWarning($runId, $published, $warning)
                : SimulationRunExecutionResult::completed($runId, $published);
        } catch (Throwable $e) {
            $context = $this->failureContext($runId, $usedHandoff);
            $this->handoffStore->markTerminal($runId, 'failed', [
                'error_message' => $e->getMessage(),
                'context' => $context,
            ]);
            $this->controlPlane->markFailed($runId, $e->getMessage(), $context);

            return SimulationRunExecutionResult::failed($e->getMessage());
        } finally {
            $this->simulationPulse->clear();
        }
    }

    private function failEarly(
        string $runId,
        string $message,
        bool $handoffUsed,
        ?string $expectedSlug = null,
        ?string $instanceSlug = null,
    ): SimulationRunExecutionResult {
        $context = $this->failureContext($runId, $handoffUsed, $expectedSlug, $instanceSlug);
        $this->handoffStore->markTerminal($runId, 'failed', [
            'error_message' => $message,
            'context' => $context,
        ]);
        $this->controlPlane->markFailed($runId, $message, $context);
        $this->simulationPulse->clear();

        return SimulationRunExecutionResult::failed($message);
    }

    /**
     * @return array<string, mixed>
     */
    private function failureContext(
        string $runId,
        bool $handoffUsed,
        ?string $expectedSlug = null,
        ?string $instanceSlug = null,
    ): array {
        return array_filter([
            'handoff_used' => $handoffUsed,
            'expected_slug' => $expectedSlug,
            'instance_slug' => $instanceSlug ?? (string) config('platform.client_slug', ''),
            'worker_log' => $this->diagnosticsReader->excerpt($runId),
        ], static fn ($v) => $v !== null && $v !== '');
    }
}
