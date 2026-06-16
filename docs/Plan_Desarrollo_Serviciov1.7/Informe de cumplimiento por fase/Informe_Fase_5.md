# Informe Fase 5 — Corrección Tenant Identity

## Estado
**Cumple**

## Objetivo
Corregir los fallos de identidad demostrados en Fase 4 (bug real en arranque de silos y frontend), de modo que cada `tenant-test-*` muestre su identidad propia en todas las capas validadas, sin data fix manual, tras reinicio del worker con el código corregido.

## Evidencia encontrada

### Hallazgos heredados de Fase 4 (punto de partida)
| Capa | Estado pre-corrección | Clasificación |
|---|---|---|
| `.env` / mirror / BD / props Inertia | Correcto | OK |
| `<title>` / `<meta app-name>` en silos | `SaaS` | Bug real |
| Cookies de sesión en silo | `platform_session_platform` (CP) | Bug real + Operativo |
| `Login.vue` título visual | `"Core Platform"` hardcoded | Bug real |

### Causa raíz confirmada
1. **`LocalInstanceEnvironmentLoader::CRITICAL_KEYS`** no incluía `APP_NAME`, `SESSION_COOKIE`, `SESSION_XSRF_COOKIE` → el worker hijo heredaba contexto del CP al arrancar vía `LocalFleetProcessSupervisor`.
2. **`app.blade.php`** usaba `config('app.name')` para el documento HTML en todos los portales.
3. **`Login.vue`** no leía `page.props.instance.company_name`.

### Tenants de certificación (solo `tenant-test-*` post-limpieza)
| Slug | Puerto | Nombre esperado | Operador |
|---|---|---|---|
| `tenant-test-branding` | 8001 | Tenant Test Branding Co | `branding@tenant-test.local` |
| `tenant-test-routing` | 8002 | Tenant Test Routing Co | `routing@tenant-test.local` |
| `tenant-test-simulation` | 8003 | Tenant Test Simulation Co | `simulation@tenant-test.local` |

### Matriz de validación post-corrección

| Capa | branding | routing | simulation | Resultado |
|---|---|---|---|---|
| `.env` `APP_NAME` / `PLATFORM_CLIENT_NAME` | ✓ | ✓ | ✓ | Cumple |
| Silo `tenants` (slug, name, status) | ✓ active | ✓ active | ✓ active | Cumple |
| Inertia `/login` `instance.company_name` | ✓ | ✓ | ✓ | Cumple |
| HTML `<title>` / `<meta app-name>` | ✓ nombre tenant | ✓ | ✓ | Cumple |
| Cookie sesión HTTP | `platform_session_tenant_test_branding` | `..._routing` | `..._simulation` | Cumple |
| Cookie XSRF HTTP | `platform_xsrf_tenant_test_branding` | `..._routing` | `..._simulation` | Cumple |
| Inertia `/dashboard` `company_name` (tras login) | ✓ | ✓ | ✓ | Cumple |
| Dashboard `<title>` | ✓ nombre tenant | ✓ | ✓ | Cumple |
| Ausencia de `SaaS` como identidad en silo | ✓ | ✓ | ✓ | Cumple |
| `Login.vue` usa `instance.company_name` | ✓ (código) | ✓ | ✓ | Cumple |
| CP `:8000` mantiene `SaaS` | ✓ | N/A | N/A | Cumple |

### Evidencia HTTP (extractos)

**Control Plane `GET :8000/login`:**
```html
<meta name="app-name" content="SaaS">
<title>SaaS</title>
```

**Silo `tenant-test-branding GET :8001/login`:**
```html
<meta name="app-name" content="Tenant Test Branding Co">
<title>Tenant Test Branding Co</title>
```
```json
"instance": {"company_name": "Tenant Test Branding Co", "company_slug": "tenant-test-branding"}
```
Cookie: `platform_session_tenant_test_branding`, `platform_xsrf_tenant_test_branding`

**Dashboard tras login (los 3 silos):**
```
port=8001 company=Tenant Test Branding Co title=Tenant Test Branding Co component=Dashboard/Index status=PASS
port=8002 company=Tenant Test Routing Co title=Tenant Test Routing Co component=Dashboard/Index status=PASS
port=8003 company=Tenant Test Simulation Co title=Tenant Test Simulation Co component=Dashboard/Index status=PASS
```

**Health checks:**
```
cp_up=200  b8001=200  b8002=200  b8003=200
```

**Prueba unitaria:**
```
Tests\Unit\Platform\LocalInstanceEnvironmentLoaderTest — PASS (6 assertions)
```

## Cambios realizados
1. Ampliar `CRITICAL_KEYS` en `LocalInstanceEnvironmentLoader` con `APP_NAME`, `SESSION_COOKIE`, `SESSION_XSRF_COOKIE` para inyectar identidad y cookies al worker del silo.
2. Actualizar `app.blade.php` para usar `platform.client_name` en portales `instance` y `app.name` solo en control plane.
3. Actualizar `Login.vue` para mostrar `page.props.instance?.company_name` e inicial dinámica (patrón alineado con `AppLayout.vue`).
4. Actualizar `LocalInstanceEnvironmentLoaderTest` para validar las nuevas claves críticas con `client-tenant-test-branding`.
5. Recompilar assets frontend (`npm run build`) para desplegar cambios de `Login.vue`.
6. Reiniciar silos (`lifecycle` reset `provisioned` → `start`) y CP (`php artisan serve --env=control-plane --port=8000`) para cargar código y env corregidos — **sin modificación manual de datos de tenant**.

