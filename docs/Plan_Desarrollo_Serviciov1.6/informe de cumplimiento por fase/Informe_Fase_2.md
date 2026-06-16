# Informe Fase 2 — Reconstruccion desde cero

Estado: Cumple

## Objetivo
Reinstalar entorno limpio y funcional siguiendo el flujo del runbook (bootstrap, fleet bootstrap, build, serve).

## Evidencia encontrada
- `npm run instances:bootstrap` ejecuto migraciones y seeders en control plane y silos.
- `npm run instances:fleet-bootstrap` importo tenants legacy y ejecuto mirror.
- `npm run build` genero assets en `public/build`.
- `npm run instances:serve` levanto servidores en 8000/8001/8002.
- Validacion HTTP `/up` devuelve 200 en 8000, 8001 y 8002.
- `http://127.0.0.1:8000/login` responde 200; `/control/overview` y `/control/simulations` responden 302 (requiere auth).
- `/control/provisioning` (8000) responde 302; `/middleware` y `/dashboard` (8001) responden 302 (auth requerida).
- `deploy/local-instances/instances.json` mantiene control plane en 8000.
- `deploy/local-instances/fleet-registry.json` mantiene `acme-retail` y `pruebas-retail`.

## Cambios realizados
- Ejecucion de bootstrap de instancias.
- Ejecucion de fleet bootstrap del control plane.
- Build de assets frontend.
- Inicio de servidores locales.

## Archivos modificados
- `.env.control-plane`
- `.env.client-acme-retail`
- `.env.client-pruebas-retail`
- `database/instances/platform.sqlite` (+ `platform.sqlite-wal`, `platform.sqlite-shm`)
- `database/instances/acme-retail.sqlite` (+ `acme-retail.sqlite-wal`, `acme-retail.sqlite-shm`)
- `database/instances/pruebas-retail.sqlite` (+ `pruebas-retail.sqlite-wal`, `pruebas-retail.sqlite-shm`)
- `config/modules/instances/acme-retail/modules_config.json`
- `config/modules/instances/pruebas-retail/modules_config.json`
- `public/build/manifest.json`
- `public/build/assets/*`

## Archivos nuevos
- `docs/Plan_Desarrollo_Serviciov1.6/informe de cumplimiento por fase/Informe_Fase_2.md`

## Riesgos detectados
- Ninguno tras reconstruccion completa.

## Riesgos mitigados
- Se regenero el entorno completo con bootstrap y fleet bootstrap.

## Hallazgos fuera de alcance
- Ninguno.

## Checklist Runbook
| Requisito | Estado | Evidencia |
|---|---|---|
| `npm run instances:bootstrap` | Cumple | Logs de bootstrap con migraciones/seeders. |
| `npm run instances:fleet-bootstrap` | Cumple | Import legacy + mirror OK. |
| `npm run build` | Cumple | Assets generados en `public/build`. |
| `npm run instances:serve` | Cumple | Servidores activos en 8000/8001/8002. |
| Control plane operativo en 8000 | Cumple | `/up` -> 200. |
| Silos creados desde provisioning | Cumple | DBs y envs de acme/pruebas regeneradas; mirror OK. |

## Compatibilidad Retroactiva
No se altero logica de lifecycle, provisioning, login, fleet, registry ni control plane. Solo se regenero el entorno y assets conforme al runbook.
