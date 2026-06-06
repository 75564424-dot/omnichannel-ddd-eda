# Informe Fase 1 — Erradicación total de legacy

## Estado
**Cumple**

## Objetivo
Eliminar todo tenant, silo y artefacto heredado del entorno, dejando sobrevivir únicamente el Control Plane (`platform`), la configuración base, el código fuente, los scripts y la documentación. El objetivo es destruir el contexto contaminado antes de reconstruir, de modo que cualquier problema posterior pueda clasificarse como bug real (si se reproduce en `tenant-test-*` nuevos) o como legacy (si desaparece tras la limpieza).

## Evidencia encontrada

### Estado previo a la erradicación (heredado de Fase 0)
- **Tenants en CP (`platform.sqlite`)**: `platform`, `acme-retail`, `pruebas-retail`, `retail-norte`, `retail-sur`, `lifecycle-test`, `unaprueba` (7 filas).
- **Usuarios**: `saas@local` (saas_admin), `admin@local`, `prueba@prueba`, `lifecycle@local`, `abc@abc` (5 filas).
- **Runs de simulación**: 4 (`pruebas-retail` x2, `acme-retail` x1, `lifecycle-test` x1).
- **`.env.client-*`**: `acme-retail`, `pruebas-retail`, `lifecycle-test`, `unaprueba` (4 archivos).
- **`database/instances/*`**: SQLite + WAL/SHM para `acme-retail`, `pruebas-retail`, `lifecycle-test`, `unaprueba`, además de `platform.sqlite` (base).
- **`config/modules/instances/*`**: `acme-retail`, `pruebas-retail`, `lifecycle-test`, `lifecycle-debug`, `unaprueba`.
- **Artefactos de simulación**: 2 handoffs, 1 launcher `.bat`, 1 log `simulation-dispatch-*.log`.
- **Procesos activos**: PID 8300 (`php.exe`, silo `unaprueba` en `:8004`) y socket en `:8000` (PID 18400, CP, ya zombie/no listado en tasklist).
- **Registry (`fleet-registry.json`)**: 4 instancias cliente (`acme-retail:8001`, `pruebas-retail:8002`, `lifecycle-test:8003`, `unaprueba:8004`).

### Estado posterior a la erradicación (verificado)
- **Tenants en CP**: solo `platform (active)`.
- **Usuarios**: solo `saas@local (saas_admin, sin tenant_id)`.
- **Runs de simulación**: 0.
- **`.env.client-*`**: 0 archivos.
- **`database/instances/`**: solo `.gitkeep` y `platform.sqlite` (integridad `PRAGMA integrity_check = ok`).
- **`config/modules/instances/`**: 0 elementos.
- **`storage/app/simulation-handoff/`**: 0 elementos.
- **`storage/app/simulation-launchers/`**: 0 elementos.
- **`storage/logs/simulation-*.log`**: 0 elementos.
- **Puertos `127.0.0.1:80xx` LISTENING**: 0.
- **Registry**: `instances: []`.

### Validación de no-regresión
- `php artisan migrate:status --env=control-plane`: ejecuta correctamente contra `platform.sqlite` (conectividad y migraciones intactas).
- `php artisan route:list --env=control-plane`: rutas de `control/*`, `control/companies`, simulación y portal registradas; routing y middleware intactos.
- `PRAGMA integrity_check` sobre `platform.sqlite`: `ok`.

## Cambios realizados
1. Detención del proceso del silo `unaprueba` (PID 8300, `:8004`). El socket zombie del CP (`:8000`, PID 18400) quedó liberado.
2. Eliminación de los 4 archivos `.env.client-*`.
3. Eliminación de los SQLite + WAL/SHM de los 4 tenants (`acme-retail`, `pruebas-retail`, `lifecycle-test`, `unaprueba`). Conservados `platform.sqlite` y `.gitkeep`.
4. Eliminación de los 5 catálogos por instancia en `config/modules/instances/` (incluido el huérfano `lifecycle-debug`).
5. Eliminación de handoffs, launcher y log de simulación.
6. Borrado transaccional en `platform.sqlite`: 4 `simulation_runs`, 4 usuarios operadores (con `tenant_id`), 6 tenants (todos excepto `platform`). Checkpoint WAL `TRUNCATE` aplicado.
7. Vaciado de `deploy/local-instances/fleet-registry.json` (`instances: []`).

