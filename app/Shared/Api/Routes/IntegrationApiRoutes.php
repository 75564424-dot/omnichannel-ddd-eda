<?php

declare(strict_types=1);

namespace App\Shared\Api\Routes;

use App\Integration\Interfaces\Http\Controllers\ChannelController;
use App\Integration\Interfaces\Http\Controllers\IntegrationController;
use App\Integration\Interfaces\Http\Controllers\WebhookIngressController;
use Illuminate\Support\Facades\Route;

final class IntegrationApiRoutes
{
    public static function register(string $prefix): void
    {
        Route::prefix($prefix)->group(function (): void {
            Route::middleware(['throttle:platform-publish'])
                ->post('/webhooks/{integrationCode}', [WebhookIngressController::class, 'receive']);

            Route::middleware(['auth.platform', 'throttle:platform-api'])->group(function (): void {
                Route::middleware('platform.ability:integrations:admin')->group(function (): void {
                    Route::get('/channels', [ChannelController::class, 'index']);
                    Route::post('/channels', [ChannelController::class, 'store']);
                    Route::get('/channels/{id}', [ChannelController::class, 'show']);
                    Route::patch('/channels/{id}', [ChannelController::class, 'update']);
                    Route::delete('/channels/{id}', [ChannelController::class, 'destroy']);

                    Route::get('/', [IntegrationController::class, 'index']);
                    Route::post('/', [IntegrationController::class, 'store']);
                    Route::get('/{id}', [IntegrationController::class, 'show']);
                    Route::patch('/{id}', [IntegrationController::class, 'update']);
                    Route::delete('/{id}', [IntegrationController::class, 'destroy']);
                    Route::post('/{id}/credentials', [IntegrationController::class, 'storeCredential']);
                    Route::post('/{id}/connectors/{connectorId}/dispatch', [IntegrationController::class, 'dispatchOutbound']);
                });
            });
        });
    }
}
