<?php

declare(strict_types=1);

namespace App\Monitoring\Interfaces\Providers;

use App\Monitoring\Application\Services\AlertEvaluationService;
use App\Monitoring\Application\Services\CanaryPublishService;
use App\Monitoring\Application\Services\DatabaseCapacityChecker;
use App\Monitoring\Application\Services\QueueDepthChecker;
use Illuminate\Support\ServiceProvider;

final class MonitoringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__, 4).'/config/platform_monitoring.php',
            'platform_monitoring',
        );

        $this->app->singleton(DatabaseCapacityChecker::class);
        $this->app->singleton(QueueDepthChecker::class);
        $this->app->singleton(AlertEvaluationService::class);
        $this->app->singleton(CanaryPublishService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Monitoring\Interfaces\Commands\EvaluateMonitoringAlertsCommand::class,
                \App\Monitoring\Interfaces\Commands\CanaryPublishCommand::class,
            ]);
        }
    }
}
