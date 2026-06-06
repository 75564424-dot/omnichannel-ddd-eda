# Informe Fase 4 — Validación Tenant Identity

## Estado
**No cumple**

> La fase cumple su objetivo analítico (demostrar que el problema **no es legacy** sino **bug real** reproducible en `tenant-test-*`), pero **no cumple** el criterio de aceptación formal: *«Cada tenant muestra su identidad propia»* en todas las capas validadas (env, BD, props Inertia, UI). Se documentan fallos concretos para corrección en Fase 5.

## Objetivo
Demostrar si el branding incorrecto es legacy o bug real, validando para cada `tenant-test-*` los campos de identidad en `.env`, tabla `tenants` del silo, props Inertia (`company_name`, `company_slug`), UI de login/dashboard, y ausencia de `SaaS` en silos temporales.

## Evidencia encontrada

### Componentes analizados
- `LocalFleetEnvBuilder` — genera `APP_NAME`, `PLATFORM_CLIENT_NAME`, `PLATFORM_CLIENT_SLUG`.
- `LocalFleetTenantMirror` — sincroniza `tenants.name` al silo.
- `ClientInstancePortalService::branding()` — resuelve `company_name` desde fila `tenants` o fallback `clientName()`.
- `InertiaSharedPropsResolver` — comparte `instance.company_name` / `instance.company_slug` en portal `instance`.
- `resources/views/app.blade.php` — `<title>` y `<meta app-name>` usan `config('app.name')`.
- `resources/js/Pages/Auth/Login.vue` — título visual hardcodeado `"Core Platform"`.
- `resources/js/Layouts/AppLayout.vue` — usa `page.props.instance?.company_name` (correcto).
- `LocalFleetProcessSupervisor` / `LocalInstanceEnvironmentLoader` — arranque de silos vía lifecycle `start`.

### Tenants validados (solo `tenant-test-*` post-limpieza)
| Slug | Nombre esperado | Puerto | Operador |
|---|---|---|---|
| `tenant-test-branding` | Tenant Test Branding Co | 8001 | `branding@tenant-test.local` |
| `tenant-test-routing` | Tenant Test Routing Co | 8002 | `routing@tenant-test.local` |
| `tenant-test-simulation` | Tenant Test Simulation Co | 8003 | `simulation@tenant-test.local` |

### Matriz de validación por capa

| Capa | branding | routing | simulation | Resultado |
|---|---|---|---|---|
| `.env` `APP_NAME` | ✓ | ✓ | ✓ | Cumple |
| `.env` `PLATFORM_CLIENT_NAME` | ✓ | ✓ | ✓ | Cumple |
| `.env` `PLATFORM_CLIENT_SLUG` | ✓ | ✓ | ✓ | Cumple |
| Silo `tenants` (slug, name, status) | ✓ active | ✓ active | ✓ active | Cumple |
| `ClientInstancePortalService::branding()` | ✓ | ✓ | ✓ | Cumple |
| Inertia `/login` `instance.company_name` | ✓ | ✓ | ✓ | Cumple |
| Inertia `/dashboard` `instance.company_name` | ✓ (branding) | — | — | Cumple en branding; patrón esperado igual en otros |
| HTML `<title>` / `<meta app-name>` | **SaaS** | **SaaS** | **SaaS** | **No cumple** |
| Cookie de sesión en proceso HTTP | `platform_session_platform` | idem CP | idem CP | **No cumple** (debería ser `platform_session_tenant_test_*`) |
| UI `Login.vue` (código) | **"Core Platform"** hardcoded | idem | idem | **No cumple** |
| Muestra `SaaS` como identidad de empresa | En title/meta | En title/meta | En title/meta | **No cumple** |

### Evidencia HTTP (ejemplo `tenant-test-branding :8001`)

**Login (`GET /login`):**
```json
"instance": {
  "company_name": "Tenant Test Branding Co",
  "company_slug": "tenant-test-branding"
}
```
```html
<meta name="app-name" content="SaaS">
<title>SaaS</title>
```

**Dashboard (`GET /dashboard` tras login `branding@tenant-test.local`):**
```
component=Dashboard/Index
company_name=Tenant Test Branding Co
title=SaaS
```

**Control Plane (`:8000`) — referencia válida:**
- `<title>SaaS</title>` — correcto solo para CP.
- Inertia en CP usa `portal=control`; `instance` es `null` en rutas de control.

### Clasificación del problema (no es legacy)

| Hallazgo | Clasificación | Punto de fallo | Reproducción |
|---|---|---|---|
| Props Inertia `company_name` correctas en silos | OK — no es bug | env + mirror + `ClientInstancePortalService` | `GET /login` en `:8001-8003` |
| `<title>` y `<meta app-name>` muestran `SaaS` en silos | **Bug real** | `app.blade.php` usa `config('app.name')`; proceso HTTP del silo resuelve `app.name=SaaS` aunque `php artisan config:show app.name --env=client-tenant-test-*` devuelve el nombre correcto | Abrir `http://127.0.0.1:8001/login` → pestaña del navegador dice «SaaS» |
| Cookie `platform_session_platform` en silo `:8001` | **Bug real** + **Operativo** | `LocalInstanceEnvironmentLoader::CRITICAL_KEYS` no incluye `APP_NAME` ni `SESSION_COOKIE`; proceso hijo hereda contexto del CP al arrancar vía `LocalFleetProcessSupervisor` | `curl -c - http://127.0.0.1:8001/login` → cookie `platform_session_platform` en lugar de `platform_session_tenant_test_branding` |
| `Login.vue` muestra «Core Platform» | **Bug real** | Frontend: `resources/js/Pages/Auth/Login.vue` línea 7 no usa `page.props.instance.company_name` | Inspección de código + hidratación Vue |
| Legacy (`acme-retail`, etc.) | **Descartado** | N/A | Problema se reproduce en `tenant-test-*` creados post-Fase 1/2 |

