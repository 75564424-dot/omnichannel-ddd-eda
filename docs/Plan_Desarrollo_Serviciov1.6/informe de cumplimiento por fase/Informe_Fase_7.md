# Informe de Cumplimiento — Fase 7: Implementación Routing Amigable

**Estado: Cumple**  
**Fecha:** 2026-06-02  
**Runbook:** `docs/Plan_Desarrollo_Serviciov1.6/Runbook_v1.6_Estabilizacion_Operativa_y_Routing_MultiTenant.md`  
**ADR:** `docs/production/ADR_011_friendly_routing_multitenant.md`

---

## Objetivo

Implementar routing path-based (Etapa 1 del ADR-011) en el control plane de forma **aditiva** y detrás de un feature flag, habilitando URLs amigables del tipo `http://127.0.0.1:8000/{slug}/login` que redirigen (HTTP 302) al silo correspondiente por puerto.

---

## Evidencia encontrada

### Archivos involucrados en la implementación

| Archivo | Rol |
|---|---|
| `app/Http/Middleware/ResolveTenantFromRoutePath.php` | Middleware que extrae el slug, verifica el tenant y adjunta `resolved_silo_url` |
| `app/Control/Interfaces/Http/Controllers/TenantPortalProxyController.php` | Controlador que emite el 302 al silo |
| `routes/tenant_portal.php` | Grupo de rutas `/{tenant_slug}/{path?}` |
| `config/platform.php` | Config key `friendly_routing` (`PLATFORM_FRIENDLY_ROUTING`) |
| `app/Providers/SecurityServiceProvider.php` | Alias `tenant.path.resolver` registrado |
| `bootstrap/app.php` | Carga de `routes/tenant_portal.php` (último, después de rutas exactas) |
| `.env.control-plane` | `PLATFORM_FRIENDLY_ROUTING=true` activado |
| `tests/Feature/Control/TenantPortalRoutingTest.php` | 9 tests de integración |

### Flujo implementado

```
GET http://127.0.0.1:8000/lifecycle-test/login
     │
     ▼ middleware web
     ├─ EnsureControlPlaneHost → PASS (control_plane=true)
     └─ ResolveTenantFromRoutePath
           │ platform.friendly_routing=true → continúa
           │ busca tenant con slug='lifecycle-test' → encontrado, activo
           │ lee settings.deployment.local_instance.app_url = 'http://127.0.0.1:8003'
           │ adjunta resolved_silo_url al request
           ▼
     TenantPortalProxyController::redirect
           └─ return redirect('http://127.0.0.1:8003/login', 302)

HTTP/1.1 302 Found
Location: http://127.0.0.1:8003/login
```

### Test suite

```
Tests\Feature\Control\TenantPortalRoutingTest
  ✓ it redirects login path to silo login
  ✓ it redirects dashboard path to silo dashboard
  ✓ it defaults to login for root tenant path
  ✓ it redirects nested paths
  ✓ it returns 503 for suspended tenant
  ✓ it returns 404 for unknown tenant slug
  ✓ it returns 503 when tenant has no provisioned silo
  ✓ it returns 404 when friendly routing flag is disabled
  ✓ it returns 404 on non control plane host

Tests: 9 passed (14 assertions)
```

### Validación HTTP real

| URL | HTTP | Destino | Causa |
|---|---|---|---|
| `GET /lifecycle-test/login` | **302** | `http://127.0.0.1:8003/login` | Silo provisionado ✓ |
| `GET /acme-retail/login` | 503 | — | Tenant sin `local_instance.app_url` (estado heredado de Fase 2) |
| `GET /pruebas-retail/login` | 503 | — | Ídem |
| `GET /platform/login` | 503 | — | Tenant de control plane, sin silo externo |

---

## Cambios realizados

1. `config/platform.php` — Nueva key `friendly_routing` con `FILTER_VALIDATE_BOOLEAN`
2. `app/Http/Middleware/ResolveTenantFromRoutePath.php` — **Nuevo archivo** — middleware que valida slug, estado y metadata de silo
3. `app/Control/Interfaces/Http/Controllers/TenantPortalProxyController.php` — **Nuevo archivo** — emite 302 al silo
4. `routes/tenant_portal.php` — **Nuevo archivo** — rutas `/{tenant_slug}/*` con middlewares `control.plane` + `tenant.path.resolver`
5. `app/Providers/SecurityServiceProvider.php` — Añadido alias `tenant.path.resolver`
6. `bootstrap/app.php` — Registro de `routes/tenant_portal.php` como último grupo de rutas (prioridad inferior a rutas exactas)
7. `.env.control-plane` — Añadido `PLATFORM_FRIENDLY_ROUTING=true`
8. `tests/Feature/Control/TenantPortalRoutingTest.php` — **Nuevo archivo** — 9 tests de integración

