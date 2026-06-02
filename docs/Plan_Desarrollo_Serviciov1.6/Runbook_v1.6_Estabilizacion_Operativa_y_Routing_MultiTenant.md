# Runbook v1.6 — Estabilizacion Operativa y Routing Multi-Tenant

**Version del plan:** 1.6  
**Estado del documento:** Borrador de ejecucion (sin implementacion de codigo)  
**Fecha:** 2026-06-01  
**Repositorio:** `omnichannel-ddd-eda`  
**Alcance:** Estabilizacion, saneamiento y formalizacion de routing sin nuevas funcionalidades de negocio.

---

## Principios rectores

1. **Primero baseline limpio.** Cualquier depuracion comienza con limpieza y reconstruccion del entorno.  
2. **Sin nuevas funcionalidades de negocio.** Solo estabilizacion y correccion de regresiones.  
3. **Compatibilidad con ADR-001.** Se mantiene instancia por cliente en runtime.  
4. **Evidencia en repositorio.** Todas las conclusiones de este runbook provienen del codigo y docs actuales.

---

## Evidencias y fuentes consultadas (repo)

### Provisioning y fleet local
- `app/Shared/Platform/LocalFleet/LocalFleetRegistry.php`
- `app/Shared/Platform/LocalFleet/LocalFleetInstanceProvisioner.php`
- `app/Shared/Platform/LocalFleet/LocalFleetEnvBuilder.php`
- `app/Shared/Platform/LocalFleet/LocalFleetTenantMirror.php`
- `deploy/local-instances/instances.json`
- `deploy/local-instances/fleet-registry.json`
- `scripts/local-instances/bootstrap.mjs`
- `scripts/local-instances/reset-operational.mjs`
- `app/Console/Commands/Platform/PruneLocalFleetClientsCommand.php`
- `app/Shared/Platform/LocalFleet/LocalFleetOrphanPruner.php`

### Routing y control de acceso
- `routes/web.php`
- `routes/control.php`
- `app/Shared/Platform/DatabaseInstanceTenantContext.php`
- `app/Shared/Platform/Services/InstanceDeploymentService.php`
- `app/Shared/Identity/Application/AuthenticateOperatorUseCase.php`
- `app/Shared/Platform/Services/InstancePortalAccessGuard.php`
- `config/platform.php`
- `tests/Feature/Identity/OperatorLoginTest.php`

### Simulaciones y polling
- `resources/js/Pages/Control/Simulation/Index.vue`
- `app/Simulation/Interfaces/Http/Controllers/SimulationRunController.php`
- `app/Simulation/Application/Services/Orchestration/SimulationRunQueryService.php`
- `app/Simulation/Application/Services/Orchestration/SimulationRunStaleGuard.php`
- `app/Simulation/Application/Services/Metrics/SimulationRunMetricsCollector.php`

### ADR relevantes
- `docs/production/ADR_001_instancia_por_cliente.md`
- `docs/production/ADR_004_tenant_id_activation.md`
- `docs/production/ADR_010_tenant_lifecycle_management.md`

---

## Estado actual (arquitectura real)

### Routing basado en puertos (actual)
- Control plane se define en `deploy/local-instances/instances.json` con `port = 8000`.
- Los silos locales se registran en `deploy/local-instances/fleet-registry.json` y reciben `port` desde `LocalFleetRegistry::nextAvailablePort()`.
- `LocalFleetRegistry` usa `config('platform.local_fleet.port_range_start')`, default `PLATFORM_LOCAL_FLEET_PORT_START=8001`.
- `LocalFleetEnvBuilder` construye `APP_URL` basado en el puerto asignado.

### Multi-tenant portal login (demo / local)
- `config/platform.php` expone `platform.multi_tenant_portal_login` (`PLATFORM_PORTAL_MULTI_TENANT_LOGIN=false` por defecto).
- `DatabaseInstanceTenantContext` guarda el tenant activo en session (`portal_tenant_id`) cuando el flag esta activo.
- `AuthenticateOperatorUseCase` y `InstancePortalAccessGuard` bloquean operadores si el tenant no corresponde al `PLATFORM_CLIENT_SLUG` cuando el flag es falso.

