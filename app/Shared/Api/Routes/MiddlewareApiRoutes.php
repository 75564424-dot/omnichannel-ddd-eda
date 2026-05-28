<?php

declare(strict_types=1);

namespace App\Shared\Api\Routes;

use App\Middleware\Interfaces\Http\Controllers\BusMetricsController;
use App\Middleware\Interfaces\Http\Controllers\DeadLetterController;
use App\Middleware\Interfaces\Http\Controllers\EventQueueController;
use App\Middleware\Interfaces\Http\Controllers\EventSearchController;
use App\Middleware\Interfaces\Http\Controllers\ModuleRegistrySyncController;
use App\Middleware\Interfaces\Http\Controllers\TopologyController;
use Illuminate\Support\Facades\Route;

final class MiddlewareApiRoutes
{
    public static function register(string $prefix): void
    {
        Route::prefix($prefix)
            ->middleware(['auth.platform', 'throttle:platform-api'])
            ->group(function (): void {
                Route::middleware('platform.ability:bus:read')->group(function (): void {
                    Route::get('/metrics', [BusMetricsController::class, 'index']);
                    Route::get('/queue', [EventQueueController::class, 'index']);
                    Route::get('/topology', [TopologyController::class, 'index']);
                    Route::get('/status', [EventSearchController::class, 'status']);
                    Route::get('/events/{eventId}', [EventSearchController::class, 'show']);
                    Route::get('/dead-letters', [DeadLetterController::class, 'index']);
                });

                Route::middleware(['platform.ability:bus:admin', 'throttle:platform-sync', 'platform.audit:registry.sync,bus_registry'])
                    ->post('/registry/sync-config', [ModuleRegistrySyncController::class, 'syncFromConfig']);

                Route::middleware(['platform.ability:bus:admin', 'platform.audit:metrics.refresh,bus_metrics'])
                    ->post('/metrics/refresh', [BusMetricsController::class, 'refresh']);

                Route::middleware(['platform.ability:bus:admin', 'platform.audit:dead_letter.resolve,dead_letter'])
                    ->patch('/dead-letters/{id}/resolve', [DeadLetterController::class, 'resolve']);

                Route::middleware(['platform.ability:bus:admin', 'platform.audit:dead_letter.requeue,dead_letter'])
                    ->post('/dead-letters/{id}/requeue', [DeadLetterController::class, 'requeue']);

                Route::middleware(['platform.ability:events:publish', 'throttle:platform-publish', 'platform.audit:events.publish,bus_event'])
                    ->post('/events/publish', [EventQueueController::class, 'publish']);
            });
    }
}
