# Informe Fase 4 — Corrección autenticación

Estado: Cumple

## Objetivo
Restablecer login para operadores de tenants históricos en sus silos correctos. Validar que la aceptación y el rechazo de login funcionan correctamente en cada instancia de la flota local.

## Evidencia encontrada

### Estado inicial de datos (pre-corrección)

**Control Plane (platform.sqlite) — tenants:**
- `acme-retail` → id=`1d2574c2-ca8f-4914-a5bf-4c810989d2d3` (status=active)
- `pruebas-retail` → id=`5144267c-c9a3-41f2-98c9-89c936b61fa5` (status=active)
- `lifecycle-test` → id=`7aad06ec-fba2-4ef2-8a50-98f1c6b24d1e` (status=active)
- `platform` → id=`f3adf3d9-9174-455a-a2ee-f7b3b4c3afc1` (control plane slug)
- `retail-norte` → id=`854808ac-2117-4931-89f6-c322d9391c67` (sin silo, sin usuarios)
- `retail-sur` → id=`a7367fe7-3f06-4f4f-a7bd-a29b72456b30` (sin silo, sin usuarios)

**Silos y consistencia de tenant_id:**
- `acme-retail` silo: `admin@local` → tenant_id=`17ef426e-...` ✓ coincide con slug `acme-retail`
- `pruebas-retail` silo: `prueba@prueba` → tenant_id=`1f3ea3ae-...` ✓ coincide con slug `pruebas-retail`
- `lifecycle-test` silo: `lifecycle@local` → tenant_id=`4446c69a-...` ✓ coincide con slug `lifecycle-test`

**PLATFORM_CLIENT_SLUG vs slug en silo:**
- `.env.client-acme-retail`: `PLATFORM_CLIENT_SLUG=acme-retail` ✓
- `.env.client-pruebas-retail`: `PLATFORM_CLIENT_SLUG=pruebas-retail` ✓
- `.env.client-lifecycle-test`: `PLATFORM_CLIENT_SLUG=lifecycle-test` ✓

### Bloqueante detectado: contraseñas legacy

`ControlPlaneFleetBootstrapService::importLegacyTenant` copió el hash de contraseña de `database/database.sqlite` (legacy DB) directamente al CP durante Fase 2. Este hash no correspondía a `client-local-dev` (la contraseña documentada en `fleet-registry.json`).

`LocalFleetTenantMirror::syncOperators` propagó ese hash del CP a los silos de `acme-retail` y `pruebas-retail`. El silo `lifecycle-test` no fue afectado porque su usuario fue creado mediante el flujo de provisioning completo (Fase 3), que genera el hash correcto.

Verificación de contraseñas ANTES de corrección:
- `admin@local` en CP: `password_verify('client-local-dev') = FAIL`
- `prueba@prueba` en CP: `password_verify('client-local-dev') = FAIL`
- `lifecycle@local` en CP: `password_verify('client-local-dev') = OK`

### Test HTTP inicial (pre-corrección)

```
[FAIL] SILO admin@local → 8001 (acme)    HTTP=302 → http://127.0.0.1:8001/login
[FAIL] SILO prueba@prueba → 8002 (pruebas) HTTP=302 → http://127.0.0.1:8002/login
[PASS] SILO lifecycle@local → 8003 (lc)  HTTP=302 → http://127.0.0.1:8003/dashboard
```

## Cambios realizados

### Corrección aplicada: reset de contraseñas y re-sync de mirror

**Contexto:** las contraseñas legacy importadas desde `database/database.sqlite` no coincidían con `client-local-dev`, que es la contraseña de acceso documentada en `deploy/local-instances/fleet-registry.json` para todos los silos locales de desarrollo.

**Acción 1:** Se restablecieron las contraseñas en la CP para `admin@local` y `prueba@prueba` al hash de `client-local-dev` via `password_hash(..., PASSWORD_BCRYPT, ['cost' => 12])`.

**Acción 2:** Se propagaron los nuevos hashes desde el CP a cada silo (`acme-retail.sqlite`, `pruebas-retail.sqlite`) mediante la misma lógica del mirror (`UPDATE users SET password = ... WHERE email = ...`).

**No se modificó código.** Esta es una corrección de datos operativos: el legacy import copió un hash incorrecto; el fix alinea los hashes con las credenciales documentadas de desarrollo local.

**Verificación tras corrección:**
- CP `admin@local`: `password_verify('client-local-dev') = OK`
- CP `prueba@prueba`: `password_verify('client-local-dev') = OK`
- Silo acme-retail `admin@local`: `password_verify('client-local-dev') = OK`
- Silo pruebas-retail `prueba@prueba`: `password_verify('client-local-dev') = OK`

### Validación HTTP final