## Archivos modificados
- `app/Shared/Platform/LocalInstanceEnvironmentLoader.php`
- `resources/views/app.blade.php`
- `resources/js/Pages/Auth/Login.vue`
- `tests/Unit/Platform/LocalInstanceEnvironmentLoaderTest.php`
- `public/build/**` (artefactos Vite generados por `npm run build`)

## Archivos nuevos
- `docs/Plan_Desarrollo_Serviciov1.7/informe de cumplimiento por fase/Informe_Fase_5.md`

## Riesgos detectados
- **Contaminación transitoria del CP**: al reiniciar servicios en la misma sesión de shell, el proceso en `:8000` mostró brevemente el título de un silo (`Tenant Test Simulation Co`) hasta reiniciar CP con `--env=control-plane` explícito.
- **Desincronización lifecycle/proceso**: si `lifecycle=running` en BD pero el proceso HTTP murió, `start` idempotente no re-spawnea (patrón ya documentado en fases anteriores).
- **Fallback `Core Platform` en `Login.vue`**: permanece solo cuando `instance` es `null` (portal control); en silos `instance` siempre está presente.

## Riesgos mitigados
- Identidad HTML (`title`/meta) ya no depende de `app.name` heredado del CP en silos.
- Cookies de sesión/XSRF son exclusivas por tenant en procesos HTTP de silo.
- UI de login refleja `company_name` de Inertia en portales `instance`.
- CP restaurado con título `SaaS` tras reinicio limpio.

## Hallazgos clasificados

### Legacy
- Ninguno. Los fallos corregidos se reproducían en `tenant-test-*` creados post-Fase 1/2; no provienen de tenants históricos.

### Bug Real (corregidos)
1. `CRITICAL_KEYS` incompleto → `app.name` y cookies del CP en workers de silo.
2. `app.blade.php` acoplado a `config('app.name')` sin distinguir portal.
3. `Login.vue` con nombre visual hardcoded.

### Configuración
- Ninguna pendiente. Los `.env.client-*` ya contenían valores correctos desde Fase 3; el fallo era de inyección al worker, no de generación en `LocalFleetEnvBuilder`.

### Operativo
1. Contaminación de env del CP al arrancar `serve` sin `--env=control-plane` en shell con contexto previo — mitigado con reinicio explícito.
2. Necesidad de resetear `lifecycle` a `provisioned` antes de `start` cuando procesos murieron sin actualizar BD.

## Control de Alcance — Trabajo perteneciente a otras fases
| Trabajo fuera de alcance | Fase | Impacto |
|---|---|---|
| Friendly routing (`PLATFORM_FRIENDLY_ROUTING`) | Fase 6 | No evaluado |
| Elegibilidad de simulación / módulos | Fase 7 | No evaluado |
| Pruebas integrales end-to-end | Fase 8 | No evaluado |
| Limpieza final de artefactos runtime | Fase 9 | No evaluado |

## Checklist del Runbook
| Requisito (Fase 5) | Estado | Evidencia |
|---|---|---|
| Corregir fallo de env/bootstrap si aplica | Cumple | `LocalInstanceEnvironmentLoader::CRITICAL_KEYS` ampliado |
| Corregir frontend que renderiza nombre visual | Cumple | `Login.vue` + `app.blade.php` |
| No usar data fix manual como solución | Cumple | Solo código + reinicio de workers |
| Tenant temporal muestra identidad correcta | Cumple | Matriz 3/3 en login y dashboard |
| Sin `SaaS` como identidad en silos temporales | Cumple | Titles/meta en :8001-8003 |
| Cookies de sesión por tenant | Cumple | `platform_session_tenant_test_*` en HTTP |
| **CA**: Identidad correcta tras corrección sin data fix | Cumple | Validación HTTP + unit test |

## Compatibilidad Retroactiva
- **Lifecycle sigue funcionando**: silos reiniciados vía `StartTenantServiceUseCase`; `/up` 200 en CP y los 3 silos.
- **Provisioning sigue funcionando**: metadata moderna intacta en CP, registry y `.env`; no se alteraron filas de tenant manualmente.
- **Routing sigue funcionando**: no evaluado en esta fase; servicios HTTP operativos en puertos asignados.
- **Simulación sigue funcionando**: props `simulation` presentes en Inertia; no se ejecutaron runs (Fase 7).
- **Control Plane sigue funcionando**: login y `/up` en `:8000` con identidad `SaaS` correcta.

## Conclusión
La Fase 5 corrige los tres puntos de fallo identificados en Fase 4 (env loader, blade, Login.vue). Los tres `tenant-test-*` muestran identidad propia en env, cookies, props Inertia, `<title>`/meta y dashboard tras login. No se aplicó data fix manual. **Estado = Cumple**.

No se avanza automáticamente a la Fase 6; se espera nueva instrucción.
