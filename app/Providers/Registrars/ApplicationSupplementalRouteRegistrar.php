<?php

declare(strict_types=1);

namespace App\Providers\Registrars;

use App\Http\Controllers\Health\ReadinessController;
use App\Simulation\Interfaces\Http\Controllers\SimulationRunInternalController;
use Illuminate\Support\Facades\Route;

/**
 * Supplemental routes registered after core web/api/console files (composition root).
 */
final class ApplicationSupplementalRouteRegistrar
{
    public static function register(): void
    {
        Route::middleware(['simulation.internal'])
            ->prefix('control/internal')
            ->group(function (): void {
                Route::get('simulation-runs/{run}', [SimulationRunInternalController::class, 'show']);
                Route::patch('simulation-runs/{run}/progress', [SimulationRunInternalController::class, 'progress']);
                Route::post('simulation-runs/{run}/complete', [SimulationRunInternalController::class, 'complete']);
                Route::post('simulation-runs/{run}/fail', [SimulationRunInternalController::class, 'fail']);
            });

        Route::middleware('web')->group(base_path('routes/control.php'));
        Route::get('/health/ready', ReadinessController::class)->name('health.ready');
        // tenant_portal MUST be last: its /{tenant_slug}/{path} wildcard must not shadow exact routes above.
        Route::middleware('web')->group(base_path('routes/tenant_portal.php'));
    }
}
