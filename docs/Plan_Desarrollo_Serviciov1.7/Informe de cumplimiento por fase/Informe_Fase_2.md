# Informe Fase 2 — Reconstrucción limpia

## Estado
**Cumple con observaciones**

> Observación principal: todos los criterios de aceptación formales del Runbook se cumplen (Control Plane operativo, cero tenants comerciales, cero silos, cero simulaciones). La única observación es una **desviación documentada** entre una acción literal del Runbook ("Verificar que `/control/companies` no muestra tenants") y el estado real (el panel muestra el self-tenant técnico `platform`, que el propio Runbook §6 permite conservar como identidad del CP). No es legacy ni regresión.

## Objetivo
Levantar únicamente el Control Plane y la configuración base sin datos legacy, demostrando que una reconstrucción desde cero produce un entorno reproducible con cero tenants comerciales, cero silos y cero simulaciones, sin ejecutar el import legacy (`instances:fleet-bootstrap`).

## Evidencia encontrada

### Componentes y configuración analizados
- `scripts/local-instances/bootstrap.mjs` y `scripts/local-instances/lib.mjs`: el bootstrap base (`instances:bootstrap`) con `fleet-registry.json` vacío solo reconstruye el Control Plane (`migrate` + `db:seed`). El import legacy vive en un comando separado (`instances:fleet-bootstrap` → `platform:fleet:bootstrap-control-plane --provision`), que **no** se ejecutó.
- `deploy/local-instances/instances.json`: define únicamente la instancia estática `control-plane` (slug `platform`, puerto 8000, role `control_plane`).
- `database/seeders/DatabaseSeeder.php`: orquesta `InstanceTenantSeeder`, `MiddlewareDatabaseSeeder`, `PlatformOperatorSeeder`, `SaasOperatorSeeder`. Ninguno importa tenants legacy.
- `database/seeders/InstanceTenantSeeder.php`: para CP (`control_plane=true`) hace upsert del tenant `platform`; el prune de tenants ajenos solo aplica a silos cliente, no al CP.
- `database/seeders/SaasOperatorSeeder.php`: crea/actualiza `saas@local` (saas_admin, sin tenant_id).
- `database/seeders/PlatformOperatorSeeder.php`: omitido (`PLATFORM_SEED_ADMIN_OPERATOR=false` en `.env.control-plane`).
- `app/Control/Application/Services/Tenants/CompanyListingService.php` y `TenantPresentationService.php`: `listTenants()` devuelve **todos** los tenants, incluido `platform` (no filtra el self-tenant del CP).

### Resultado de la reconstrucción (BD `platform.sqlite`)
- `PRAGMA integrity_check`: **ok**.
- Tenants (1): `platform | Mi empresa (SaaS / control plane) | active`.
- Usuarios (1): `saas@local | saas_admin | (sin tenant_id)`.
- `simulation_runs`: 0.

### Verificación HTTP del Control Plane
- `GET /up` → **200**.
- `GET /login` → **200**.
- `POST /login` (`saas@local` / `saas-local-dev`) → **302 → /control/overview** (autenticación correcta).
- `GET /control/companies` (autenticado) → **200**.
- `GET /control/overview` (autenticado) → **200**.
- `GET /control/companies` (anónimo) → **302 → /login** (middleware de auth correcto).
- `CompanyListingService::tenantsForIndex()` → 1 fila: `platform` (self-tenant del CP). **0 tenants comerciales.**

### Verificación de ausencia de silos
- Puertos `127.0.0.1:8001-8009` LISTENING: **0**.
- `.env.client-*`: **0**.
- SQLite de tenant (distintos de `platform.sqlite`): **0**.
- `config/modules/instances/`: **0** elementos.
- `deploy/local-instances/fleet-registry.json`: `instances: []`.

### Autenticación
- `saas@local` existe, role `saas_admin`, `Hash::check('saas-local-dev')` → **YES**.

## Cambios realizados
1. Reconstrucción limpia de la BD del Control Plane sin import legacy:
   - Intento inicial `php artisan migrate:fresh --seed --env=control-plane` falló con `SQLSTATE[HY000]: database disk image is malformed` tras el "Dropping all tables", por un estado WAL inconsistente heredado del cierre forzado de procesos en Fase 1.
   - Mitigación (greenfield real): eliminación del `platform.sqlite` malformado, recreación de archivo vacío y `php artisan migrate --seed --force --env=control-plane`.
2. Levantamiento del CP (`php artisan serve --port=8000`) para validación HTTP y posterior detención (proceso padre + hijo `php`).
3. Consolidación del WAL de `platform.sqlite` (`PRAGMA wal_checkpoint(TRUNCATE)`) para dejar la BD sin `-wal`/`-shm`.

## Archivos modificados
- `database/instances/platform.sqlite` (recreado desde cero: esquema migrado + seed base).

## Archivos nuevos
- `docs/Plan_Desarrollo_Serviciov1.7/informe de cumplimiento por fase/Informe_Fase_2.md`

## Archivos eliminados
- `database/instances/platform.sqlite` malformado (sustituido por el reconstruido) y sus `-wal`/`-shm` transitorios.