## Archivos modificados
- `deploy/local-instances/fleet-registry.json` (vaciado de clientes).
- `database/instances/platform.sqlite` (eliminación de tenants comerciales, operadores y runs; consolidación WAL).

## Archivos nuevos
- `docs/Plan_Desarrollo_Serviciov1.7/informe de cumplimiento por fase/Informe_Fase_1.md`

## Archivos eliminados
- `.env.client-acme-retail`, `.env.client-pruebas-retail`, `.env.client-lifecycle-test`, `.env.client-unaprueba`
- `database/instances/{acme-retail,pruebas-retail,lifecycle-test,unaprueba}.sqlite` + `-wal` + `-shm`
- `database/instances/platform.sqlite-wal`, `database/instances/platform.sqlite-shm` (consolidados al checkpoint)
- `config/modules/instances/{acme-retail,pruebas-retail,lifecycle-test,lifecycle-debug,unaprueba}/`
- `storage/app/simulation-handoff/{aaaaaaaa-...,bb96cd4d-...}.json`
- `storage/app/simulation-launchers/bb96cd4d-...bat`
- `storage/logs/simulation-dispatch-bb96cd4d-...log`

## Riesgos detectados
- **WAL/SHM activos por procesos vivos**: el silo `unaprueba` mantenía bloqueados archivos SQLite. Se mitigó deteniendo el proceso antes de tocar disco.
- **Riesgo de pérdida de `platform.sqlite`**: las eliminaciones por wildcard podían afectar la BD base. Se mitigó usando prefijos específicos por tenant y verificando integridad posterior (`integrity_check = ok`).
- **Re-contaminación por import legacy**: `ControlPlaneFleetBootstrapService::importLegacyTenants()` y `database/database.sqlite` pueden reintroducir `acme-retail`/`pruebas-retail` si se ejecuta `instances:fleet-bootstrap`. Mitigación: Fase 2 debe ejecutar bootstrap base **sin** import legacy (precondición del Runbook §6).

## Riesgos mitigados
- Procesos de silo detenidos antes de la limpieza de disco (sin WAL/SHM retenidos).
- Integridad de `platform.sqlite` verificada tras las eliminaciones.
- Borrado de CP realizado en transacción con rollback ante error.

## Hallazgos clasificados

### Legacy (desaparecen tras la limpieza) — CONFIRMADO
- Tenants históricos `acme-retail`, `pruebas-retail`, `retail-norte`, `retail-sur` y sus operadores/SQLite/env/catálogos: eliminados.
- Tenants huérfanos `retail-norte` y `retail-sur` (sin silo): eliminados del CP.

### Artefacto temporal (desaparecen tras la limpieza) — CONFIRMADO
- `lifecycle-test` y `unaprueba` (tenants, silos, SQLite, env, catálogos): eliminados.
- Catálogo huérfano `lifecycle-debug`: eliminado.
- 4 `simulation_runs`, 2 handoffs, 1 launcher, 1 log: eliminados.

### Operativo — RESUELTO
- Proceso PHP del silo `unaprueba` (`:8004`, PID 8300): detenido.
- Socket zombie del CP (`:8000`, PID 18400): liberado.

### Configuración — FUERA DE ALCANCE (registrado)
- `database/database.sqlite` (fuente legacy de import) y la documentación/código de import legacy permanecen. No forman parte del alcance de erradicación de la Fase 1 (Runbook §6 no lo lista). Ver sección Control de Alcance.

### Bug Real
- Ninguno en esta fase. La determinación de bugs reales requiere `tenant-test-*` creados desde cero (Fases 3-8).

## Control de Alcance — Trabajo perteneciente a otras fases
Se detectaron elementos legacy en **código y documentación** (no en datos runtime), que NO se modifican en Fase 1:

| Trabajo fuera de alcance | Descripción | Fase correspondiente | Impacto | Recomendación |
|---|---|---|---|---|
| `database/database.sqlite` | SQLite fuente del import legacy | Fase 2 / Fase 10 | Re-contaminación solo si se corre `fleet-bootstrap` | No ejecutar import legacy; documentar/decidir en baseline |
| `ControlPlaneFleetBootstrapService::importLegacyTenants()` | Código que importa `acme-retail`/`pruebas-retail` | Problema 6 / Baseline | Potencial reintroducción de legacy | Separar modo demo de modo GitHub Ready |
| `database/seeders/AcmeRetailSimulationSeeder.php` | Seeder demo con slug legacy | Baseline / Docs | Solo si se ejecuta explícitamente | Revisar en certificación de docs |
| `README.md`, `deploy/local-instances/README.md`, `.env.example`, `.env.playwright` | Documentan `acme-retail`/`pruebas-retail` | Problema 6 / Fase 10 | Documentación heredada | Actualizar README a flujo baseline reproducible |
| Tests (`tests/**`) que referencian slugs legacy | Fixtures de prueba | N/A (código de prueba) | Ninguno en runtime | Sin acción en v1.7 |

Estos elementos son **código fuente / documentación**, no artefactos runtime, por lo que su presencia no contradice el criterio de erradicación de la Fase 1. Se continúa únicamente con la fase actual.

## Checklist del Runbook
| Requisito (Fase 1) | Estado | Evidencia |
|---|---|---|
| Detener procesos locales | Cumple | PID 8300 terminado; `:8001-8004` sin LISTENING. |
| Vaciar registry de clientes | Cumple | `fleet-registry.json` → `instances: []`. |
| Eliminar `.env.client-*` | Cumple | 0 archivos `.env.client-*`. |
| Eliminar SQLite/WAL/SHM de tenants | Cumple | `database/instances/` solo `.gitkeep` + `platform.sqlite`. |
| Eliminar catálogos por instancia | Cumple | `config/modules/instances/` con 0 elementos. |
| Eliminar handoffs, launchers y logs de simulación | Cumple | 0 elementos en handoff/launchers/logs. |
| Eliminar tenants comerciales y operadores del CP | Cumple | Solo `platform` + `saas@local`. |
| Eliminar runs de simulación de tenants comerciales | Cumple | `simulation_runs = 0`. |
| Verificar inexistencia de slugs prohibidos fuera de doc histórica | Cumple | Sin tenants/silos/envs/registry; referencias residuales solo en código/tests/docs (registradas en Control de Alcance). |
| **CA**: El CP no lista tenants comerciales | Cumple | Solo `platform`. |
| **CA**: El filesystem no contiene silos ni envs de cliente | Cumple | 0 `.env.client-*`, 0 SQLite de tenant. |
| **CA**: No hay procesos de silos en puertos 8001+ | Cumple | 0 LISTENING en `:80xx`. |

## Compatibilidad Retroactiva
La Fase 1 es destructiva por diseño (erradica datos), por lo que la compatibilidad se argumenta a nivel de **código y framework**, no de datos:

- **Lifecycle sigue funcionando**: no se modificó código de lifecycle (`EnsureTenantOperationalStatus`, endpoints `start/suspend/restore`). Las rutas siguen registradas (`route:list`).
- **Provisioning sigue funcionando**: `LocalFleetInstanceProvisioner` y `LocalFleetEnvBuilder` intactos; generarán tenants nuevos en Fase 3.
- **Routing sigue funcionando**: `routes/tenant_portal.php`, `ResolveTenantFromRoutePath` y `TenantPortalProxyController` sin cambios; rutas presentes en `route:list`.
- **Simulación sigue funcionando**: servicios de simulación sin cambios; solo se borraron datos (runs/handoffs). La lógica de elegibilidad se evaluará en Fase 7.
- **Control Plane sigue funcionando**: `platform.sqlite` con integridad `ok`; `migrate:status` y `route:list` arrancan sin errores contra la BD limpia.

## Conclusión
Todos los criterios de aceptación de la Fase 1 del Runbook v1.7 están cumplidos y verificados con evidencia. El entorno queda sin tenants comerciales, sin silos, sin envs de cliente, sin catálogos por instancia, sin artefactos de simulación y sin procesos de silo activos. Sobreviven únicamente el Control Plane (`platform`), `saas@local`, configuración base, código fuente, scripts y documentación.

**Estado = Cumple.** No se avanza automáticamente a la Fase 2; se espera nueva instrucción.
