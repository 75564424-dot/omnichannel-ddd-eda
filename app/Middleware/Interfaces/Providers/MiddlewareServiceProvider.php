<?php

declare(strict_types=1);

namespace App\Middleware\Interfaces\Providers;

use App\Middleware\Application\Services\BusHealthService;
use App\Middleware\Application\Services\BusMetricsService;
use App\Middleware\Application\Services\EventLogProjector;
use App\Middleware\Application\Services\EventProcessingService;
use App\Middleware\Application\Services\EventPublisherService;
use App\Middleware\Application\Services\EventSchemaRegistry;
use App\Middleware\Application\Services\SubscriptionRegistryService;
use App\Middleware\Application\Services\WorkflowEngine;
use App\Middleware\Application\UseCases\GetBusMetricsUseCase;
use App\Middleware\Application\UseCases\GetBusStatusUseCase;
use App\Middleware\Application\UseCases\GetDeadLetterQueueUseCase;
use App\Middleware\Application\UseCases\GetEventQueueUseCase;
use App\Middleware\Application\UseCases\GetTopologySnapshotUseCase;
use App\Middleware\Application\UseCases\RequeueDeadLetterUseCase;
use App\Middleware\Application\UseCases\SearchEventByIdUseCase;
use App\Middleware\Application\UseCases\SyncConfiguredModulesToRegistryUseCase;
use App\Middleware\Domain\ModuleRegistry;
use App\Middleware\Domain\Repositories\BusMetricsRepositoryInterface;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;
use App\Middleware\Domain\Repositories\EventLogRepositoryInterface;
use App\Middleware\Domain\Repositories\EventStoreRepositoryInterface;
use App\Middleware\Domain\Repositories\OutboxRepositoryInterface;
use App\Middleware\Domain\Repositories\ProcessingJobRepositoryInterface;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use App\Middleware\Domain\Repositories\RetryAttemptRepositoryInterface;
use App\Middleware\Domain\Repositories\WorkflowRepositoryInterface;
use App\Middleware\Domain\TopologyService;
use App\Middleware\Infrastructure\EventBus\KafkaEventBusAdapter;
use App\Middleware\Infrastructure\EventBus\LaravelEventBusAdapter;
use App\Middleware\Infrastructure\Persistence\DatabaseModuleRegistry;
use App\Middleware\Infrastructure\Persistence\EloquentBusMetricsRepository;
use App\Middleware\Infrastructure\Persistence\EloquentDeadLetterRepository;
use App\Middleware\Infrastructure\Persistence\EloquentEventLogRepository;
use App\Middleware\Infrastructure\Persistence\EloquentEventStoreRepository;
use App\Middleware\Infrastructure\Persistence\EloquentOutboxRepository;
use App\Middleware\Infrastructure\Persistence\EloquentProcessingJobRepository;
use App\Middleware\Infrastructure\Persistence\EloquentQueueEntryRepository;
use App\Middleware\Infrastructure\Persistence\EloquentRetryAttemptRepository;
use App\Middleware\Infrastructure\Persistence\EloquentWorkflowRepository;
use App\Middleware\Infrastructure\Resilience\ConnectorCircuitBreaker;
use App\Middleware\Listeners\BusTrackingListener;
use App\Middleware\Listeners\ModuleObservationListener;
use App\Middleware\Listeners\ProcessEventJobFailureListener;
use App\Shared\Contracts\EventBus\EventBusPort;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MiddlewareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerEventBus();
        $this->registerProcessingServices();
        $this->registerDomainServices();
        $this->registerApplicationServices();
        $this->registerUseCases();
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->registerBusTrackingListeners();
        $this->registerProcessEventJobFailureListener();
    }

    private function registerRepositories(): void
    {
        $this->app->bind(
            QueueEntryRepositoryInterface::class,
            EloquentQueueEntryRepository::class,
        );

        $this->app->bind(
            DeadLetterRepositoryInterface::class,
            EloquentDeadLetterRepository::class,
        );

        $this->app->bind(
            RetryAttemptRepositoryInterface::class,
            EloquentRetryAttemptRepository::class,
        );

        $this->app->bind(
            EventStoreRepositoryInterface::class,
            EloquentEventStoreRepository::class,
        );

        $this->app->bind(
            EventLogRepositoryInterface::class,
            EloquentEventLogRepository::class,
        );

        $this->app->bind(
            OutboxRepositoryInterface::class,
            EloquentOutboxRepository::class,
        );

        $this->app->bind(
            WorkflowRepositoryInterface::class,
            EloquentWorkflowRepository::class,
        );

        $this->app->bind(
            ProcessingJobRepositoryInterface::class,
            EloquentProcessingJobRepository::class,
        );
    }

    private function registerEventBus(): void
    {
        $this->app->bind(EventBusPort::class, function () {
            if (config('eventbus.driver') === 'kafka') {
                return $this->app->make(KafkaEventBusAdapter::class);
            }

            return $this->app->make(LaravelEventBusAdapter::class);
        });

        $this->app->singleton(ConnectorCircuitBreaker::class);
    }

    private function registerProcessingServices(): void
    {
        $this->app->singleton(EventSchemaRegistry::class);
        $this->app->singleton(EventLogProjector::class);
        $this->app->singleton(EventLogService::class);
        $this->app->singleton(WorkflowEngine::class);
        $this->app->singleton(EventProcessingService::class);
        $this->app->singleton(\App\Middleware\Application\Services\Processing\EventProcessingAttemptExecutor::class);
        $this->app->singleton(\App\Middleware\Application\Services\Processing\EventProcessingDispatchPlanner::class);
        $this->app->singleton(\App\Middleware\Application\Services\Processing\EventDeadLetterFinalizer::class);
    }

    private function registerDomainServices(): void
    {
        $this->app->bind(
            BusMetricsRepositoryInterface::class,
            EloquentBusMetricsRepository::class,
        );

        $this->app->bind(
            ModuleRegistry::class,
            DatabaseModuleRegistry::class,
        );

        $this->app->singleton(TopologyService::class);
    }

    private function registerApplicationServices(): void
    {
        $this->app->singleton(SubscriptionRegistryService::class);
        $this->app->singleton(BusMetricsService::class);
        $this->app->singleton(BusHealthService::class);
        $this->app->singleton(EventPublisherService::class);
    }

    private function registerUseCases(): void
    {
        $this->app->bind(GetBusMetricsUseCase::class);
        $this->app->bind(GetEventQueueUseCase::class);
        $this->app->bind(GetTopologySnapshotUseCase::class);
        $this->app->bind(SearchEventByIdUseCase::class);
        $this->app->bind(GetBusStatusUseCase::class);
        $this->app->bind(GetDeadLetterQueueUseCase::class);
        $this->app->bind(RequeueDeadLetterUseCase::class);
        $this->app->bind(SyncConfiguredModulesToRegistryUseCase::class);
    }

    private function loadRoutes(): void
    {
        Route::middleware('api')->group(
            __DIR__ . '/../Routes/api.php'
        );
    }

    /**
     * Registers BusTrackingListener and ModuleObservationListener for every known event type.
     * This is the "recording layer" — the listener observes all events in transit
     * and logs them to bus_queue_entries WITHOUT interfering with business processing.
     */
    private function registerBusTrackingListeners(): void
    {
        $events = $this->app->make(Dispatcher::class);
        $events->listen('*', BusTrackingListener::class);
        $events->listen('*', ModuleObservationListener::class);
    }

    private function registerProcessEventJobFailureListener(): void
    {
        $events = $this->app->make(Dispatcher::class);
        $events->listen(JobFailed::class, ProcessEventJobFailureListener::class);
    }
}
