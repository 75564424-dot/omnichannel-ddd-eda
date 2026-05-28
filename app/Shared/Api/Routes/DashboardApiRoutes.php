<?php

declare(strict_types=1);

namespace App\Shared\Api\Routes;

use App\Dashboard\Interfaces\Http\Controllers\DashboardController;
use App\Dashboard\Interfaces\Http\Controllers\EventFeedController;
use App\Dashboard\Interfaces\Http\Controllers\EventStreamController;
use App\Dashboard\Interfaces\Http\Controllers\MetricsController;
use App\Dashboard\Interfaces\Http\Controllers\ModulesCatalogController;
use App\Dashboard\Interfaces\Http\Controllers\NodeStatusController;
use App\Control\Interfaces\Http\Controllers\ClientSupportReportApiController;
use Illuminate\Support\Facades\Route;

final class DashboardApiRoutes
{
    public static function register(string $prefix): void
    {
        Route::prefix($prefix)
            ->middleware(['auth.platform', 'throttle:platform-api'])
            ->group(function (): void {
                Route::middleware('platform.ability:dashboard:read')->group(function (): void {
                    Route::get('snapshot', [DashboardController::class, 'snapshot']);
                    Route::get('metrics', [MetricsController::class, 'global']);
                    Route::get('metrics/catalog', [MetricsController::class, 'catalog']);
                    Route::get('metrics/series/{metric}', [MetricsController::class, 'metricSeries']);
                    Route::get('metrics/flow', [MetricsController::class, 'flowDiagram']);
                    Route::get('metrics/daily-series', [MetricsController::class, 'configuredDailySeries']);
                    Route::get('modules/catalog', [ModulesCatalogController::class, 'catalog']);
                    Route::get('events/feed', [EventFeedController::class, 'index']);
                    Route::get('nodes/status', [NodeStatusController::class, 'status']);
                    Route::get('middleware/bus', [NodeStatusController::class, 'busMetrics']);
                    Route::post('support/reports', [ClientSupportReportApiController::class, 'store']);

                    Route::middleware('throttle:platform-stream')
                        ->get('stream', [EventStreamController::class, 'stream']);
                });

                Route::middleware('platform.ability:bus:admin')->group(function (): void {
                    Route::post('nodes/{node}/refresh', [NodeStatusController::class, 'refresh']);
                    Route::patch('nodes/{node}/middleware-events', [NodeStatusController::class, 'patchMiddlewareEvents']);
                });
            });
    }
}
