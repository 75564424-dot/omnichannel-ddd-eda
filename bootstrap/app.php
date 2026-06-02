<?php

declare(strict_types=1);

use App\Simulation\Interfaces\Http\Controllers\SimulationRunInternalController;
use App\Http\Controllers\Health\ReadinessController;
use App\Http\Middleware\CorrelationIdMiddleware;
use App\Http\Middleware\EnsureAuthenticatedInstanceBinding;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use App\Observability\Interfaces\Providers\ObservabilityServiceProvider;
use App\Providers\LoggingServiceProvider;
use App\Providers\SecurityServiceProvider;
use App\Shared\Api\Http\Middleware\AppendRateLimitHeadersMiddleware;
use App\Shared\Api\Http\Responses\ProblemDetailsFactory;
use App\Shared\Api\Interfaces\Providers\ApiServiceProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
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
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(replace: [
            ValidateCsrfToken::class => VerifyCsrfToken::class,
        ]);

        $middleware->web(prepend: [
            \App\Http\Middleware\EnsureTenantOperationalStatus::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            SecurityHeadersMiddleware::class,
            EnsureAuthenticatedInstanceBinding::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\EnsureTenantOperationalStatus::class,
            EnsureFrontendRequestsAreStateful::class,
            CorrelationIdMiddleware::class,
            SecurityHeadersMiddleware::class,
        ]);

        $middleware->api(append: [
            AppendRateLimitHeadersMiddleware::class,
        ]);

        $middleware->alias(array_merge(
            SecurityServiceProvider::middlewareAliases(),
            LoggingServiceProvider::middlewareAliases(),
            ObservabilityServiceProvider::middlewareAliases(),
            ApiServiceProvider::middlewareAliases(),
        ));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') && config('platform_api.problem_details.enabled', true)) {
                return ProblemDetailsFactory::unauthorized($e->getMessage());
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*') && config('platform_api.problem_details.enabled', true)) {
                return ProblemDetailsFactory::forbidden($e->getMessage());
            }
        });
    })->create();
