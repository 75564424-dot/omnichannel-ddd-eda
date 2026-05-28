<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Middleware\ShareCorrelationLogContext;
use App\Middleware\Application\Services\EventLogService;
use App\Shared\Logging\PlatformStructuredLogger;
use App\Shared\Logging\Services\AuditLogService;
use Illuminate\Support\ServiceProvider;

final class LoggingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__, 2).'/config/platform_logging.php',
            'platform_logging',
        );

        $this->app->singleton(PlatformStructuredLogger::class);
        $this->app->singleton(AuditLogService::class);
        $this->app->singleton(EventLogService::class);
    }

    public function boot(): void
    {
        //
    }

    public static function middlewareAliases(): array
    {
        return [
            'platform.log.context' => CorrelationIdMiddleware::class,
            'platform.correlation.id' => CorrelationIdMiddleware::class,
        ];
    }
}
