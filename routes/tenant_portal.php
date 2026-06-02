<?php

declare(strict_types=1);

use App\Control\Interfaces\Http\Controllers\TenantPortalProxyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Portal — Path-based friendly routing (ADR-011, Fase 7 v1.6)
|--------------------------------------------------------------------------
|
| Activado únicamente cuando PLATFORM_FRIENDLY_ROUTING=true en el control
| plane. El middleware 'tenant.path.resolver' verifica el flag en runtime.
|
| Patrón: GET /{tenant_slug}/{path?}
|   → ResolveTenantFromRoutePath (valida slug, estado y silo_url)
|   → TenantPortalProxyController::redirect (302 al puerto del silo)
|
| Retrocompatibilidad: no interfiere con rutas existentes. Las rutas exactas
| registradas antes (/login, /control/*, /api/*) tienen prioridad absoluta.
|
*/

Route::prefix('{tenant_slug}')
    ->middleware(['control.plane', 'tenant.path.resolver'])
    ->group(function (): void {
        Route::get('login', [TenantPortalProxyController::class, 'redirect'])
             ->name('tenant.portal.login');

        Route::get('/{path}', [TenantPortalProxyController::class, 'redirect'])
             ->where('path', '.*')
             ->name('tenant.portal.proxy');

        Route::get('/', [TenantPortalProxyController::class, 'redirect'])
             ->name('tenant.portal.root');
    });
