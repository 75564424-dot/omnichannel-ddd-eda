<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Control\Application\Services\SimulationDiagnosticsReader;
use App\Control\Application\Services\SimulationMessages;
use App\Control\Application\Services\SimulationProgressReporter;
use App\Control\Application\Services\SimulationRunControlPlaneClient;
use App\Control\Application\Services\SimulationRunHandoffStore;
use App\Control\Application\Services\TenantSimulationAutomationService;
use App\Middleware\Application\Services\SimulationPulseService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Throwable;

final class ExecuteSimulationRunOnInstanceCommand extends Command
{
    protected $signature = 'platform:simulation:execute-run {runId : Simulation run UUID on control plane}';

    protected $description = 'Execute a control-plane simulation run inside this client silo (local fleet).';

    public function handle(
        SimulationRunControlPlaneClient $controlPlane,
        SimulationProgressReporter $progressReporter,
        TenantSimulationAutomationService $automation,
        SimulationPulseService $simulationPulse,
        SimulationRunHandoffStore $handoffStore,
        SimulationDiagnosticsReader $diagnosticsReader,
    ): int {
        $runId = (string) $this->argument('runId');

        if (config('platform.control_plane', false)) {
            return $this->failEarly(
                $controlPlane,
                $handoffStore,
                $simulationPulse,
                $diagnosticsReader,
                $runId,
                SimulationMessages::WORKER_WRONG_HOST,
                false,
            );
        }

        $spec = $handoffStore->read($runId);
        $usedHandoff = $spec !== null;
        if (! $usedHandoff) {
            try {
                $spec = $controlPlane->fetchRun($runId);
            } catch (Throwable $e) {
                return $this->failEarly($controlPlane, $handoffStore, $simulationPulse, $diagnosticsReader, $runId, $e->getMessage(), false);
            }
        } else {
            $this->info("Using local handoff spec for run {$runId}.");
        }

        $tenantSlug = (string) ($spec['tenant_slug'] ?? '');
        $instanceSlug = (string) config('platform.client_slug', '');
        if ($tenantSlug === '') {
            return $this->failEarly($controlPlane, $handoffStore, $simulationPulse, $diagnosticsReader, $runId, 'Handoff sin tenant_slug.', $usedHandoff);
        }

        if (! $usedHandoff && ($instanceSlug === '' || $tenantSlug !== $instanceSlug)) {
            return $this->failEarly(
                $controlPlane,
                $handoffStore,
                $simulationPulse,
                $diagnosticsReader,
                $runId,
                "Tenant slug «{$tenantSlug}» no coincide con esta instancia «{$instanceSlug}».",
                false,
                $tenantSlug,
                $instanceSlug,
            );
        }

        $eventsPerMinute = (int) ($spec['events_per_minute'] ?? 0);
        $durationMinutes = max(1, (int) ($spec['duration_minutes'] ?? 1));
        $plannedTotal    = (int) ($spec['planned_total'] ?? 0);
        $prepareFirst    = (bool) ($spec['prepare_first'] ?? true);
        $catalog         = is_array($spec['modules_catalog'] ?? null) ? $spec['modules_catalog'] : [];
        $effectiveTotal  = $plannedTotal > 0 ? $plannedTotal : max(1, $eventsPerMinute * $durationMinutes);

        set_time_limit(max(600, $durationMinutes * 120 + 120));
        $handoffStore->updateProgress($runId, 0, $effectiveTotal, 'starting');

        try {
            $controlPlane->reportProgress($runId, 0, $effectiveTotal);

            $result = $automation->runOnClientSilo(
                tenantSlug: $tenantSlug,
                modulesCatalog: $catalog,
                eventsPerMinute: $eventsPerMinute,
                durationMinutes: $durationMinutes,
                totalEvents: $plannedTotal,
                skipPrepare: ! $prepareFirst,
                onProgress: $progressReporter->forRun($runId, $plannedTotal),
            );

            $completionPayload = [
                'published'     => $result['published'],
                'queue_matches' => $result['queue_matches'],
                'event_ids'     => $result['event_ids'],
            ];

            $handoffStore->markTerminal($runId, 'completed', $completionPayload);

            try {
                $controlPlane->markCompleted($runId, $completionPayload);
            } catch (ConnectionException $e) {
                $this->warn('Control plane no alcanzable; el estado final quedó en handoff: '.$e->getMessage());
            } catch (Throwable $e) {
                $this->warn('No se notificó al control plane por HTTP: '.$e->getMessage());
            }

            $this->info("Simulation {$runId} completed: {$result['published']} events published.");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $context = $this->failureContext($diagnosticsReader, $runId, $usedHandoff);
            $handoffStore->markTerminal($runId, 'failed', [
                'error_message' => $e->getMessage(),
                'context'       => $context,
            ]);
            $controlPlane->markFailed($runId, $e->getMessage(), $context);

            $this->error($e->getMessage());

            return self::FAILURE;
        } finally {
            $simulationPulse->clear();
        }
    }

    private function failEarly(
        SimulationRunControlPlaneClient $controlPlane,
        SimulationRunHandoffStore $handoffStore,
        SimulationPulseService $simulationPulse,
        SimulationDiagnosticsReader $diagnosticsReader,
        string $runId,
        string $message,
        bool $handoffUsed,
        ?string $expectedSlug = null,
        ?string $instanceSlug = null,
    ): int {
        $this->error($message);
        $context = $this->failureContext($diagnosticsReader, $runId, $handoffUsed, $expectedSlug, $instanceSlug);
        $handoffStore->markTerminal($runId, 'failed', [
            'error_message' => $message,
            'context'       => $context,
        ]);
        $controlPlane->markFailed($runId, $message, $context);
        $simulationPulse->clear();

        return self::FAILURE;
    }

    /**
     * @return array<string, mixed>
     */
    private function failureContext(
        SimulationDiagnosticsReader $diagnosticsReader,
        string $runId,
        bool $handoffUsed,
        ?string $expectedSlug = null,
        ?string $instanceSlug = null,
    ): array {
        return array_filter([
            'handoff_used'  => $handoffUsed,
            'expected_slug' => $expectedSlug,
            'instance_slug' => $instanceSlug ?? (string) config('platform.client_slug', ''),
            'worker_log'    => $diagnosticsReader->excerpt($runId),
        ], static fn ($v) => $v !== null && $v !== '');
    }
}
