# Plan de Migración — Routing Amigable Multi-Tenant v1.6

**Fecha:** 2026-06-02  
**ADR relacionado:** [ADR-011](../production/ADR_011_friendly_routing_multitenant.md)  
**Runbook:** Runbook_v1.6 §Problema 5 — Fase 6 (Diseño) y Fase 7 (Implementación)

---

## Objetivo

Migrar el routing del sistema desde URLs basadas en puertos hacia URLs amigables basadas en rutas, de forma **aditiva** (sin romper el routing por puertos existente), con activación mediante feature flag.

---

## Estado actual (pre-migración)

```
Control plane:     http://127.0.0.1:8000/control/companies
Silo acme-retail:  http://127.0.0.1:8001/login
Silo pruebas:      http://127.0.0.1:8002/login
Silo lc-test:      http://127.0.0.1:8003/login
```

Cada silo es un proceso independiente. El usuario debe conocer el puerto de su empresa.

---

## Estado objetivo v1.6 (post-migración Fase 7)

```
Control plane:     http://127.0.0.1:8000/control/companies  (sin cambio)
Acme vía ruta:     http://127.0.0.1:8000/acme-retail/login  → redirect a 8001
Pruebas vía ruta:  http://127.0.0.1:8000/pruebas-retail/login → redirect a 8002
Silos directos:    http://127.0.0.1:8001/login               (siguen funcionando)
```

---

## Etapas de migración

### Etapa 0 — Diseño y decisiones (Fase 6 — COMPLETADA)

- [x] ADR-011 redactado y aprobado
- [x] Estrategia de proxy seleccionada: **Opción A — Redirección al puerto del silo**
- [x] Feature flag diseñado: `PLATFORM_FRIENDLY_ROUTING`
- [x] Impacto en componentes existentes evaluado: ningún cambio en runtime
- [x] Plan de migración documentado (este archivo)

---

### Etapa 1 — Routing por ruta (Fase 7 de v1.6)

**Precondición:** `PLATFORM_FRIENDLY_ROUTING=false` (runtime actual no afectado hasta este cambio)

#### Paso 1.1 — Añadir config flag

Archivo: `config/platform.php`

```php
'friendly_routing' => filter_var(
    env('PLATFORM_FRIENDLY_ROUTING', false),
    FILTER_VALIDATE_BOOLEAN,
),
```

Archivo: `.env.control-plane` (solo el control plane activa el routing amigable)

```ini
PLATFORM_FRIENDLY_ROUTING=true
PLATFORM_PORTAL_MULTI_TENANT_LOGIN=true
```

#### Paso 1.2 — Implementar middleware `ResolveTenantFromRoutePath`

Archivo nuevo: `app/Http/Middleware/ResolveTenantFromRoutePath.php`

Responsabilidades:
1. Extraer `{tenant_slug}` de la ruta
2. Resolver el tenant en la tabla `tenants`
3. Verificar que el tenant está `active` y tiene silo provisionado
4. Llamar `bindPortalTenantFromSession($tenantId)`
5. Adjuntar `resolved_silo_url` al request para el controlador proxy

Registro en `bootstrap/app.php` con alias `tenant.path.resolver`.

#### Paso 1.3 — Implementar controlador proxy `TenantPortalProxyController`

Archivo nuevo: `app/Control/Interfaces/Http/Controllers/TenantPortalProxyController.php`

Acción principal: obtener `resolved_silo_url` del request y devolver `redirect($siloUrl . request()->path_without_slug())`.

Rutas cubiertas: `/login`, `/dashboard`, `/middleware` (las rutas más comunes del portal de operador).

#### Paso 1.4 — Registrar rutas en `bootstrap/app.php`

```php
if (config('platform.friendly_routing') && config('platform.control_plane')) {
    Route::middleware('web')
         ->group(base_path('routes/tenant_portal.php'));
}
```

Archivo nuevo: `routes/tenant_portal.php`

```php
Route::prefix('{tenant_slug}')
    ->middleware(['control.plane', 'web', 'tenant.path.resolver'])
    ->group(function (): void {
        Route::get('/login', [TenantPortalProxyController::class, 'showLogin'])
             ->name('tenant.login');
        Route::post('/login', [TenantPortalProxyController::class, 'handleLogin'])
             ->name('tenant.login.store');
        Route::get('/dashboard', [TenantPortalProxyController::class, 'redirect'])
             ->name('tenant.dashboard');
        Route::get('/middleware', [TenantPortalProxyController::class, 'redirect'])
             ->name('tenant.middleware');
        Route::any('/{path}', [TenantPortalProxyController::class, 'redirect'])
             ->where('path', '.*')
             ->name('tenant.proxy');
    });
```

#### Paso 1.5 — Tests de integración

Archivo nuevo: `tests/Feature/Control/TenantPortalRoutingTest.php`

