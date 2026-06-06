# Informe Fase 3 — Provisioning moderno desde cero

## Estado
**Cumple**

## Objetivo
Crear tenants temporales exclusivamente mediante provisioning moderno (`POST /control/provisioning`), demostrando que cada uno recibe metadata moderna completa (fila en CP, operador, registry, `.env`, SQLite, `settings.deployment.local_instance.app_url`, `settings.deployment.lifecycle`) y que ningún tenant histórico reaparece.

## Evidencia encontrada

### Componentes utilizados (flujo moderno)
- `ProvisioningController::store()` → `ProvisionNewTenantService::provision()`
- `LocalFleetInstanceProvisioner::provision()` (auto-provision con `PLATFORM_LOCAL_FLEET_AUTO_PROVISION=true`)
- `LocalFleetEnvBuilder`, `LocalFleetRegistry`, `LocalFleetTenantMirror`
- Catálogo comercial: `config/saas_catalog.php` (plan `starter`, módulo `middleware`)

### Tenants creados (vía `POST /control/provisioning`, autenticado como `saas@local`)

| Slug | Nombre visual | ID (CP) | Puerto | Operador | Resultado HTTP |
|---|---|---|---|---|---|
| `tenant-test-branding` | Tenant Test Branding Co | `d56f169c-3182-4939-870b-fc59d6de9485` | 8001 | `branding@tenant-test.local` | 302 → `/control/companies/d56f169c-...` |
| `tenant-test-routing` | Tenant Test Routing Co | `accac371-2437-4bd6-8d4d-1341df85286b` | 8002 | `routing@tenant-test.local` | 302 → `/control/companies/accac371-...` |
| `tenant-test-simulation` | Tenant Test Simulation Co | `cb554dfb-6f2b-4bc9-b5e8-b36d62c90742` | 8003 | `simulation@tenant-test.local` | 302 → `/control/companies/cb554dfb-...` |

### Validación de metadata moderna por tenant

Para cada `tenant-test-*` se verificó:

| Requisito | branding | routing | simulation |
|---|---|---|---|
| Fila en CP (`tenants`) | ✓ | ✓ | ✓ |
| Operador (`users`, `platform_admin`) | ✓ | ✓ | ✓ |
| Entrada en `fleet-registry.json` | ✓ `:8001` | ✓ `:8002` | ✓ `:8003` |
| `.env.client-*` | ✓ | ✓ | ✓ |
| SQLite (`database/instances/*.sqlite`) | ✓ 38 tablas, integrity ok | ✓ | ✓ |
| `settings.deployment.local_instance.app_url` | ✓ `http://127.0.0.1:8001` | ✓ `:8002` | ✓ `:8003` |
| `settings.deployment.lifecycle` | ✓ `provisioned` | ✓ `provisioned` | ✓ `provisioned` |
| `APP_NAME` / `PLATFORM_CLIENT_NAME` en `.env` | ✓ nombre visual único | ✓ | ✓ |
| `PLATFORM_CLIENT_SLUG` en `.env` | ✓ | ✓ | ✓ |
| Fila `tenants` en silo (mirror) | ✓ slug+name correctos | ✓ | ✓ |
| Operador en silo (mirror) | ✓ | ✓ | ✓ |
| `config/modules/instances/{slug}/modules_config.json` | ✓ | ✓ | ✓ |

### Verificación de no reintroducción legacy
- Tenants prohibidos en CP: **NONE** (`acme-retail`, `pruebas-retail`, `lifecycle-test`, `retail-norte`, `retail-sur`, `unaprueba`).
- `CompanyListingService::tenantsForIndex()`: 4 filas (`platform` + 3 `tenant-test-*`); legacy en listing: **NONE**.
- `simulation_runs`: **0**.

### Verificación operativa post-provisioning
- Silos **no** auto-levantados: puertos `8001-8003` LISTENING = **0** (provisioning crea artefactos, no inicia procesos HTTP; lifecycle `start` es Fase posterior).
- Control Plane operativo durante validación: `GET /up` → **200**.
- Rutas de lifecycle/provisioning/companies registradas (`route:list`).

### Archivos generados
- `.env.client-tenant-test-branding`, `.env.client-tenant-test-routing`, `.env.client-tenant-test-simulation`
- `database/instances/tenant-test-branding.sqlite`, `tenant-test-routing.sqlite`, `tenant-test-simulation.sqlite`
- `config/modules/instances/tenant-test-{branding,routing,simulation}/modules_config.json`
- `deploy/local-instances/fleet-registry.json` (3 instancias cliente)
- `database/instances/platform.sqlite` (actualizado con tenants y operadores)

## Cambios realizados
1. Levantamiento del Control Plane (`php artisan serve --port=8000`).
2. Autenticación como `saas@local` y tres llamadas `POST /control/provisioning` (flujo moderno del Runbook).
3. Validación exhaustiva de metadata, artefactos, mirror de silos y ausencia de legacy.
4. Detención del CP y checkpoint WAL de `platform.sqlite`.

## Archivos modificados
- `deploy/local-instances/fleet-registry.json`
- `database/instances/platform.sqlite`
- `storage/logs/laravel.log` (actividad de provisioning)

## Archivos nuevos
- `.env.client-tenant-test-branding`
- `.env.client-tenant-test-routing`
- `.env.client-tenant-test-simulation`
- `database/instances/tenant-test-branding.sqlite`
- `database/instances/tenant-test-routing.sqlite`
- `database/instances/tenant-test-simulation.sqlite`
- `config/modules/instances/tenant-test-branding/modules_config.json`
- `config/modules/instances/tenant-test-routing/modules_config.json`
- `config/modules/instances/tenant-test-simulation/modules_config.json`
- `docs/Plan_Desarrollo_Serviciov1.7/informe de cumplimiento por fase/Informe_Fase_3.md`