---

## Archivos modificados

- `config/platform.php`
- `app/Providers/SecurityServiceProvider.php`
- `bootstrap/app.php`
- `.env.control-plane`

## Archivos nuevos

- `app/Http/Middleware/ResolveTenantFromRoutePath.php`
- `app/Control/Interfaces/Http/Controllers/TenantPortalProxyController.php`
- `routes/tenant_portal.php`
- `tests/Feature/Control/TenantPortalRoutingTest.php`

---

## Riesgos detectados

| Riesgo | Estado |
|---|---|
| Conflicto de rutas: `/{slug}` shadow sobre `/login`, `/health/ready`, etc. | **Mitigado** — `tenant_portal.php` se registra último, las rutas exactas tienen prioridad |
| Route wildcard capturando `/health/ready` | **Detectado y corregido** — movido registro después de `Route::get('/health/ready', ...)` |
| `EnsureTenantOperationalStatus` en CP no corre para tenant de silo | **Aceptado por diseño** — en CP, el middleware skippea (control_plane=true). El resolver verifica `status !== 'active'` directamente |

## Riesgos mitigados

- **Route ordering bug:** En primera implementación, `tenant_portal.php` se registraba antes de `/health/ready`, causando que el wildcard captara la ruta de health. Detectado inmediatamente al correr el suite completo (3 tests fallidos). Corregido moviendo la carga al final del `then` closure.

---

## Hallazgos fuera de alcance

| Hallazgo | Fase | Descripción |
|---|---|---|
| `acme-retail` y `pruebas-retail` sin `settings.deployment.local_instance.app_url` | Fase 2 / Fase 8 | Los tenants históricos fueron importados en la Fase 2 antes de que el provisioner escribiera la metadata de despliegue. Sus silos corren en puertos 8001/8002 pero la BD no tiene su `app_url`. El resolver retorna 503 correctamente. Para activar routing amigable en estos tenants se requiere re-ejecución del provisioner (Fase 8 o mantenimiento de datos). |

---

## Checklist Runbook

| Requisito | Estado | Evidencia |
|---|---|---|
| Config flag `PLATFORM_FRIENDLY_ROUTING` | ✓ Cumple | `config/platform.php` key `friendly_routing` |
| Middleware `ResolveTenantFromRoutePath` implementado | ✓ Cumple | `app/Http/Middleware/ResolveTenantFromRoutePath.php` |
| Controlador proxy `TenantPortalProxyController` implementado | ✓ Cumple | `app/Control/Interfaces/Http/Controllers/TenantPortalProxyController.php` |
| Rutas `/{tenant_slug}/*` registradas | ✓ Cumple | `routes/tenant_portal.php` |
| Feature activada en control plane | ✓ Cumple | `.env.control-plane` con `PLATFORM_FRIENDLY_ROUTING=true` |
| Tests de integración | ✓ Cumple | 9 tests, 14 assertions, 100% pass |
| Sin regresiones en suite completo | ✓ Cumple | 129/129 Feature tests pasan |
| Login por ruta funcional en entorno local | ✓ Cumple | `GET /lifecycle-test/login` → 302 → `http://127.0.0.1:8003/login` |
| No rompe routing por puertos | ✓ Cumple | Los silos en 8001/8002/8003 siguen funcionando independientemente |

---

## Compatibilidad Retroactiva

| Componente | Estado | Justificación |
|---|---|---|
| **lifecycle** | No afectado | Las rutas `/{slug}/*` no interfieren con endpoints `/control/lifecycle/*` |
| **provisioning** | No afectado | El provisioner no usa rutas web |
| **login** | No afectado | `/login` es ruta exacta registrada en `web.php` antes del wildcard |
| **fleet** | No afectado | Fleet opera via CLI y proceso supervisor, no HTTP routing |
| **registry** | No afectado | Las rutas de API (`/api/*`) tienen su propio prefijo |
| **control plane** | No afectado | Rutas `/control/*` registradas antes de `tenant_portal.php` |
| **simulación** | No afectado | Rutas `/control/internal/*` registradas antes del wildcard |
| **health** | No afectado | `/up` y `/health/ready` registradas antes del wildcard |
