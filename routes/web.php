<?php

declare(strict_types=1);

use App\Control\Interfaces\Http\Controllers\Web\SupportNotificationsWebController;
use App\Control\Interfaces\Http\Controllers\Web\SupportReportWebController;
use App\Http\Controllers\Auth\LoginController;
use App\Dashboard\Interfaces\Http\Controllers\Web\ClientDashboardMetricsWebController;
use App\Dashboard\Interfaces\Http\Controllers\Web\ClientDashboardModulesWebController;
use App\Dashboard\Interfaces\Http\Controllers\Web\ClientDashboardNodesWebController;
use App\Dashboard\Interfaces\Http\Controllers\Web\DashboardWebController;
use App\Middleware\Interfaces\Http\Controllers\Web\MiddlewareWebController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::redirect('/', '/dashboard');

Route::middleware(['auth.platform.web', 'instance.web', 'instance.portal'])->group(function () {
    Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/metrics/catalog', [ClientDashboardMetricsWebController::class, 'catalog'])
        ->name('dashboard.metrics.catalog');
    Route::get('/dashboard/metrics/series/{metric}', [ClientDashboardMetricsWebController::class, 'series'])
        ->where('metric', '[^/]+')
        ->name('dashboard.metrics.series');
    Route::patch('/dashboard/modules/visibility', [ClientDashboardModulesWebController::class, 'updateVisibility'])
        ->name('dashboard.modules.visibility');
    Route::get('/dashboard/nodes/status', [ClientDashboardNodesWebController::class, 'status'])
        ->name('dashboard.nodes.status');
    Route::post('/dashboard/nodes/{node}/refresh', [ClientDashboardNodesWebController::class, 'refresh'])
        ->where('node', '[^/]+')
        ->name('dashboard.nodes.refresh');
    Route::patch('/dashboard/nodes/{node}/middleware-events', [ClientDashboardNodesWebController::class, 'patchMiddlewareEvents'])
        ->where('node', '[^/]+')
        ->name('dashboard.nodes.middleware-events');
    Route::get('/middleware', [MiddlewareWebController::class, 'index'])->name('middleware');
    Route::post('/support/reports', [SupportReportWebController::class, 'store'])->name('support.reports.store');
    Route::get('/support/notifications', [SupportNotificationsWebController::class, 'index'])->name('support.notifications.index');
    Route::get('/support/reports/{report}', [SupportNotificationsWebController::class, 'show'])->name('support.reports.show');
    Route::post('/support/notifications/read-all', [SupportNotificationsWebController::class, 'markAllRead'])->name('support.notifications.read-all');
    Route::post('/support/notifications/{report}/read', [SupportNotificationsWebController::class, 'markRead'])->name('support.notifications.read');
});

// Legacy path — user management moved to SaaS control plane
Route::redirect('/admin/users', '/control/companies');
