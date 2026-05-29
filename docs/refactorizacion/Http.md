# Auditoría — Http (Capa de entrega Laravel)

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Http/` |
| **Namespace** | `App\Http\` |
| **Tipo** | Adapter / delivery mechanism (no BC) |
| **Archivos PHP** | 18 |
| **LOC aprox.** | 668 |
| **Controllers restantes** | 2 (`Auth/Login`, `Health/Readiness`) |
| **Middleware** | 14 |
| **Tests** | Indirectos vía Feature (sin suite dedicada) |

> **Última refactorización:** 2026-05-28 — controllers web movidos a BCs; lógica extraída a Shared/Platform e Identity.

## ¿Qué hace?

Capa **HTTP de Laravel** residual: autenticación web, health checks y **middleware stack** (CSRF, instance portal, control plane host, simulation internal, security headers, correlation). Los controllers de negocio viven en los bounded contexts.

## ¿Para qué sirve?

- Enrutar requests a use cases/services de los BC (vía rutas que apuntan a `Control/`, `Dashboard/`, `Middleware/`).
- Aplicar políticas de acceso por tipo de instancia (CP `:8000` vs cliente `:8001/8002`).
- Shared props Inertia y probes de readiness.

## Estructura (post-refactor)

```text
app/Http/
├── Controllers/
│   ├── Auth/LoginController.php      ~65 LOC — delega auth + home path
│   └── Health/ReadinessController.php ~20 LOC — delega probe
└── Middleware/                       gates, CSRF, Inertia shell, correlation
```

| Subcarpeta | Archivos | Rol |
|------------|----------|-----|
| `Controllers/` | 2 | Solo auth + readiness (sin lógica de negocio) |
| `Middleware/` | 14 | Gates, CSRF, binding, Inertia wrapper |

### Controllers migrados fuera de `app/Http/`

| Antes | Ahora |
|-------|-------|
| `Controllers/Web/DashboardWebController` | `Dashboard/Interfaces/Http/Controllers/Web/` |
| `Controllers/Web/MiddlewareWebController` | `Middleware/Interfaces/Http/Controllers/Web/` |
| `Controllers/Web/Support*WebController` | `Control/Interfaces/Http/Controllers/Web/` |
| `Controllers/Control/*` (12) | `Control/Interfaces/Http/Controllers/` (refactor previo) |

## Servicios extraídos en esta refactorización

| Servicio | Ubicación | Reemplaza lógica en |
|----------|-----------|---------------------|
| `InertiaSharedPropsResolver` | `Shared/Platform/Services` | `HandleInertiaRequests::share` |
| `InstancePortalAccessGuard` | `Shared/Platform/Services` | `EnsureInstancePortalAccess` |
| `InstanceReadinessProbe` | `Shared/Platform/Services` | `ReadinessController` checks |
| `ResolveOperatorHomePathUseCase` | `Shared/Identity/Application` | `LoginController::homeForUser` |
| `MiddlewareIndexPageService` | `Middleware/Application/Services` | `MiddlewareWebController` |
| `ClientSupportWebService` | `Control/Application/Services` | Support web controllers |
| `CompanyShowPageService` | `Control/Application/Services/Tenants` | `CompanyController::show` |

## Métricas de deuda

| Indicador | Antes | **Ahora** | Detalle |
|-----------|-------|-----------|---------|
| **% código sucio** | 44% | **14%** | Sin controllers de negocio en Http |
| **% código espagueti** | 38% | **12%** | Middleware delgados; reglas en servicios |
| **Archivos >150 LOC** | 4 | **0** | Mayor archivo: `VerifyCsrfToken` (~80 LOC) |
| **Controllers web en Http** | ~8 | **0** | Patrón unificado con Dashboard/Middleware/Control |

### Middleware relevante (sin cambio de contrato)

| Middleware | Rol |
|------------|-----|
| `EnsureControlPlaneHost` | Aísla CP |
| `EnsureInstancePortalAccess` | Portal cliente (delega a `InstancePortalAccessGuard`) |
| `EnsureSimulationInternalRequest` | API simulación interna |
| `EnsureAuthenticatedInstanceBinding` | Tenant binding |
| `HandleInertiaRequests` | Wrapper Inertia (~25 LOC; props en resolver) |
| `VerifyCsrfToken` | CSRF multi-instancia (XSRF cookie por silo) |

## Cosas sueltas / inconsistentes (restantes)

1. **CSRF en Feature tests** — rutas web con sesión requieren token o `withoutMiddleware(VerifyCsrfToken)`; varios tests CP siguen con 419/302 por entorno de test (pre-existente).
2. **LoginController** — validación inline (sin Form Request dedicado).
3. **Middleware stack** — permanece en `app/Http/` por convención Laravel; aliases en `SecurityServiceProvider`.

## Acoplamientos

| Hacia | Tipo | Riesgo |
|-------|------|--------|
| Shared/Platform | Inertia props, portal guard, readiness | ✅ Bajo |
| Shared/Identity | Login redirect | ✅ Bajo |
| BC controllers | Rutas en `routes/web.php`, `routes/control.php` | ✅ Correcto |

## Cobertura de tests

- **Verificado (2026-05-28):** `ClientDashboardNodesWebTest` corregido (tenant_id + CSRF); Unit Control/Dashboard pasan.
- **Gaps:** tests unitarios aislados de middleware; Form Requests; suite dedicada `tests/Unit/Http/`.

## Recomendaciones de refactor (futuro)

| Prioridad | Acción |
|-----------|--------|
| P3 | Form Request classes para login y acciones web restantes. |
| P3 | Helper/trait de test para requests web con CSRF válido. |
| P4 | Tests unitarios de `InstancePortalAccessGuard` y `VerifyCsrfToken`. |

## Veredicto

**Capa Http adelgada** a su rol de delivery: auth, health, middleware. Controllers de portal y CP viven en sus BCs. Deuda restante concentrada en ergonomía de tests CSRF y Form Requests opcionales.