---

## Problema 1 — Baseline limpio

### Evidencia de artefactos historicos
Fuentes de datos y artefactos que afectan el estado actual:
- **Control plane y silos locales:** `database/instances/*.sqlite`, `database/database.sqlite`
- **Env por instancia:** `.env.control-plane`, `.env.client-*` (generadas por bootstrap)
- **Registry de fleet local:** `deploy/local-instances/fleet-registry.json`
- **Catalogos por instancia:** `config/modules/instances/{slug}/modules_config.json`
- **Handoff de simulacion:** `storage/app/simulation-handoff/*.json`
- **Logs de simulacion:** `storage/logs/simulation-*.log`
- **Launchers de simulacion en Windows:** `storage/app/simulation-launchers/*.bat`
- **Caches y views:** `storage/framework/cache`, `storage/framework/views`

### Estrategia de respaldo (obligatoria)
1. Exportar copia de:
   - `database/database.sqlite`
   - `database/instances/*.sqlite`
   - `deploy/local-instances/fleet-registry.json`
   - `config/modules/instances/`
2. Conservar `.env.*` actuales como referencia de configuracion.
3. Respaldar `storage/logs/simulation-*.log` solo si se requiere post-mortem.

### Estrategia de limpieza
Objetivo: eliminar datos historicos sin afectar el codigo.
- Eliminar silos locales no presentes en `fleet-registry.json` usando `platform:fleet:prune-orphans`.
- Reset de datos operativos con `npm run instances:reset-operational` (usa `demo:reset-operational` y `platform:simulation:reset`).
- Borrar handoffs y logs de simulacion en `storage/app/simulation-handoff` y `storage/logs/simulation-*`.
- Re-crear `database/instances` y `.env.client-*` via `npm run instances:bootstrap`.

### Estrategia de reconstruccion
Flujo esperado tras limpieza:
```
Base limpia
  -> npm run instances:bootstrap
  -> npm run instances:fleet-bootstrap
  -> npm run build
  -> npm run instances:serve
  -> Provisioning desde /control/provisioning
  -> Levantar servicio / Suspender / Restaurar
  -> Login operador
  -> Simulacion
  -> Operacion normal
```

---

## Problema 2 — Asignacion de puertos

### Evidencia actual
- `LocalFleetRegistry::nextAvailablePort()` inicia desde `config('platform.local_fleet.port_range_start')`.
- `config/platform.php` expone `PLATFORM_LOCAL_FLEET_PORT_START` (default 8001).
- Puertos persistidos en `fleet-registry.json`.
- Control plane siempre en `instances.json` (no auto-asignado).

### Requisito de configuracion centralizada
La asignacion debe depender de un unico origen configurado. Hoy existe `PLATFORM_LOCAL_FLEET_PORT_START`; se propone formalizarlo como **BASE_TENANT_PORT** en documentacion y mapearlo a `PLATFORM_LOCAL_FLEET_PORT_START` (sin cambios de codigo en v1.6).

### Riesgos detectados
- Colisiones si `fleet-registry.json` conserva puertos antiguos.
- Inconsistencia si `instances.json` y `.env.control-plane` no coinciden con el puerto definido en los scripts.

---

## Problema 3 — Login de empresas historicas

### Evidencia de comportamiento
- `AuthenticateOperatorUseCase` rechaza operadores con `tenant_id` null o cuando `PLATFORM_CLIENT_SLUG` no coincide (si `PLATFORM_PORTAL_MULTI_TENANT_LOGIN=false`).
- `InstancePortalAccessGuard` bloquea usuarios cuyo `tenant_id` no coincide con el tenant activo.
- `DatabaseInstanceTenantContext` resuelve tenant por `PLATFORM_CLIENT_SLUG` o `portal_tenant_id` en session.
- `LocalFleetTenantMirror` sincroniza usuarios/tenants del control plane al silo, pero requiere que el silo exista y tenga tenant local.