```
[PASS] CP   saas@local → 8000 (CP)             HTTP=302 → http://127.0.0.1:8000/control/overview
[PASS] SILO admin@local → 8001 (acme)           HTTP=302 → http://127.0.0.1:8001/dashboard
[PASS] SILO prueba@prueba → 8002 (pruebas)      HTTP=302 → http://127.0.0.1:8002/dashboard
[PASS] SILO lifecycle@local → 8003 (lc)         HTTP=302 → http://127.0.0.1:8003/dashboard
[PASS] REJ  admin@local → 8002 (silo incorrecto) HTTP=302 → http://127.0.0.1:8002/login
[PASS] REJ  saas@local → 8001 (saas en silo)    HTTP=302 → http://127.0.0.1:8001/login
[PASS] REJ  contraseña incorrecta → 8001         HTTP=302 → http://127.0.0.1:8001/login

OVERALL: ALL PASS
```

## Archivos modificados

- `database/instances/platform.sqlite` — contraseñas de `admin@local` y `prueba@prueba` restablecidas a hash(`client-local-dev`)
- `database/instances/acme-retail.sqlite` — contraseña de `admin@local` sincronizada desde CP
- `database/instances/pruebas-retail.sqlite` — contraseña de `prueba@prueba` sincronizada desde CP

## Archivos nuevos

Ninguno (los scripts temporales `_f4_*.php` fueron eliminados tras su uso).

## Riesgos detectados

1. **Tenants huérfanos:** `retail-norte` y `retail-sur` existen en CP sin silo ni usuarios. No generan errores de login (no hay usuarios para ellos), pero representan datos de referencia sin instancia activa. Corresponde a limpieza de datos, no a Fase 4.

2. **Legacy import copia hashes estáticos:** `ControlPlaneFleetBootstrapService::importLegacyTenant` no actualiza contraseñas a valores documentados. Si se vuelve a ejecutar con el mismo `database.sqlite`, reintroduciría hashes legacy. Esto se registra como riesgo latente.

3. **Múltiples procesos por puerto:** Durante la validación, se detectaron múltiples procesos PHP escuchando en el mismo puerto (8000, 8003) causados por ejecuciones previas de `instances:serve` y Fase 3. Se resolvió matando los procesos duplicados antes de continuar.

## Riesgos mitigados

- Tenant_id correctamente asignado en todos los usuarios de silos activos: verificado.
- PLATFORM_CLIENT_SLUG coincide con slug en silo para todos los silos activos: verificado.
- Mirror propagó contraseñas actualizadas correctamente.
- AuthenticateOperatorUseCase rechaza correctamente: usuario de otro silo (wrong tenant_id match), saas_admin en silo, contraseña incorrecta.

## Hallazgos fuera de alcance

1. **`ControlPlaneFleetBootstrapService::importLegacyTenant` debería normalizar contraseñas** al importar usuarios legacy en lugar de copiar hashes. Esto corresponde a una mejora en el flujo de fleet-bootstrap. Se registra para Fase 9 o backlog.

2. **`retail-norte` y `retail-sur`** son tenants sin silo ni operadores. Podrían limpiarse del CP. Corresponde a Fase 0 (baseline) si se detecta necesidad de re-ejecución.

## Checklist Runbook

| Requisito | Estado | Evidencia |
|---|---|---|
| `tenant_id` en users vs tenants consistente | Cumple | Verificado en todos los silos: IDs coinciden con slug=PLATFORM_CLIENT_SLUG |
| Mirror ejecutado post-cleanup | Cumple | `platform:fleet:sync-local` ejecutado; mirror confirmó skip (ya provisionados) |
| PLATFORM_CLIENT_SLUG correcto en cada silo | Cumple | `.env.client-*` verificados; slug coincide con tenant en silo DB |
| Operadores legacy autenticados en silo correcto | Cumple | `admin@local`→8001, `prueba@prueba`→8002, `lifecycle@local`→8003: todos redirigen a `/dashboard` |
| Rechazo de operador en silo incorrecto | Cumple | `admin@local`→8002: HTTP 302 a `/login` (sesión no iniciada) |
| saas_admin rechazado en silo | Cumple | `saas@local`→8001: HTTP 302 a `/login` |
| saas_admin autenticado en control plane | Cumple | `saas@local`→8000: HTTP 302 a `/control/overview` |

## Compatibilidad Retroactiva

- Lifecycle: no se tocaron `LocalFleetProcessSupervisor`, `LocalFleetInstanceProvisioner`, `TenantLifecycleOrchestrator`, ni ningún use case de lifecycle.
- Provisioning: el flujo de `provision` en `LocalFleetInstanceProvisioner` no fue modificado.
- Fleet/registry: `fleet-registry.json` no fue modificado.
- Control plane: no se alteraron rutas, middleware ni configuración de autenticación.
- La corrección fue exclusivamente a datos operativos (hashes de contraseñas) para alinear con la documentación de desarrollo local (`fleet-registry.json` → `adminPassword: "client-local-dev"`).
