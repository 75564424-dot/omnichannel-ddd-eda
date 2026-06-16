<?php

declare(strict_types=1);

namespace App\Simulation\Interfaces\Providers;

use App\Providers\Registrars\SimulationServiceBindingsRegistrar;
use App\Simulation\Application\Services\Execution\ClientSiloSimulationExecutor;
use App\Simulation\Application\Services\Execution\ExecuteSimulationRunOnInstanceService;
use App\Simulation\Application\Services\Execution\SimulationFixtureResolver;
use App\Simulation\Application\Services\Execution\SimulationTenantEligibilityChecker;
use App\Simulation\Application\Services\Execution\TenantSimulationAutomationService;
use App\Simulation\Application\Services\Handoff\SimulationRunHandoffStore;
use App\Simulation\Application\Services\Handoff\SimulationRunHandoffSync;
use App\Simulation\Application\Services\Metrics\SimulationMetricsBaselineCapture;
use App\Simulation\Application\Services\Metrics\SimulationQueueMetricsAnalyzer;
use App\Simulation\Application\Services\Metrics\SimulationRunMetricsCollector;
use App\Simulation\Application\Services\Metrics\SimulationRunReportBuilder;
use App\Simulation\Application\Services\Orchestration\LocalFleetSimulationRunner;
use App\Simulation\Application\Services\Orchestration\SimulationRunOrchestrator;
use App\Simulation\Application\Services\Orchestration\SimulationRunQueryService;
use App\Simulation\Application\Services\Orchestration\SimulationRunStaleGuard;
use App\Simulation\Application\Services\Orchestration\SimulationStaleRunReplacer;
use App\Simulation\Application\Services\Prepare\InstanceSimulationReadinessService;
use App\Simulation\Application\Services\Prepare\SimulationDiagnosticsReader;
use App\Simulation\Application\Services\Prepare\SimulationInstancePrepareService;
use App\Simulation\Application\Services\Prepare\SimulationTenantSettingsSync;
use App\Simulation\Application\Services\Progress\SimulationProgressReporter;
use App\Simulation\Application\Services\Progress\SimulationRunCompletionService;
use App\Simulation\Application\Services\Progress\SimulationRunControlPlaneClient;
use App\Simulation\Application\Services\Progress\SimulationRunFailureHandler;
use App\Simulation\Application\Services\Progress\SimulationRunInternalApiService;
use App\Simulation\Application\Services\Reset\SimulationRunsResetService;
use App\Simulation\Application\Services\Runtime\SimulationPublishScope;
use App\Simulation\Application\Services\Runtime\SimulationPulseService;
use App\Simulation\Application\Services\Runtime\SimulationQueueDrainer;
use App\Simulation\Application\Services\Worker\SimulationRunWorkerMonitor;
use App\Simulation\Application\Services\Worker\SimulationWorkerEnvironmentFactory;
use App\Simulation\Application\Services\Worker\SimulationWorkerLauncher;
use App\Simulation\Application\Services\Worker\SimulationWorkerTenantBootstrap;
use Illuminate\Support\ServiceProvider;

final class SimulationServiceProvider extends ServiceProvider
{
    /** @return list<class-string> */
    public static function singletonClasses(): array
    {
        return [
            SimulationPublishScope::class,
            SimulationPulseService::class,
            SimulationQueueDrainer::class,
            SimulationWorkerLauncher::class,
            SimulationWorkerEnvironmentFactory::class,
            SimulationWorkerTenantBootstrap::class,
            SimulationRunWorkerMonitor::class,
            SimulationDiagnosticsReader::class,
            SimulationTenantSettingsSync::class,
            InstanceSimulationReadinessService::class,
            SimulationInstancePrepareService::class,
            SimulationFixtureResolver::class,
            SimulationTenantEligibilityChecker::class,
            ClientSiloSimulationExecutor::class,
            TenantSimulationAutomationService::class,
            SimulationMetricsBaselineCapture::class,
            SimulationQueueMetricsAnalyzer::class,
            SimulationRunReportBuilder::class,
            SimulationRunMetricsCollector::class,
            SimulationRunHandoffStore::class,
            SimulationRunHandoffSync::class,
            SimulationRunStaleGuard::class,
            SimulationStaleRunReplacer::class,
            SimulationRunCancellationService::class,
            LocalFleetSimulationRunner::class,
            SimulationRunOrchestrator::class,
            SimulationRunQueryService::class,
            SimulationProgressReporter::class,
            SimulationRunControlPlaneClient::class,
            SimulationRunInternalApiService::class,
            SimulationRunCompletionService::class,
            SimulationRunFailureHandler::class,
            ExecuteSimulationRunOnInstanceService::class,
            SimulationRunsResetService::class,
        ];
    }

    public function register(): void
    {
        SimulationServiceBindingsRegistrar::register($this->app);

        foreach (self::singletonClasses() as $class) {
            $this->app->singleton($class);
        }
    }
}
