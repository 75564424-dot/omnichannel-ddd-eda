<?php

declare(strict_types=1);

namespace App\Observability\Interfaces\Providers;

use App\Observability\Application\Services\Prometheus\FeedProjectionLagCalculator;
use App\Observability\Application\Services\Prometheus\PrometheusGaugeCollector;
use App\Observability\Application\Services\Prometheus\PrometheusTextRenderer;
use App\Observability\Application\Services\PrometheusMetricsExporter;
use App\Observability\Application\Services\SliMetricsRecorder;
use App\Observability\Application\Services\StreamConnectionTracker;
use App\Observability\Application\Services\TraceSpanService;
use App\Observability\Domain\Repositories\TraceLogRepositoryInterface;
use App\Observability\Infrastructure\Persistence\EloquentTraceLogRepository;
use App\Observability\Interfaces\Http\Controllers\PrometheusMetricsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class ObservabilityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__, 4).'/config/platform_observability.php',
            'platform_observability',
        );

        $this->mergeConfigFrom(
            dirname(__DIR__, 4).'/config/platform_slos.php',
            'platform_slos',
        );

        $this->app->singleton(TraceLogRepositoryInterface::class, EloquentTraceLogRepository::class);
        $this->app->singleton(TraceSpanService::class);
        $this->app->singleton(SliMetricsRecorder::class);
        $this->app->singleton(StreamConnectionTracker::class);
        $this->app->singleton(FeedProjectionLagCalculator::class);
        $this->app->singleton(PrometheusGaugeCollector::class);
        $this->app->singleton(PrometheusTextRenderer::class);
        $this->app->singleton(PrometheusMetricsExporter::class);
    }

    public function boot(): void
    {
        Route::get('/metrics', PrometheusMetricsController::class)
            ->name('observability.prometheus.metrics');
    }

    public static function middlewareAliases(): array
    {
        return [
            'platform.correlation.id' => \App\Http\Middleware\CorrelationIdMiddleware::class,
        ];
    }
}
