<?php

declare(strict_types=1);

use App\Http\Controllers\Control\CompanyController;
use App\Http\Controllers\Control\SimulationRunController;
use App\Http\Controllers\Control\IncidentsController;
use App\Http\Controllers\Control\InfrastructureController;
use App\Http\Controllers\Control\MiddlewareGlobalController;
use App\Http\Controllers\Control\OverviewController;
use App\Http\Controllers\Control\ProvisioningController;
use Illuminate\Support\Facades\Route;

Route::redirect('/control', '/control/overview');
Route::redirect('/control/tenants', '/control/companies');
Route::redirect('/control/users', '/control/companies');

Route::middleware(['auth.platform.web', 'control.web'])->prefix('control')->group(function (): void {
    Route::get('/overview', [OverviewController::class, 'index'])->name('control.overview');

    Route::prefix('simulations')->name('control.simulations.')->group(function (): void {
        Route::get('/', [SimulationRunController::class, 'index'])->name('index');
        Route::get('/{run}/status', [SimulationRunController::class, 'status'])->name('status');
        Route::get('/{run}/report', [SimulationRunController::class, 'report'])->name('report');
    });

    Route::prefix('companies')->name('control.companies.')->group(function (): void {
        Route::get('/', [CompanyController::class, 'index'])->name('index');
        Route::post('/', [CompanyController::class, 'store'])->name('store');
        Route::post('/simulation', [SimulationRunController::class, 'store'])->name('simulation.run');
        Route::get('/{tenant}/modules', [CompanyController::class, 'modulesConfig'])->name('modules');
        Route::patch('/{tenant}/modules-catalog', [CompanyController::class, 'updateModulesCatalog'])->name('modules-catalog.update');
        Route::post('/{tenant}/modules-catalog/apply', [CompanyController::class, 'applyModulesCatalog'])->name('modules-catalog.apply');
        Route::get('/{tenant}', [CompanyController::class, 'show'])->name('show');
        Route::post('/{tenant}/suspend', [CompanyController::class, 'suspend'])->name('suspend');
        Route::post('/{tenant}/activate', [CompanyController::class, 'activate'])->name('activate');
        Route::patch('/{tenant}/plan', [CompanyController::class, 'updatePlan'])->name('update-plan');
        Route::patch('/{tenant}/modules', [CompanyController::class, 'updateModules'])->name('update-modules');
        Route::post('/{tenant}/operators', [CompanyController::class, 'storeOperator'])->name('operators.store');
        Route::patch('/{tenant}/operators/{user}/role', [CompanyController::class, 'updateOperatorRole'])->name('operators.update-role');
        Route::patch('/{tenant}/operators/{user}/password', [CompanyController::class, 'updateOperatorPassword'])->name('operators.update-password');
    });

    Route::get('/middleware', [MiddlewareGlobalController::class, 'index'])->name('control.middleware');
    Route::get('/infrastructure', [InfrastructureController::class, 'index'])->name('control.infrastructure');
    Route::get('/incidents', [IncidentsController::class, 'index'])->name('control.incidents');
    Route::get('/incidents/reports/{report}', [IncidentsController::class, 'showReport'])->name('control.incidents.reports.show');
    Route::patch('/incidents/reports/{report}', [IncidentsController::class, 'updateReport'])->name('control.incidents.reports.update');
    Route::post('/incidents/reports/{report}/respond', [IncidentsController::class, 'respondReport'])->name('control.incidents.reports.respond');

    Route::get('/provisioning', [ProvisioningController::class, 'index'])->name('control.provisioning');
    Route::post('/provisioning', [ProvisioningController::class, 'store'])->name('control.provisioning.store');
});
