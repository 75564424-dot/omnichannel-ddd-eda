# Informe Fase 3 — Validacion lifecycle

Estado: Cumple

## Objetivo
Verificar Levantar / Suspender / Restaurar sin reinicio global. Criterio: health check operativo, suspension bloquea login y API.

## Evidencia encontrada y validacion final
- Se retomo ejecucion interrumpida y se verifico estado real de repositorio, procesos y artefactos antes de continuar.
- Se elimino completamente `lifecycle-test` tras cada correccion aplicada (tenant en control plane, usuario operador, `.env.client-lifecycle-test`, `database/instances/lifecycle-test.sqlite`, entrada en `deploy/local-instances/fleet-registry.json`).
- Provisioning desde `POST /control/provisioning` para `lifecycle-test` devolvio `302` a detalle de compania (creacion exitosa).
- Verificacion en control plane (SQLite): `settings.deployment.local_instance` presente con `env_id=client-lifecycle-test`, `port=8003`, `app_url=http://127.0.0.1:8003`.
- Verificacion bootstrap de silo: `database/instances/lifecycle-test.sqlite` creada con contenido (`598016 bytes`, `38` tablas).
- Validacion Levantar (`POST /control/companies/{tenant}/lifecycle/start`):
  - Antes de start: `PRE_START_UP=ERR` en `http://127.0.0.1:8003/up`.
  - Start responde `302`.
  - Despues de start: `POST_START_UP=200`.
  - Estado lifecycle CP: `{"lifecycle":"running","status":"active","actions_available":["suspend"]}`.
- Validacion Suspender (`POST /control/companies/{tenant}/lifecycle/suspend`):
  - Suspend responde `302`.
  - Bloqueo efectivo en silo suspendido: `SUSPEND_LOGIN=503` y `SUSPEND_API=403`.
  - Estado lifecycle CP: `{"lifecycle":"running","status":"suspended","actions_available":["restore"]}`.
- Validacion Restaurar (`POST /control/companies/{tenant}/lifecycle/restore`):
  - Restore responde `302`.
  - Rehabilitacion efectiva: `POST_RESTORE_UP=200` y `RESTORE_LOGIN=200`.
  - Estado lifecycle CP: `{"lifecycle":"running","status":"active","actions_available":["suspend"]}`.

## Correcciones aplicadas en alcance Fase 3
- `app/Shared/Platform/LocalFleet/LocalFleetInstanceProvisioner.php`
  - Correccion de argumentos bootstrap (`force` -> `--force`) para eliminar `----force`.
  - Ejecucion de Artisan con `--env` en posicion valida.
  - Fallback a `php` cuando `PHP_BINARY` apunta a `php-cgi`.
  - Inyeccion explicita de variables de `.env.{envId}` al proceso de bootstrap para evitar herencia incorrecta del control plane.
- `app/Shared/Platform/LocalFleet/LocalFleetProcessSupervisor.php`
  - Inyeccion de entorno critico del tenant al proceso aislado levantado por Start/Restore (`APP_ENV`, `DB_DATABASE`, `PLATFORM_CONTROL_PLANE`, etc.) para evitar que el silo arranque con contexto del control plane.
  - Ajuste de arranque detached en Windows conservando dicho entorno.
- `config/platform.php`
  - Parseo booleano robusto con `filter_var(..., FILTER_VALIDATE_BOOLEAN)` para flags de plataforma/lifecycle/local fleet.
- `scripts/local-instances/lib.mjs`
  - `spawnArtisanServe` ahora propaga `APP_ENV` de la instancia al proceso `php artisan serve`.

## Archivos modificados
- `app/Shared/Platform/LocalFleet/LocalFleetInstanceProvisioner.php`
- `app/Shared/Platform/LocalFleet/LocalFleetProcessSupervisor.php`
- `config/platform.php`
- `scripts/local-instances/lib.mjs`
- `deploy/local-instances/fleet-registry.json`
- `.env.client-lifecycle-test`
- `database/instances/lifecycle-test.sqlite`
- `database/instances/platform.sqlite` (+ `platform.sqlite-wal`, `platform.sqlite-shm`)
- `storage/logs/laravel.log`

## Bloqueantes y estado
- Bloqueante 1 (`settings.deployment.local_instance` ausente): Resuelto.
- Bloqueante 2 (`----force` en bootstrap): Resuelto.

## Checklist Runbook
| Requisito | Estado | Evidencia |
|---|---|---|
| `deployment.local_instance` existe | Cumple | Verificado en `tenants.settings` para `lifecycle-test` (env/port/app_url). |
| Bootstrap exitoso de nuevo silo | Cumple | `lifecycle-test.sqlite` con 38 tablas y tamano no cero. |
| StartTenantServiceUseCase exitoso | Cumple | `POST /lifecycle/start` -> 302; `/up` en 8003 pasa de error a 200. |
| Health check operativo tras Levantar | Cumple | `POST_START_UP=200`. |
| Suspension bloquea login y API | Cumple | `SUSPEND_LOGIN=503`, `SUSPEND_API=403`. |
| Restaurar habilita acceso | Cumple | `POST_RESTORE_UP=200`, `RESTORE_LOGIN=200`, status vuelve a `active`. |

## Compatibilidad Retroactiva
Las correcciones se limitaron a provisioning/bootstrap/metadata/arranque de silo local del lifecycle. No se tocaron fases 4+ (autenticacion, simulacion, routing) ni se introdujeron workarounds temporales.
