<?php

declare(strict_types=1);

namespace App\Quality\Interfaces\Providers;

use App\Quality\Application\Services\Coverage\ApplicationCoverageCalculator;
use App\Quality\Application\Services\Coverage\ApplicationCoverageGateService;
use App\Quality\Application\Services\QualityCoverageConsoleReporter;
use App\Quality\Application\Services\QualitySettings;
use Illuminate\Support\ServiceProvider;

final class QualityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__, 4).'/config/platform_quality.php',
            'platform_quality',
        );

        $this->app->singleton(QualitySettings::class, fn () => QualitySettings::fromConfig());
        $this->app->singleton(ApplicationCoverageCalculator::class);
        $this->app->singleton(ApplicationCoverageGateService::class);
        $this->app->singleton(QualityCoverageConsoleReporter::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Quality\Interfaces\Commands\CheckApplicationCoverageCommand::class,
            ]);
        }
    }
}
