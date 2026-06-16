# Informe Fase 0 — Baseline limpio

Estado: Cumple

## Objetivo
Ejecutar el baseline limpio: respaldar datos, eliminar artefactos historicos y dejar el entorno listo para reconstruccion.

## Evidencia encontrada
- `deploy/local-instances/fleet-registry.json` lista solo `acme-retail` y `pruebas-retail`.
- `database/instances` contiene solo `acme-retail.sqlite` y `pruebas-retail.sqlite` tras limpieza.
- No existen archivos en `storage/app/simulation-handoff`.
- No existen archivos `storage/logs/simulation-*.log`.
- No existen archivos en `storage/app/simulation-launchers`.
- Catalogos existentes: `config/modules/instances/acme-retail/modules_config.json`, `config/modules/instances/pruebas-retail/modules_config.json`.
- Archivos env respaldados: `.env.control-plane`, `.env.client-acme-retail`, `.env.client-pruebas-retail`.
- Backup creado en `docs/Plan_Desarrollo_Serviciov1.6/backups/Fase_0_20260601_183811`.

## Cambios realizados
- Respaldo de `database/database.sqlite`, `database/instances/*.sqlite`, `fleet-registry.json`, `config/modules/instances/*` y `.env.*`.
- Ejecucion de `platform:fleet:prune-orphans`.
- Limpieza de handoffs, logs, launchers, cache y views.
- Eliminacion de DBs huérfanas `alpha/beta/gamma/platform`.
- Reset de datos operativos con `npm run instances:reset-operational`.

## Archivos modificados
- Ninguno (solo operaciones en datos/artefactos).

## Archivos nuevos
- `docs/Plan_Desarrollo_Serviciov1.6/informe de cumplimiento por fase/Informe_Fase_0.md`

## Riesgos detectados
- Ninguno tras limpieza (baseline controlado).

## Riesgos mitigados
- Removidos artefactos historicos fuera del registry.
- Handoffs/logs de simulacion eliminados.

## Hallazgos fuera de alcance
- Ninguno.

## Checklist Runbook
| Requisito | Estado | Evidencia |
|---|---|---|
| Respaldar BDs y registry | Cumple | Backup en `docs/Plan_Desarrollo_Serviciov1.6/backups/Fase_0_20260601_183811`. |
| Ejecutar `platform:fleet:prune-orphans` | Cumple | Comando ejecutado en Fase 0. |
| Limpiar handoffs, logs, cache y datos operativos | Cumple | Handoffs/logs/launchers/cache vacios; reset-operational ejecutado. |
| `fleet-registry.json` solo tenants vigentes | Cumple | Solo `acme-retail` y `pruebas-retail`. |
| `database/instances` solo bases asociadas | Cumple | Solo `acme-retail.sqlite` y `pruebas-retail.sqlite`. |

## Compatibilidad Retroactiva
No se modifico codigo ni configuraciones runtime. Lifecycle, provisioning, login, fleet, registry y control plane mantienen compatibilidad.

## Bloqueantes
- Ninguno.