## Riesgos detectados
- **Provisioning requiere autenticación**: `POST /control/provisioning` sin sesión redirige a `/login` (302). Mitigado con login previo y cookie jar persistente.
- **Duración del provisioning**: cada tenant ejecuta migrate + bootstrap + mirror (~5s cada uno). Riesgo de timeout en entornos lentos; no ocurrió en esta ejecución.
- **`TenantLifecyclePolicy::inferLifecycle()` para `platform`**: sin bloque `deployment`, infiere `provisioned` en el listado (presentación, no metadata real del CP). Comportamiento pre-existente; no afecta los `tenant-test-*`.

## Riesgos mitigados
- Se usó exclusivamente el endpoint `/control/provisioning` (no `instances:fleet-bootstrap` ni import legacy).
- Cada tenant validado individualmente contra los 8 requisitos de metadata del Runbook.
- Verificación explícita de que ningún slug legacy reapareció.

## Hallazgos clasificados

### Legacy
- Ningún tenant histórico reapareció tras el provisioning (verificado en CP y en listado de empresas).

### Bug Real
- Ninguno detectado en esta fase. Los tres tenants `tenant-test-*` recibieron metadata moderna completa desde el primer provisioning.

### Configuración
- Ninguna incorrecta que impidiera el provisioning. `PLATFORM_LOCAL_FLEET_AUTO_PROVISION=true` en `.env.control-plane` habilitó la creación automática de silos.

### Operativo
- Primer intento de provisioning falló por sesión no autenticada (HTTP 302→login / HTTP 000). Resuelto con flujo login→provision en cookie jar único.

## Control de Alcance — Trabajo perteneciente a otras fases
| Trabajo fuera de alcance | Descripción | Fase correspondiente | Impacto | Recomendación |
|---|---|---|---|---|
| Levantar silos (`lifecycle/start`) | Provisioning deja `lifecycle=provisioned`, no `running` | Fases 4-8 (lifecycle) | Silos no escuchan en 8001-8003 hasta start | Ejecutar start antes de validar login/routing en fases siguientes |
| Validar branding en UI de silo | Solo se verificó env + fila tenant en silo | Fase 4 | Ninguno para Fase 3 | Validar UI en Fase 4 |
| Configurar módulos explícitos para simulación | Tenants creados solo con `middleware` | Fase 7 | `tenant-test-simulation` aún no es simulable | Configurar `modules_catalog` en Fase 7 |
| Activar `PLATFORM_FRIENDLY_ROUTING` | Flag ausente en CP | Fase 6 | Routing amigable no certificable aún | Activar en Fase 6 |

## Checklist del Runbook
| Requisito (Fase 3) | Estado | Evidencia |
|---|---|---|
| Crear `tenant-test-branding` con nombre visual único | Cumple | POST provisioning → 302; name=`Tenant Test Branding Co` |
| Crear `tenant-test-routing` con nombre visual único | Cumple | POST provisioning → 302; name=`Tenant Test Routing Co` |
| Crear `tenant-test-simulation` con nombre visual único | Cumple | POST provisioning → 302; name=`Tenant Test Simulation Co` |
| Fila en CP | Cumple | 3 filas en `tenants` + verificación por slug |
| Operador | Cumple | 3 usuarios `platform_admin` con emails únicos |
| Entrada registry | Cumple | `fleet-registry.json` con 3 instancias, puertos 8001-8003 |
| `.env.client-*` | Cumple | 3 archivos verificados con APP_NAME/PLATFORM_CLIENT_* |
| SQLite | Cumple | 3 archivos, integrity ok, 38 tablas cada uno |
| `settings.deployment.local_instance.app_url` | Cumple | Presente en settings de cada tenant en CP |
| `settings.deployment.lifecycle` | Cumple | `provisioned` en los 3 |
| **CA**: Todos los tenants nuevos tienen metadata moderna | Cumple | Tabla de validación completa arriba |
| **CA**: Ningún tenant histórico reaparece | Cumple | Legacy check: NONE |

## Compatibilidad Retroactiva
- **Lifecycle sigue funcionando**: endpoints `start/suspend/restore/status` registrados; los 3 tenants tienen `local_instance` y `lifecycle=provisioned`, listos para `start` en fases posteriores.
- **Provisioning sigue funcionando**: flujo `POST /control/provisioning` → `ProvisionNewTenantService` → `LocalFleetInstanceProvisioner` demostrado con 3 tenants exitosos.
- **Routing sigue funcionando**: rutas de control y portal intactas; cada tenant tiene `app_url` para redirect en Fase 6.
- **Simulación sigue funcionando**: servicios sin cambios; `simulation_runs=0`; elegibilidad se evaluará en Fase 7 con `tenant-test-simulation`.
- **Control Plane sigue funcionando**: `/up` 200, login y provisioning operativos; `platform.sqlite` integrity ok tras checkpoint WAL.

## Conclusión
Los tres tenants `tenant-test-*` fueron creados exclusivamente mediante provisioning moderno desde `/control/provisioning`, con metadata moderna completa y sin reintroducción de legacy. Todos los criterios de aceptación de la Fase 3 del Runbook v1.7 están cumplidos.

**Estado = Cumple.** No se avanza automáticamente a la Fase 4; se espera nueva instrucción.
