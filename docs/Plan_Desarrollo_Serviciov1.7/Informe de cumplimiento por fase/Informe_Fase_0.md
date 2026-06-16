# Informe Fase 0 — Inventario técnico completo

## Estado
Cumple

## Objetivo
Registrar todo dato, artefacto y configuración que pueda contaminar la validación, generando una matriz de artefactos clasificados antes de proceder con la limpieza (Fase 1).

## Evidencia encontrada

### 1. Tenants en Control Plane (`platform.sqlite`)
- `platform` (Control Plane, Base)
- `acme-retail` (Histórico)
- `pruebas-retail` (Histórico)
- `retail-norte` (Histórico / Huérfano - sin silo)
- `retail-sur` (Histórico / Huérfano - sin silo)
- `lifecycle-test` (Temporal v1.6)
- `unaprueba` (Desconocido / Temporal generado)

### 2. Usuarios por `tenant_id`
- `saas@local` (saas_admin, sin tenant_id)
- `admin@local` (platform_admin, `acme-retail`)
- `prueba@prueba` (platform_admin, `pruebas-retail`)
- `lifecycle@local` (platform_admin, `lifecycle-test`)
- `abc@abc` (platform_admin, `unaprueba`)

### 3. Fleet Registry (`deploy/local-instances/fleet-registry.json`)
Contiene los siguientes silos registrados:
- `acme-retail` (Puerto 8001)
- `pruebas-retail` (Puerto 8002)
- `lifecycle-test` (Puerto 8003)
- `unaprueba` (Puerto 8004)

### 4. Archivos `.env.client-*`
- `.env.client-acme-retail`
- `.env.client-lifecycle-test`
- `.env.client-pruebas-retail`
- `.env.client-unaprueba`

### 5. Bases de datos (`database/instances/*`)
- `platform.sqlite` (y sus archivos `-wal`, `-shm`)
- `acme-retail.sqlite` (y sus archivos `-wal`, `-shm`)
- `pruebas-retail.sqlite` (y sus archivos `-wal`, `-shm`)
- `lifecycle-test.sqlite` (y sus archivos `-wal`, `-shm`)
- `unaprueba.sqlite` (y sus archivos `-wal`, `-shm`)

### 6. Catálogos de módulos (`config/modules/instances/*`)
- `acme-retail/modules_config.json`
- `pruebas-retail/modules_config.json`
- `lifecycle-test/modules_config.json`
- `lifecycle-debug/modules_config.json` (Huérfano)
- `unaprueba/modules_config.json`

### 7. Runs de simulación (`simulation_runs`)
- `6fb8d77b...` (`pruebas-retail`, completed)
- `28e3f92b...` (`pruebas-retail`, completed)
- `06f89101...` (`acme-retail`, completed)
- `bb96cd4d...` (`lifecycle-test`, failed)

### 8. Artefactos de simulación (`storage/app/simulation-*`, `storage/logs/simulation-*`)
- Handoffs: `aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa.json`, `bb96cd4d-1bf2-4075-b5ee-1fbcab9f1f67.json`
- Launchers: `bb96cd4d-1bf2-4075-b5ee-1fbcab9f1f67.bat`
- Logs: `simulation-dispatch-bb96cd4d-1bf2-4075-b5ee-1fbcab9f1f67.log`

### 9. Procesos activos por puerto
- Puerto `8000` (Control Plane)
- Puerto `8004` (Silo `unaprueba`)

### 10. Estado Git (`git status --short`)
El workspace presenta múltiples archivos modificados, incluyendo `.env.client-*`, bases de datos SQLite, archivos compilados en `public/build/assets`, y rutas duplicadas con separadores de Windows (`database\instances\...`). Esto confirma la contaminación del entorno.

## Cambios realizados
- Ninguno. Fase de solo lectura e inventario.

## Archivos modificados
- Ninguno.

## Archivos nuevos
- `docs/Plan_Desarrollo_Serviciov1.7/informe de cumplimiento por fase/Informe_Fase_0.md`

## Riesgos detectados
- El workspace está altamente contaminado con datos históricos, temporales y huérfanos.
- Existen procesos activos (puerto 8000 y 8004) que mantienen bloqueados los archivos WAL/SHM de SQLite, lo que podría interferir con la limpieza en la Fase 1.

## Riesgos mitigados
- Se ha documentado la totalidad de la contaminación antes de proceder a su eliminación.

## Hallazgos clasificados
- **Legacy**: `acme-retail`, `pruebas-retail`, `retail-norte`, `retail-sur`.
- **Artefactos temporales**: `lifecycle-test`, `unaprueba`, `lifecycle-debug`, runs de simulación, handoffs, launchers, logs.
- **Operativo**: Procesos activos en puertos 8000 y 8004.

## Checklist del Runbook
| Requisito | Estado | Evidencia |
|-----------|--------|-----------|
| Inventariar tenants en control plane | Cumple | Listados en sección 1. |
| Inventariar usuarios por `tenant_id` | Cumple | Listados en sección 2. |
| Inventariar `fleet-registry.json` | Cumple | Listados en sección 3. |
| Inventariar `.env.client-*` | Cumple | Listados en sección 4. |
| Inventariar `database/instances/*` | Cumple | Listados en sección 5. |
| Inventariar `config/modules/instances/*` | Cumple | Listados en sección 6. |
| Inventariar runs de simulacion | Cumple | Listados en sección 7. |
| Inventariar `storage/app/simulation-*` y logs | Cumple | Listados en sección 8. |
| Inventariar procesos activos por puerto | Cumple | Listados en sección 9. |
| Capturar `git status --short` antes de limpiar | Cumple | Documentado en sección 10. |
| Existe una matriz de artefactos con clasificación | Cumple | Clasificación detallada en el informe. |

## Compatibilidad Retroactiva
No aplica. Fase de solo lectura. Todo sigue funcionando igual.