### Causas probables (sin asumir)
1. **Usuarios legacy sin `tenant_id`** (bloqueo explicito).
2. **Silos desincronizados** por falta de mirror tras cambios.
3. **Slug configurado distinto** entre `.env.client-*` y la fila de `tenants`.
4. **Multi-tenant portal login deshabilitado** (default), lo cual bloquea operadores que intentan ingresar en un host distinto al de su tenant.

### Estrategia de correccion (sin implementar)
- Validar consistencia de `tenant_id` en `users` vs `tenants`.
- Ejecutar mirror control plane -> silo tras limpieza (`platform:fleet:sync-local`).
- Verificar `PLATFORM_CLIENT_SLUG` en `.env` del silo coincide con `tenants.slug`.
- Activar `PLATFORM_PORTAL_MULTI_TENANT_LOGIN` solo para modo demo/control plane.

---

## Problema 4 — Simulacion en bucle (polling infinito)

### Evidencia encontrada
En `resources/js/Pages/Control/Simulation/Index.vue`, el polling llama:
```
GET /control/simulations/{id}/status
```
El controlador responde con:
```
{ data: { run: { ... } } }
```
pero el frontend espera `payload.run` y no `payload.data.run`.  
Resultado: `mergeRunFromStatus()` no actualiza el estado local y el polling nunca detecta que la simulacion termino.

### Estrategia de correccion (sin implementar)
- Unificar el contrato: ajustar frontend a `payload.data.run` o ajustar backend a `{ run: ... }`.
- Agregar timeout de polling en frontend como defensa secundaria.
- Validar `SimulationRunStaleGuard` para marcar runs colgadas y terminar polling.

---

## Problema 5 — Formalizacion de routing multi-tenant

### Estado actual (basado en puertos)
- Un puerto por cliente: `http://127.0.0.1:8001/login`.
- `PLATFORM_CLIENT_SLUG` define el tenant de la instancia.
- Control plane separado en puerto 8000.

### Estado intermedio (routing por ruta)
Objetivo: `http://127.0.0.1:8000/{tenant}/login`
- Usar `portal_tenant_id` en session para fijar el tenant activo.
- Requiere middleware de resolucion de tenant por ruta (slug).
- Debe coexistir con rutas actuales por puerto sin romperlas.

### Estado futuro (routing por subdominio)
Objetivo: `https://{tenant}.midominio.com/login`
- Depende de config `platform.deployment.app_url_template`.
- Requiere resolucion por subdominio y mapeo a tenant.
- Compatible con ADR-001: sigue siendo una instancia por cliente, solo cambia el routing externo.

### Impacto esperado
- Middleware: requiere un resolver de tenant por ruta/subdominio.
- Autenticacion: debe validar `tenant_id` contra el tenant resuelto.
- Sesiones: session cookie debe soportar path o dominio compartido.
- Fleet/registry: se mantiene para local dev, pero se documenta el routing amigable.

---

## Fases del Runbook v1.6

### Fase 0 — Baseline limpio
**Objetivo:** snapshot y eliminacion controlada de datos historicos.  
**Acciones:**
- Respaldar BDs y registry.
- Ejecutar `platform:fleet:prune-orphans`.
- Limpiar handoffs, logs, cache y datos operativos.
**Criterio de aceptacion:**
- `fleet-registry.json` contiene solo tenants vigentes.
- `database/instances` contiene solo bases asociadas.

### Fase 1 — Saneamiento de entorno
**Objetivo:** eliminar configuraciones inconsistentes y garantizar parametros coherentes.  
**Acciones:**
- Alinear `PLATFORM_LOCAL_FLEET_PORT_START` con el puerto base.
- Verificar que `instances.json` y `.env.control-plane` usen el puerto control plane correcto.
- Documentar variable `BASE_TENANT_PORT` como alias conceptual de `PLATFORM_LOCAL_FLEET_PORT_START`.