## Riesgos detectados
- **`database disk image is malformed`** al ejecutar `migrate:fresh`: causado por residuos del modo WAL tras cierres forzados de procesos en Fase 1. Riesgo de corrupción de la BD base durante la reconstrucción.
- **Procesos hijo de `php artisan serve`**: al detener el proceso padre quedó un proceso `php` hijo sirviendo el puerto 8000.
- **Drift de `.env.control-plane`**: no contiene `PLATFORM_FRIENDLY_ROUTING` (corresponde a Fase 6).
- **`/control/companies` lista el self-tenant `platform`**: comportamiento de presentación pre-existente que podría confundir el criterio "cero tenants comerciales".

## Riesgos mitigados
- BD reconstruida desde archivo vacío con esquema y seed base; integridad verificada `ok`.
- Procesos del CP detenidos (padre e hijo); `0` puertos `80xx` escuchando al cierre de la fase.
- WAL consolidado: `platform.sqlite` queda sin `-wal`/`-shm`.
- Se confirmó que el bootstrap base **no** reintroduce legacy (import legacy no ejecutado; seeders verificados).

## Hallazgos clasificados

### Legacy
- Ningún tenant/silo/artefacto legacy reapareció tras la reconstrucción (verificado: solo `platform` + `saas@local`).

### Bug Real
- Ninguno. El error `database disk image is malformed` **no** es un bug de implementación: es un artefacto operativo del estado WAL tras cierres forzados. No se reproduce en una reconstrucción desde archivo vacío.

### Configuración
- `.env.control-plane` sin `PLATFORM_FRIENDLY_ROUTING` (se atenderá en Fase 6).
- `TenantPresentationService::listTenants()` / `CompanyListingService` no excluyen el self-tenant `platform` del listado de empresas (comportamiento pre-existente).

### Operativo
- WAL inconsistente en `platform.sqlite` tras kill forzado (resuelto recreando el archivo).
- Proceso hijo de `php artisan serve` persistente en `:8000` (resuelto con `taskkill` del hijo).

## Control de Alcance — Trabajo perteneciente a otras fases
| Trabajo fuera de alcance | Descripción | Fase correspondiente | Impacto | Recomendación |
|---|---|---|---|---|
| Filtrar `platform` del listado `/control/companies` | El panel muestra el self-tenant del CP | Baseline / UI (no asignada) | Cosmético; no afecta "cero comerciales" | Evaluar si el CP debe ocultar su propio tenant del panel de empresas; no modificar en Fase 2 |
| Añadir `PLATFORM_FRIENDLY_ROUTING=true` a `.env.control-plane` | Flag de routing amigable ausente | Fase 6 | Routing amigable retorna 404 sin el flag | Activar y verificar en Fase 6 |
| Limpieza/refactor del import legacy (`ControlPlaneFleetBootstrapService`, `database/database.sqlite`) | Fuente de re-contaminación legacy | Problema 6 / Fase 10 | Solo si se ejecuta `fleet-bootstrap` | No ejecutar import legacy durante v1.7 |

No se realizaron cambios fuera del alcance de la Fase 2.

## Checklist del Runbook
| Requisito (Fase 2) | Estado | Evidencia |
|---|---|---|
| Ejecutar bootstrap base sin import legacy | Cumple | `migrate --seed --env=control-plane`; `instances:fleet-bootstrap` NO ejecutado; seeders sin legacy. |
| Verificar que el CP responde | Cumple | `/up` 200, `/login` 200, login 302→`/control/overview`, paneles 200. |
| Verificar que `/control/companies` no muestra tenants | Cumple con observación | Muestra solo el self-tenant `platform` (identidad del CP). **0 tenants comerciales**. Desviación documentada. |
| Verificar que no se crearon silos automáticamente | Cumple | 0 puertos 8001+, 0 `.env.client-*`, 0 SQLite de tenant, 0 catálogos. |
| Verificar que `fleet-registry.json` sigue sin clientes | Cumple | `instances: []`. |
| **CA**: Control Plane operativo | Cumple | Login y paneles 200; integridad BD `ok`. |
| **CA**: Cero tenants comerciales | Cumple | Solo `platform` (self-tenant, no comercial). |
| **CA**: Cero silos | Cumple | Sin puertos/envs/SQLite/catálogos de cliente. |
| **CA**: Cero simulaciones | Cumple | `simulation_runs = 0`. |

## Compatibilidad Retroactiva
- **Lifecycle sigue funcionando**: código de lifecycle sin cambios; las rutas `start/suspend/restore/status` siguen registradas. Operará sobre los tenants nuevos de Fase 3.
- **Provisioning sigue funcionando**: `LocalFleetInstanceProvisioner`/`LocalFleetEnvBuilder` intactos; el bootstrap base demostró que el CP queda listo para provisionar.
- **Routing sigue funcionando**: rutas `control/*` y portal registradas; `/control/companies` y `/control/overview` responden 200 autenticados.
- **Simulación sigue funcionando**: servicios sin cambios; `simulation_runs` reiniciado a 0; la elegibilidad se evaluará en Fase 7.
- **Control Plane sigue funcionando**: reconstruido desde cero, integridad `ok`, autenticación `saas@local` válida, paneles operativos.

## Conclusión
La reconstrucción limpia produjo un Control Plane operativo desde un estado base, sin import legacy, con cero tenants comerciales, cero silos y cero simulaciones. Todos los criterios de aceptación formales de la Fase 2 se cumplen. Se documenta una desviación menor: `/control/companies` lista el self-tenant técnico `platform` (permitido por el Runbook §6 como identidad del CP), por lo que el estado se declara **Cumple con observaciones**.

No se avanza automáticamente a la Fase 3; se espera nueva instrucción.
