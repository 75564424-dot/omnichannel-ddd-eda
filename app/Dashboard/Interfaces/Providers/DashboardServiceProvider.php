<?php

declare(strict_types=1);

namespace App\Dashboard\Interfaces\Providers;

use App\Dashboard\Application\Adapters\ControlClientDashboardMetricsAdapter;
use App\Dashboard\Application\Contracts\ClientDashboardMetricsPortInterface;
use App\Dashboard\Application\Contracts\ModulesCatalogDataProviderInterface;
use App\Dashboard\Application\Services\DynamicMetricSeriesBuilder;
use App\Dashboard\Application\UseCases\GetConfiguredDailySeriesUseCase;
use App\Dashboard\Application\UseCases\GetDashboardMetricCatalogUseCase;
use App\Dashboard\Application\UseCases\GetDynamicMetricSeriesUseCase;
use App\Dashboard\Application\UseCases\GetEventFlowDiagramDataUseCase;
use App\Dashboard\Application\UseCases\GetGlobalMetricsUseCase;
use App\Dashboard\Application\UseCases\GetMiddlewareBusMetricsUseCase;
use App\Dashboard\Application\UseCases\GetModulesCatalogUseCase;
use App\Dashboard\Application\UseCases\GetRecentEventFeedUseCase;
use App\Dashboard\Application\UseCases\GetSystemNodeStatusUseCase;
use App\Dashboard\Application\UseCases\RefreshSystemNodeUseCase;
use App\Dashboard\Application\UseCases\SetNodeMiddlewareEventsUseCase;
use App\Dashboard\Application\UseCases\StreamLiveEventsUseCase;
use App\Dashboard\Domain\DashboardKnownNodes;
use App\Dashboard\Domain\Repositories\BusQueueAnalyticsRepositoryInterface;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use App\Dashboard\Domain\Repositories\MetricsRepositoryInterface;
use App\Dashboard\Domain\Repositories\MiddlewareBusMetricsRepositoryInterface;
use App\Dashboard\Domain\Repositories\NodeStatusRepositoryInterface;
use App\Dashboard\Infrastructure\Modules\TenantAwareModulesCatalogDataProvider;
use App\Dashboard\Infrastructure\Persistence\DbBusQueueAnalyticsRepository;
use App\Dashboard\Infrastructure\Persistence\EloquentEventFeedRepository;
use App\Dashboard\Infrastructure\Persistence\EloquentMetricsRepository;
use App\Dashboard\Infrastructure\Persistence\EloquentMiddlewareBusMetricsRepository;
use App\Dashboard\Infrastructure\Persistence\EloquentNodeStatusRepository;
use App\Dashboard\Infrastructure\Projectors\EventFeedProjector;
use App\Dashboard\Listeners\MiddlewareMetricsListener;
use App\Dashboard\Listeners\UniversalDashboardFeedListener;
use App\Shared\Contracts\ControlPlane\NodeIngestionGateReaderInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

final class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientDashboardMetricsPortInterface::class, ControlClientDashboardMetricsAdapter::class);

        $this->app->bind(ModulesCatalogDataProviderInterface::class, TenantAwareModulesCatalogDataProvider::class);

        $this->app->bind(BusQueueAnalyticsRepositoryInterface::class, DbBusQueueAnalyticsRepository::class);
        $this->app->bind(EventFeedRepositoryInterface::class, EloquentEventFeedRepository::class);
        $this->app->bind(MetricsRepositoryInterface::class, EloquentMetricsRepository::class);
        $this->app->bind(NodeStatusRepositoryInterface::class, EloquentNodeStatusRepository::class);
        $this->app->bind(MiddlewareBusMetricsRepositoryInterface::class, EloquentMiddlewareBusMetricsRepository::class);

        $this->app->bind(NodeIngestionGateReaderInterface::class, fn ($app) => $app->make(NodeStatusRepositoryInterface::class));

        $this->app->singleton(EventFeedProjector::class);

        $this->registerUseCases();
    }

    public function boot(): void
    {
        $events = $this->app->make(Dispatcher::class);
        $events->listen('*', UniversalDashboardFeedListener::class);
        $events->listen('*', MiddlewareMetricsListener::class);

        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }

    private function registerUseCases(): void
    {
        $this->app->bind(
            GetRecentEventFeedUseCase::class,
            fn ($app) => new GetRecentEventFeedUseCase($app->make(EventFeedRepositoryInterface::class)),
        );

        $this->app->bind(
            GetGlobalMetricsUseCase::class,
            fn ($app) => new GetGlobalMetricsUseCase(
                $app->make(EventFeedRepositoryInterface::class),
                $app->make(BusQueueAnalyticsRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            GetDynamicMetricSeriesUseCase::class,
            fn ($app) => new GetDynamicMetricSeriesUseCase(
                $app->make(ClientDashboardMetricsPortInterface::class),
                $app->make(DynamicMetricSeriesBuilder::class),
            ),
        );

        $this->app->bind(
            GetDashboardMetricCatalogUseCase::class,
            fn ($app) => new GetDashboardMetricCatalogUseCase(
                $app->make(ClientDashboardMetricsPortInterface::class),
            ),
        );

        $this->app->bind(
            GetSystemNodeStatusUseCase::class,
            fn ($app) => new GetSystemNodeStatusUseCase(
                $app->make(NodeStatusRepositoryInterface::class),
                $app->make(EventFeedRepositoryInterface::class),
                $app->make(DashboardKnownNodes::class),
            ),
        );

        $this->app->bind(
            RefreshSystemNodeUseCase::class,
            fn ($app) => new RefreshSystemNodeUseCase(
                $app->make(NodeStatusRepositoryInterface::class),
                $app->make(DashboardKnownNodes::class),
                $app->make(Dispatcher::class),
            ),
        );

        $this->app->bind(
            SetNodeMiddlewareEventsUseCase::class,
            fn ($app) => new SetNodeMiddlewareEventsUseCase(
                $app->make(NodeStatusRepositoryInterface::class),
                $app->make(DashboardKnownNodes::class),
            ),
        );

        $this->app->bind(
            StreamLiveEventsUseCase::class,
            fn ($app) => new StreamLiveEventsUseCase(
                $app->make(EventFeedRepositoryInterface::class),
                $app->make(\App\Observability\Application\Services\StreamConnectionTracker::class),
            ),
        );

        $this->app->bind(
            GetMiddlewareBusMetricsUseCase::class,
            fn ($app) => new GetMiddlewareBusMetricsUseCase(
                $app->make(MiddlewareBusMetricsRepositoryInterface::class),
                $app->make(EventFeedRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            GetEventFlowDiagramDataUseCase::class,
            fn ($app) => new GetEventFlowDiagramDataUseCase(
                $app->make(EventFeedRepositoryInterface::class),
                $app->make(NodeStatusRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            GetConfiguredDailySeriesUseCase::class,
            fn ($app) => new GetConfiguredDailySeriesUseCase(
                $app->make(EventFeedRepositoryInterface::class),
            ),
        );

        $this->app->bind(
            GetModulesCatalogUseCase::class,
            fn ($app) => new GetModulesCatalogUseCase(
                $app->make(ModulesCatalogDataProviderInterface::class),
            ),
        );
    }
}