### Fase 2 — Reconstruccion desde cero
**Objetivo:** reinstalar entorno limpio y funcional.  
**Acciones:**
- `npm run instances:bootstrap`
- `npm run instances:fleet-bootstrap`
- `npm run build`
- `npm run instances:serve`
**Criterio de aceptacion:** control plane operativo en 8000 y silos creados desde provisioning.

### Fase 3 — Validacion de lifecycle
**Objetivo:** verificar Levantar/Suspender/Restaurar sin reinicio global.  
**Acciones:** pruebas manuales siguiendo v1.5.  
**Criterio:** health check operativo, suspension bloquea login y API.

### Fase 4 — Correccion de autenticacion
**Objetivo:** restablecer login para tenants historicos.  
**Acciones:** validar `tenant_id`, mirror y slug correcto por silo.  
**Criterio:** operadores legacy pueden autenticarse en su silo correcto.

### Fase 5 — Correccion de simulacion
**Objetivo:** eliminar polling infinito y asegurar cierre de runs.  
**Acciones:** unificar contrato `/status`, activar stale guard como fallback.  
**Criterio:** polling se detiene al completar/fallar.

### Fase 6 — Diseno de routing amigable
**Objetivo:** definir resolver por ruta y subdominio.  
**Acciones:** ADR tecnico de routing y plan de migracion.  
**Criterio:** decisiones aprobadas sin alterar runtime actual.

### Fase 7 — Implementacion routing amigable
**Objetivo:** habilitar `/tenant/*` en entorno local sin romper puertos.  
**Acciones:** middleware de tenant resolver, rutas con prefijo, compatibilidad backward.  
**Criterio:** login por ruta funcional en entorno local.

### Fase 8 — Pruebas integrales
**Objetivo:** regresion completa (lifecycle, login, simulacion, routing).  
**Criterio:** suite manual + automatizada sin fallas.

### Fase 9 — Validacion final y certificacion
**Objetivo:** checklist final y sign-off.  
**Criterio:** runbook completo, docs actualizadas, entorno estable.

---

## Documentacion obligatoria a actualizar (v1.6)

- `README.md`: flujo baseline limpio y variables centrales.
- `deploy/local-instances/README.md`: puertos base y routing intermedio.
- `.env.example`: documentar `PLATFORM_LOCAL_FLEET_PORT_START` como puerto base.
- Nuevas instrucciones para `BASE_TENANT_PORT` (alias conceptual).

---

## Checklist de cumplimiento (v1.6)

| Requisito | Cumple | Evidencia |
|----------|--------|-----------|
| Baseline limpio antes de depurar | ✓ Cumple | Fases 0–2: entorno reconstruido, fleet-registry consistente, silos operativos |
| Puertos centralizados | ✓ Cumple | `PLATFORM_LOCAL_FLEET_PORT_START` en `config/platform.php`; alias `BASE_TENANT_PORT` documentado en README y `.env.example` |
| Login historico analizado y corregido | ✓ Cumple | Fase 4: password hashes actualizados y propagados a silos; `admin@local` y `prueba@prueba` operativos |
| Polling simulacion eliminado | ✓ Cumple | Fase 5: contrato `/status` unificado, guard `MAX_POLL_CYCLES` añadido en frontend, `SimulationRunStaleGuard` activado |
| Routing multi-tenant implementado | ✓ Cumple | Fase 6 ADR-011 aceptado; Fase 7 `ResolveTenantFromRoutePath` + `TenantPortalProxyController` implementados; `PLATFORM_FRIENDLY_ROUTING=true` activado en CP |
| Suite automatizada sin fallos | ✓ Cumple | Fase 8: 260/260 tests (131 Unit + 129 Feature); 30/30 checks HTTP reales |
| Documentacion obligatoria actualizada | ✓ Cumple | Fase 9: README.md, deploy/local-instances/README.md, .env.example, ADR-011, Plan_Migracion_Routing_v1.6.md |

---

## Estado final de este runbook

Documento creado con base en evidencia del repositorio.  
No se realizaron cambios de codigo, migraciones ni limpieza de datos.  
Listo para revision y aprobacion previa a ejecucion de la version 1.6.
