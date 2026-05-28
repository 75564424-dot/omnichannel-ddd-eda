<?php

declare(strict_types=1);

namespace App\Shared\Api\Interfaces\Providers;

use App\Shared\Api\Application\Services\IdempotencyKeyStore;
use App\Shared\Api\Http\Middleware\AppendRateLimitHeadersMiddleware;
use App\Shared\Api\Routes\DashboardApiRoutes;
use App\Shared\Api\Routes\IntegrationApiRoutes;
use App\Shared\Api\Routes\MiddlewareApiRoutes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__, 5).'/config/platform_api.php',
            'platform_api',
        );

        $this->app->singleton(IdempotencyKeyStore::class);
    }

    public function boot(): void
    {
        Route::middleware('api')->group(function (): void {
            MiddlewareApiRoutes::register('api/v1/middleware');
            DashboardApiRoutes::register('api/v1/dashboard');
            IntegrationApiRoutes::register('api/v1/integrations');
        });
    }

    public static function middlewareAliases(): array
    {
        return [
            'platform.api.rate_headers' => AppendRateLimitHeadersMiddleware::class,
        ];
    }
}
