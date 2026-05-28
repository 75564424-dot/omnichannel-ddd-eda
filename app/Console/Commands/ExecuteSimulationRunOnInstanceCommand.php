<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Control\Application\Services\SimulationProgressReporter;
use App\Control\Application\Services\SimulationRunControlPlaneClient;
use App\Control\Application\Services\SimulationRunHandoffStore;
use App\Control\Application\Services\TenantSimulationAutomationService;
use App\Middleware\Application\Services\SimulationPulseService;
use Illuminate\Console\Command;
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
    ): int {
        $runId = (string) $this->argument('runId');

        if (config('platform.control_plane', false)) {
            $message = 'El worker de simulación arrancó en el control plane en lugar del silo cliente '
                .'(revise APP_ENV / PLATFORM_CONTROL_PLANE del subproceso).';
            $this->error($message);
            $controlPlane->markFailed($runId, $message, $this->failureContext($runId, false));
            $handoffStore->forget($runId);

            return self::FAILURE;
        }

        $spec = $handoffStore->read($runId);
        $usedHandoff = $spec !== null;
        if (! $usedHandoff) {
            try {
                $spec = $controlPlane->fetchRun($runId);
            } catch (Throwable $e) {
                $this->error($e->getMessage());
                $simulationPulse->clear();
                $controlPlane->markFailed($runId, $e->getMessage(), $this->failureContext($runId, false));
                $handoffStore->forget($runId);

                return self::FAILURE;
            }
        } else {
            $this->info("Using local handoff spec for run {$runId}.");
        }

        $tenantSlug = (string) ($spec['tenant_slug'] ?? '');
        $instanceSlug = (string) config('platform.client_slug', '');
        if ($tenantSlug === '') {
            $message = 'Handoff sin tenant_slug.';
            $controlPlane->markFailed($runId, $message, $this->failureContext($runId, $usedHandoff));
            $this->error($message);

            return self::FAILURE;
        }

        if (! $usedHandoff && ($instanceSlug === '' || $tenantSlug !== $instanceSlug)) {
            $message = "Tenant slug «{$tenantSlug}» no coincide con esta instancia «{$instanceSlug}».";
            $controlPlane->markFailed($runId, $message, $this->failureContext($runId, false, $tenantSlug, $instanceSlug));
            $this->error($message);

            return self::FAILURE;
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

            $controlPlane->markCompleted($runId, [
                'published'     => $result['published'],
                'queue_matches' => $result['queue_matches'],
                'event_ids'     => $result['event_ids'],
            ]);

            $this->info("Simulation {$runId} completed: {$result['published']} events published.");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $controlPlane->markFailed($runId, $e->getMessage(), $this->failureContext($runId, $usedHandoff));
            $this->error($e->getMessage());

            return self::FAILURE;
        } finally {
            $simulationPulse->clear();
            $handoffStore->forget($runId);
        }
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
        $logPath = storage_path('logs/simulation-worker-'.$runId.'.log');
        $log = is_file($logPath) ? (string) file_get_contents($logPath) : '';

        return array_filter([
            'handoff_used'  => $handoffUsed,
            'expected_slug' => $expectedSlug,
            'instance_slug' => $instanceSlug ?? (string) config('platform.client_slug', ''),
            'worker_log'    => $log !== '' ? $log : null,
        ], static fn ($v) => $v !== null && $v !== '');
    }
}