**Conclusión analítica de la fase:** el branding incorrecto observado históricamente **no proviene de legacy**; es un **bug real de implementación** en el arranque de silos (supervisor/env) y en la UI de login (Vue hardcoded), mientras la capa de datos (env, mirror, Inertia props) funciona correctamente.

## Cambios realizados
- Ninguno. Fase de validación exclusivamente (sin correcciones; corresponden a Fase 5).

## Archivos modificados
- Ninguno.

## Archivos nuevos
- `docs/Plan_Desarrollo_Serviciov1.7/informe de cumplimiento por fase/Informe_Fase_4.md`

## Riesgos detectados
- **Identidad visual inconsistente entre capas**: operador ve `SaaS` en pestaña del navegador y potencialmente «Core Platform» en login, aunque el dashboard (AppLayout) recibiría el nombre correcto vía Inertia.
- **Cookies de sesión del CP en silos**: riesgo de colisión de sesión / comportamiento impredecible si CP y silo comparten `platform_session_platform`.
- **Falsa sensación de cumplimiento**: validar solo `ClientInstancePortalService` o props Inertia sin revisar `<title>` y `Login.vue` ocultaría el bug.

## Riesgos mitigados
- Se levantaron silos vía lifecycle `start` (como en operación real) antes de validar UI.
- Se validó con los tres `tenant-test-*` (no tenants prohibidos).
- Se demostró que `php artisan config:show` por `--env` difiere del `config()` en el proceso HTTP vivo → evidencia forense para Fase 5.

## Hallazgos clasificados

### Legacy
- Ninguno. Los fallos se reproducen en tenants creados desde cero tras limpieza total.

### Bug Real
1. **`APP_NAME` / `SESSION_COOKIE` no inyectados al worker del silo** (`LocalInstanceEnvironmentLoader` + `LocalFleetProcessSupervisor`) → `config('app.name')=SaaS` y cookies del CP en puertos de cliente.
2. **`Login.vue` hardcodea «Core Platform»** sin leer `instance.company_name`.

### Configuración
- `app.blade.php` acopla identidad visual del documento HTML a `config('app.name')` en lugar de `instance.company_name` / branding del portal.

### Operativo
- Procesos de silo arrancados por `lifecycle/start` heredan variables de entorno incompletas respecto al `.env.client-*` completo.

## Control de Alcance — Trabajo perteneciente a otras fases
| Trabajo fuera de alcance | Fase | Impacto |
|---|---|---|
| Corregir `LocalInstanceEnvironmentLoader` / `LocalFleetProcessSupervisor` | **Fase 5** | Arregla title/meta y cookies |
| Corregir `Login.vue` y/o `app.blade.php` | **Fase 5** | Arregla UI visible de login |
| Friendly routing | Fase 6 | N/A aquí |
| Elegibilidad simulación | Fase 7 | N/A aquí |

## Checklist del Runbook
| Requisito (Fase 4) | Estado | Evidencia |
|---|---|---|
| Validar `.env` `APP_NAME`, `PLATFORM_CLIENT_NAME`, `PLATFORM_CLIENT_SLUG` | Cumple | Select-String en los 3 `.env.client-*` |
| Validar tabla `tenants` del silo (slug, name, status) | Cumple | SQLite de los 3 silos |
| Validar UI login/dashboard del silo | **No cumple** | title=SaaS; Login.vue hardcoded; dashboard props OK pero title incorrecto |
| Validar props Inertia `company_name`, `company_slug` | Cumple | JSON en `/login` y `/dashboard` |
| Ningún silo temporal muestra `SaaS` | **No cumple** | `<title>SaaS</title>` en :8001-8003 |
| **CA**: Cada tenant muestra su identidad propia | **No cumple** | Falla en title/meta y Login.vue |
| **CA**: Si aparece SaaS, clasificar punto de fallo | Cumple | Supervisor/env (app.name) + frontend (Login.vue) |

## Compatibilidad Retroactiva
- **Lifecycle sigue funcionando**: `lifecycle/start` levantó los 3 silos (`/up` 200).
- **Provisioning sigue funcionando**: metadata moderna intacta; no se modificó código.
- **Routing sigue funcionando**: no evaluado en esta fase (Fase 6).
- **Simulación sigue funcionando**: no se ejecutaron runs; servicios intactos.
- **Control Plane sigue funcionando**: login y lifecycle operativos durante la validación.

## Acciones requeridas para Fase 5 (derivadas de esta validación)
1. Añadir `APP_NAME`, `SESSION_COOKIE`, `SESSION_XSRF_COOKIE` a `LocalInstanceEnvironmentLoader::CRITICAL_KEYS` (o inyectar env completo al worker).
2. Actualizar `Login.vue` para mostrar `usePage().props.instance?.company_name` con fallback razonable.
3. Evaluar `app.blade.php` para usar branding de instancia en `<title>` cuando `portal=instance`.
4. Re-validar con los mismos `tenant-test-*` tras corrección (sin data fix manual).

## Conclusión
La Fase 4 demostró con evidencia que la identidad por tenant **funciona en env, mirror, BD y props Inertia**, pero **falla en la capa visible del documento HTML** (`<title>`/meta = `SaaS`) y en **Login.vue** (`Core Platform` hardcoded). El problema es **bug real**, no legacy. El criterio de aceptación *«Cada tenant muestra su identidad propia»* **no se cumple** → **Estado = No cumple**.

No se avanza automáticamente a la Fase 5; se espera nueva instrucción.