Casos mínimos:
- `GET /acme-retail/login` → 302 → `http://127.0.0.1:8001/login` (cuando silo activo)
- `GET /unknown-slug/login` → 404
- `GET /lifecycle-test/login` → 302 → `http://127.0.0.1:8003/login`
- `GET /acme-retail/login` con tenant suspendido → 503
- `GET /acme-retail/login` con tenant sin silo provisionado → 503
- `GET /login` (sin slug) → sin cambio (ruta original)

#### Paso 1.6 — Activación y validación

1. Activar `PLATFORM_FRIENDLY_ROUTING=true` en `.env.control-plane`
2. Reiniciar control plane
3. Probar HTTP: `curl -I http://127.0.0.1:8000/acme-retail/login`
4. Esperar redirect 302 a `http://127.0.0.1:8001/login`
5. Verificar que rutas directas por puerto siguen funcionando

---

### Etapa 2 — Routing por subdominio (Post-v1.6)

**Precondición:** Infraestructura de red configurada (DNS, nginx/caddy)

#### Paso 2.1 — Nuevo parámetro de config

```ini
PLATFORM_INSTANCE_BASE_DOMAIN=middleware.example.com
```

#### Paso 2.2 — Middleware `ResolveTenantFromSubdomain`

Mismo contrato que `ResolveTenantFromRoutePath` pero extrae el slug del host:

```php
$host = $request->getHost();
$baseDomain = config('platform.deployment.base_domain');
$slug = Str::before($host, '.' . $baseDomain);
```

#### Paso 2.3 — Estrategia de proxy

En subdominios, el nginx actúa de reverse proxy hacia el silo correspondiente. No se requiere proxy PHP.

```nginx
server {
    server_name ~^(?P<slug>[^.]+)\.middleware\.example\.com$;
    location / {
        proxy_pass http://127.0.0.1:$silo_port;  # mapeado por slug
    }
}
```

O alternativamente, el control plane detecta el subdominio y hace redirect al silo (Opción A extendida a subdominios).

---

## Compatibilidad retroactiva

| Componente | Afectado | Nota |
|---|---|---|
| Silos por puerto (8001, 8002, 8003) | No | Siguen funcionando igual |
| Control plane `/control/*` | No | No cambia |
| Auth (login por puerto) | No | No cambia |
| Lifecycle (start/suspend/restore) | No | No cambia |
| Fleet registry | No | Se lee en el resolver |
| Provisioning | No | No cambia |
| Tests existentes | No | No usan rutas `/{slug}/*` |
| Simulación | No | No cambia |

---

## Feature flags necesarios en `.env.control-plane` para activar Fase 7

```ini
# Activar routing amigable (solo en control plane)
PLATFORM_FRIENDLY_ROUTING=true

# Requerido para que el resolver llame a bindPortalTenantFromSession
PLATFORM_PORTAL_MULTI_TENANT_LOGIN=true
```

> **Los silos NO deben tener PLATFORM_FRIENDLY_ROUTING.**  
> Los silos siguen siendo instancias independientes con `PLATFORM_CLIENT_SLUG` propio.

---

## Criterios de aceptación de Fase 7

- [ ] `GET http://127.0.0.1:8000/acme-retail/login` → redirect 302 → `http://127.0.0.1:8001/login`
- [ ] `GET http://127.0.0.1:8000/pruebas-retail/login` → redirect 302 → `http://127.0.0.1:8002/login`
- [ ] `GET http://127.0.0.1:8001/login` sigue respondiendo 200 (sin cambio)
- [ ] Tenant inexistente → 404
- [ ] Tenant suspendido → 503
- [ ] Tenant sin silo → 503
- [ ] `PLATFORM_FRIENDLY_ROUTING=false` → rutas `/{slug}/*` no existen (runtime sin cambio)

---

## Diagrama de flujo (Etapa 1 — Redirección)

```
Browser
  │
  │  GET http://127.0.0.1:8000/acme-retail/login
  ▼
Control Plane (port 8000)
  │
  ├── Middleware: EnsureControlPlaneHost (control_plane=true ✓)
  ├── Middleware: ResolveTenantFromRoutePath
  │     ├── slug = 'acme-retail'
  │     ├── tenant = tenants WHERE slug = 'acme-retail'
  │     ├── status = 'active' ✓
  │     ├── silo_url = 'http://127.0.0.1:8001'
  │     └── bindPortalTenantFromSession(tenant.id) → session['portal_tenant_id']
  │
  └── TenantPortalProxyController::showLogin()
        └── redirect('http://127.0.0.1:8001/login', 302)
              │
              ▼
          Silo Acme Retail (port 8001)
            │
            └── GET /login → renders login form
```

```
Browser
  │
  │  POST http://127.0.0.1:8001/login  (credentials)
  ▼
Silo Acme Retail (port 8001)
  │
  ├── AuthenticateOperatorUseCase
  │     ├── Auth::attempt() ✓
  │     ├── user.tenant_id === silo_tenant_id ✓
  │     └── session regenerate
  │
  └── redirect('/dashboard')
```
