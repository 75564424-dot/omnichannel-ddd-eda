# Informe Fase 1 — Saneamiento de entorno

Estado: Cumple

## Objetivo
Eliminar configuraciones inconsistentes y garantizar parametros coherentes (puertos base y control plane).

## Evidencia encontrada
- `deploy/local-instances/instances.json` define control plane en puerto 8000.
- `.env.control-plane` usa `APP_URL=http://127.0.0.1:8000`.
- `config/platform.php` expone `PLATFORM_LOCAL_FLEET_PORT_START` con valor 8001.
- `php artisan --env=control-plane config:show platform.local_fleet.port_range_start` devuelve 8001.
- `php artisan --env=control-plane route:list` ejecuta correctamente y valida rutas base.
- `README.md`, `deploy/local-instances/README.md` y `.env.example` documentan el puerto base y el alias conceptual `BASE_TENANT_PORT`.

## Cambios realizados
- Documentacion de puertos base en `README.md`.
- Documentacion de asignacion de puertos en `deploy/local-instances/README.md`.
- Documentacion de alias `BASE_TENANT_PORT` en `.env.example`.
- Restauracion de `database/instances/platform.sqlite` desde backup de Fase 0 para validar el control plane.

## Archivos modificados
- `README.md`
- `deploy/local-instances/README.md`
- `.env.example`

## Archivos nuevos
- `docs/Plan_Desarrollo_Serviciov1.6/informe de cumplimiento por fase/Informe_Fase_1.md`

## Riesgos detectados
- Ninguno tras la alineacion de puertos y validacion de control plane.

## Riesgos mitigados
- Documentacion explicita del puerto base evita colisiones y configuraciones divergentes.

## Hallazgos fuera de alcance
- La necesidad de restaurar `platform.sqlite` proviene de la limpieza previa (Fase 0). Se corrigio para permitir validacion, sin cambios de codigo.

## Checklist Runbook
| Requisito | Estado | Evidencia |
|---|---|---|
| Alinear `PLATFORM_LOCAL_FLEET_PORT_START` con el puerto base | Cumple | Valor 8001 en `.env.control-plane` y `config:show`. |
| Verificar `instances.json` y `.env.control-plane` para puerto control plane | Cumple | `instances.json` puerto 8000; `.env.control-plane` APP_URL 8000. |
| Documentar `BASE_TENANT_PORT` como alias conceptual | Cumple | `README.md`, `deploy/local-instances/README.md`, `.env.example`. |

## Compatibilidad Retroactiva
No se modifico logica de runtime. Lifecycle, provisioning, login, fleet, registry y control plane mantienen compatibilidad; solo se actualizaron documentos y se restauro el DB del control plane para validacion.
