# Runbook v1.7 — Certificacion Operativa, Legacy Eradication y Baseline GitHub Ready

**Version del plan:** 1.7  
**Estado del documento:** Hoja de ruta oficial de auditoria y ejecucion controlada  
**Fecha:** 2026-06-02  
**Repositorio:** `omnichannel-ddd-eda`  
**Alcance:** Erradicacion legacy, reconstruccion limpia, validacion de identity/branding, routing amigable, elegibilidad de simulacion, limpieza final y certificacion GitHub Ready.

---

## 1. Proposito

La version 1.7 tiene un objetivo principal: demostrar con evidencia si los problemas observados provienen de errores reales de implementacion o de datos legacy, configuraciones antiguas, tenants historicos o artefactos heredados.

Este runbook no asume que las certificaciones v1.5/v1.6 son falsas. Tampoco asume que todo problema actual es legacy. Define un procedimiento reproducible para separar ambos escenarios.

El resultado esperado de v1.7 es un baseline donde sobrevivan unicamente:

- Control Plane.
- Configuracion base.
- Codigo fuente.
- Scripts.
- Documentacion.

No debe sobrevivir ningun tenant creado previamente, silo heredado, simulacion heredada, SQLite historica, env historico, mirror historico ni artefacto temporal.

---

## 2. Fuentes de Verdad Auditadas

### Documentacion v1.5

- `docs/Plan_Desarrollo_Serviciov1.5/Runbook_v1.5_Gestion_Ciclo_Vida_Tenants.md`
- `docs/Plan_Desarrollo_Serviciov1.5/informe de cumplimiento por fase/Informe_Auditoria_Global_v1.5.md`
- `docs/Plan_Desarrollo_Serviciov1.5/informe de cumplimiento por fase/Fase_7.md`
- `docs/production/ADR_010_tenant_lifecycle_management.md`

### Documentacion v1.6

- `docs/Plan_Desarrollo_Serviciov1.6/Runbook_v1.6_Estabilizacion_Operativa_y_Routing_MultiTenant.md`
- `docs/Plan_Desarrollo_Serviciov1.6/Plan_Migracion_Routing_v1.6.md`
- `docs/Plan_Desarrollo_Serviciov1.6/informe de cumplimiento por fase/Informe_Fase_0.md`
- `docs/Plan_Desarrollo_Serviciov1.6/informe de cumplimiento por fase/Informe_Fase_2.md`
- `docs/Plan_Desarrollo_Serviciov1.6/informe de cumplimiento por fase/Informe_Fase_4.md`
- `docs/Plan_Desarrollo_Serviciov1.6/informe de cumplimiento por fase/Informe_Fase_5.md`
- `docs/Plan_Desarrollo_Serviciov1.6/informe de cumplimiento por fase/Informe_Fase_7.md`
- `docs/Plan_Desarrollo_Serviciov1.6/informe de cumplimiento por fase/Informe_Fase_8.md`
- `docs/Plan_Desarrollo_Serviciov1.6/informe de cumplimiento por fase/Informe_Fase_9.md`
- `docs/production/ADR_011_friendly_routing_multitenant.md`

### Codigo y configuracion

- `README.md`
- `deploy/local-instances/README.md`
- `deploy/local-instances/fleet-registry.json`
- `config/platform.php`
- `config/module_blueprint.php`
- `bootstrap/app.php`
- `routes/control.php`
- `routes/tenant_portal.php`
- `app/Shared/Platform/LocalFleet/LocalFleetEnvBuilder.php`
- `app/Shared/Platform/LocalFleet/LocalFleetInstanceProvisioner.php`
- `app/Shared/Platform/LocalFleet/LocalFleetTenantMirror.php`
- `app/Shared/Platform/Services/ControlPlaneFleetBootstrapService.php`
- `app/Shared/Platform/DatabaseInstanceTenantContext.php`
- `app/Shared/Identity/Application/AuthenticateOperatorUseCase.php`
- `app/Http/Middleware/EnsureTenantOperationalStatus.php`
- `app/Http/Middleware/ResolveTenantFromRoutePath.php`
- `app/Control/Interfaces/Http/Controllers/TenantPortalProxyController.php`
- `app/Control/Application/Services/ClientInstancePortalService.php`
- `app/Control/Application/Services/Tenants/TenantModuleCatalogService.php`
- `app/Control/Application/Services/Tenants/TenantPresentationService.php`
- `app/Control/Application/Services/Tenants/CompanyListingService.php`
- `app/Simulation/Application/Services/Execution/SimulationTenantEligibilityChecker.php`
- `app/Simulation/Application/Services/Execution/SimulationFixtureResolver.php`
- `app/Simulation/Application/Services/Execution/TenantSimulationAutomationService.php`
- `app/Simulation/Application/Services/Orchestration/SimulationRunOrchestrator.php`
- `app/Simulation/Application/Services/Orchestration/SimulationRunQueryService.php`
- `app/Simulation/Interfaces/Http/Controllers/SimulationRunController.php`
- `resources/js/Pages/Control/Companies/Index.vue`

---

## 3. Diagnostico General

### Diagnostico 1 — El entorno actual no es GitHub Ready

El estado actual contiene artefactos de datos y pruebas que impiden certificar un baseline reproducible:

- `deploy/local-instances/fleet-registry.json` registra `acme-retail`, `pruebas-retail`, `lifecycle-test` y `unaprueba`.
- Existen `.env.client-acme-retail`, `.env.client-pruebas-retail`, `.env.client-lifecycle-test` y `.env.client-unaprueba`.
- Existen SQLite y archivos WAL/SHM para tenants locales en `database/instances`.
- Existen handoffs de simulacion en `storage/app/simulation-handoff`.
- El estado Git muestra artefactos con separadores mixtos Windows, por ejemplo `database/instances/...` y `database\instances\...`, lo que debe tratarse como contaminacion de workspace antes de certificar.

Conclusion: no se puede usar el estado actual para decidir si un problema funcional es bug real o dato heredado. Primero debe erradicarse legacy.

### Diagnostico 2 — v1.6 ya documento contaminacion legacy residual

Los informes v1.6 certifican la estabilizacion, pero tambien registran diferencias entre tenants historicos y tenants provisionados con el flujo moderno:

- `Informe_Fase_7.md` indica que `acme-retail` y `pruebas-retail` devolvian 503 en routing amigable por falta de `settings.deployment.local_instance.app_url`.
- `Informe_Fase_8.md` confirma que `lifecycle-test` si tenia `app_url`, mientras `acme-retail` y `pruebas-retail` no.
- `Informe_Fase_4.md` documenta contrasenas legacy importadas desde `database/database.sqlite` y propagadas por mirror.
- `Informe_Fase_4.md` tambien documenta tenants huerfanos `retail-norte` y `retail-sur`.

Conclusion: v1.7 debe validar exclusivamente con tenants nuevos creados tras limpieza total.

### Diagnostico 3 — Tenant Identity depende de varios niveles

La identidad visual no proviene de un unico punto:

- `LocalFleetEnvBuilder` escribe `APP_NAME` y `PLATFORM_CLIENT_NAME` usando el `label` del registry.
- `config/platform.php` define `platform.client_name` como `PLATFORM_CLIENT_NAME` con fallback a `APP_NAME`.
- `DatabaseInstanceTenantContext::clientName()` lee `platform.client_name`.
- `ClientInstancePortalService::branding()` usa `TenantModel.name` si encuentra tenant por `PLATFORM_CLIENT_SLUG`; si no lo encuentra, cae a `clientName()`.
- `LocalFleetTenantMirror::syncTenantSettings()` actualiza `tenants.name` del silo con el nombre del control plane.

Conclusion: si aparece `SaaS` como nombre visual en un silo, las causas probables son: `APP_NAME`/`PLATFORM_CLIENT_NAME` heredado, fila `tenants` ausente o no sincronizada en el silo, `PLATFORM_CLIENT_SLUG` incorrecto, o mirror incompleto.

### Diagnostico 4 — Friendly Routing existe, pero debe certificarse en entorno limpio

La implementacion actual:

- `routes/tenant_portal.php` registra `/{tenant_slug}` al final del routing.
- `ResolveTenantFromRoutePath` valida flag `platform.friendly_routing`, tenant existente, status no suspendido y `settings.deployment.local_instance.app_url`.
- `TenantPortalProxyController` devuelve redirect 302 al silo.
- `bootstrap/app.php` carga `tenant_portal.php` despues de rutas exactas para evitar shadowing.

La documentacion v1.6 certifico `lifecycle-test`, pero reconocio que tenants historicos fallaban por metadata incompleta. Por tanto, v1.7 debe crear tenants temporales nuevos y validar rutas amigables solo contra esos tenants.

Hallazgos adicionales para v1.7:

- El flag `PLATFORM_FRIENDLY_ROUTING` esta documentado como requisito del CP, pero debe verificarse explicitamente en `.env.control-plane`; si falta, `config/platform.php` lo deja en `false` y las rutas amigables retornan 404.
- La resolucion actual valida `local_instance.app_url`, pero no demuestra por si sola que el proceso del silo este vivo. Un redirect 302 puede apuntar a un puerto detenido si el tenant fue provisionado pero no levantado.

### Diagnostico 5 — Elegibilidad de simulacion es demasiado permisiva para el criterio v1.7

La capa UI usa `can_simulate` y deshabilita tenants no elegibles en `resources/js/Pages/Control/Companies/Index.vue`. El backend tambien valida en `SimulationRunOrchestrator::start()` mediante `simulationBlockReason()`.

Sin embargo, `SimulationTenantEligibilityChecker` delega a `SimulationFixtureResolver::hasSimulationSource()`, que retorna true si existe fixture de simulacion. A su vez:

- `SimulationFixtureResolver::resolveFixtureSlug()` cae al fixture default `platform.simulation.fixture_slug`, por defecto `acmepos`.
- `TenantModuleCatalogService::getCatalog()` devuelve un catalogo default si no hay `settings.modules_catalog`, archivo por instancia ni fixture versionado.
- `config/module_blueprint.php` define default catalog con middleware pero sin producers/subscribers.

Conclusion: el criterio v1.7 debe ser mas estricto: una empresa solo es simulable si tiene configuracion explicita de modulos activos y productores con tipos de evento emitidos. La existencia de un fixture default no debe hacer simulable a un tenant sin modulos configurados.

---

## 4. Causas Probables por Problema

### Problema 1 — Legacy persistente

**Causas probables:**

- Fases anteriores conservaron tenants demo/historicos para validacion incremental.
- `npm run instances:fleet-bootstrap` importa tenants legacy desde `database/database.sqlite`.
- `ControlPlaneFleetBootstrapService::importLegacyTenants()` importa especificamente `acme-retail` y `pruebas-retail`.
- Los artefactos locales no estan completamente ignorados o eliminados del workspace.
- SQLite WAL/SHM quedan como subproducto de ejecuciones locales.

**Evidencia:**

- `README.md` documenta que `instances:fleet-bootstrap` importa `acme-retail` y `pruebas-retail` desde `database/database.sqlite`.
- `ControlPlaneFleetBootstrapService.php` implementa ese import legacy.
- `Informe_Fase_4.md` documenta hashes legacy importados.
- `fleet-registry.json` actual contiene tenants historicos y temporales.

### Problema 2 — Reconstruccion no concluyente si arranca desde datos viejos

**Causas probables:**

- Bootstrap y fleet-bootstrap pueden regenerar artefactos a partir de legacy.
- Un tenant historico puede seguir funcionando por puerto aunque no tenga metadata moderna.
- La suite puede pasar con datos corregidos manualmente sin demostrar greenfield real.

**Evidencia:**

- `Informe_Fase_2.md` declara `npm run instances:fleet-bootstrap` como "Import legacy + mirror OK".
- `Informe_Fase_8.md` muestra silos historicos operativos por puerto aunque sin `app_url`.

### Problema 3 — Tenant Identity muestra `SaaS`

**Causas probables:**

- `.env.client-*` generado con `APP_NAME` o `PLATFORM_CLIENT_NAME` incorrecto.
- `PLATFORM_CLIENT_SLUG` apunta a un slug que no existe en la tabla `tenants` del silo.
- El tenant existe pero el mirror no copio `name` desde el control plane.
- El branding cae al fallback de `DatabaseInstanceTenantContext::clientName()`.
- El tenant fue creado antes del flujo moderno de provisioning.

**Evidencia:**

- `LocalFleetEnvBuilder.php` genera `APP_NAME` y `PLATFORM_CLIENT_NAME`.
- `ClientInstancePortalService.php` usa `TenantModel.name` y luego fallback a `clientName()`.
- `LocalFleetTenantMirror.php` actualiza `tenants.name` en el silo.

### Problema 4 — Friendly Routing no probado sobre baseline limpio

**Causas probables:**

- Validaciones v1.6 combinaron tenants historicos y un tenant moderno (`lifecycle-test`).
- `ResolveTenantFromRoutePath` requiere `settings.deployment.local_instance.app_url`; tenants viejos no lo tienen.
- El redirect por puerto no prueba sesiones cross-path; solo prueba resolucion y metadata de silo.
- El redirect tampoco prueba que el proceso del silo este escuchando; requiere validacion de lifecycle `running` o health/port check posterior.
- El flag puede estar ausente o desactivado en `.env.control-plane`, aun si la documentacion lo describe como habilitado.

**Evidencia:**

- `Informe_Fase_7.md` certifica `lifecycle-test` y registra 503 para `acme-retail`/`pruebas-retail`.
- `ResolveTenantFromRoutePath.php` aborta 503 si falta `local_instance.app_url`.
- `TenantPortalProxyController.php` implementa redirect 302.
- `config/platform.php` define `friendly_routing` con default `false`.

### Problema 5 — Empresa sin modulos participa en simulacion

**Causas probables:**

- `SimulationFixtureResolver::hasSimulationSource()` acepta fixture default.
- `TenantModuleCatalogService::getCatalog()` devuelve catalogo default aunque no exista configuracion explicita.
- La validacion no distingue entre "catalogo default de UI" y "modulos contratados/configurados".
- `tenantFilterOptions()` en historial de simulaciones lista tenants sin anotar elegibilidad.

**Evidencia:**

- `SimulationTenantEligibilityChecker.php` valida status y existencia de source, no modulos explicitos.
- `SimulationFixtureResolver.php` permite fixture default.
- `TenantModuleCatalogService.php` retorna `defaultCatalogForTenant()`.
- `config/module_blueprint.php` define default sin producers/subscribers.
- `CompanyListingService.php` expone `can_simulate` para el index, pero la elegibilidad depende del checker actual.

### Problema 6 — Baseline reproducible incompleto

**Causas probables:**

- El README actual todavia documenta import legacy en `instances:fleet-bootstrap`.
- El entorno local esta orientado a demos con Acme/Pruebas.
- Los assets compilados, SQLite y envs locales aparecen en el workspace.

**Evidencia:**

- `README.md` y `deploy/local-instances/README.md` documentan `acme-retail` y `pruebas-retail`.
- Git status actual muestra cambios en `public/build`, bases SQLite, `.env.client-*` y registros locales.

### Problema 7 — Limpieza final no certificada

**Causas probables:**

- Las fases previas validaron con tenants persistentes.
- Los handoffs de simulacion quedan en `storage/app/simulation-handoff`.
- Los archivos WAL/SHM quedan abiertos por procesos SQLite/PHP.
- No hay una fase GitHub Ready que compare filesystem, registry, CP DB y silos.

**Evidencia:**

- `Informe_Fase_0.md` v1.6 limpio handoffs/logs/launchers, pero el estado actual vuelve a mostrar handoffs.
- Existen handoffs bajo `storage/app/simulation-handoff`.

---

## 5. Estrategia de Solucion

### Principio rector

No corregir sobre tenants historicos. Primero destruir el contexto contaminado. Despues crear tenants temporales nuevos. Solo si el problema se reproduce en tenants nuevos desde entorno limpio, se clasifica como error real de implementacion.

### Clasificacion de resultados

| Resultado observado tras limpieza total | Clasificacion |
|---|---|
| El problema desaparece con tenants nuevos | Legacy / datos historicos |
| El problema persiste con tenants nuevos | Bug real de implementacion |
| El problema aparece solo tras mirror o restore | Bug en mirror/lifecycle |
| El problema aparece solo si se ejecuta `fleet-bootstrap` legacy | Contaminacion por import legacy |
| El problema aparece solo con fixtures default | Bug de elegibilidad/configuracion de simulacion |

---

## 6. Estrategia de Limpieza Inicial

### Objetivo

Dejar el workspace sin tenants, silos, simulaciones ni artefactos heredados. Debe sobrevivir solo Control Plane, configuracion base, codigo fuente, scripts y documentacion.

### Alcance de erradicacion

Eliminar o resetear, en una ejecucion controlada futura:

- Tenants historicos del control plane, excepto el tenant tecnico `platform` si existe como identidad del CP.
- Silos historicos.
- SQLite de tenants en `database/instances`.
- WAL/SHM de SQLite.
- `.env.client-*`.
- `config/modules/instances/*`.
- `deploy/local-instances/fleet-registry.json` de clientes.
- Handoffs en `storage/app/simulation-handoff`.
- Launchers en `storage/app/simulation-launchers`.
- Logs de simulacion `storage/logs/simulation-*.log`.
- Runs historicas en tablas de simulacion.
- Usuarios operadores de tenants eliminados.
- Mirrors y settings de deployment asociados a tenants eliminados.

### Precondiciones

- Detener servidores locales antes de tocar SQLite para evitar WAL/SHM activos.
- Respaldar solo si se necesita trazabilidad forense, no para reinyectar datos.
- No ejecutar `instances:fleet-bootstrap` legacy durante la certificacion v1.7 salvo como prueba controlada de contaminacion.

### Criterio de aceptacion

- `deploy/local-instances/fleet-registry.json` sin instancias cliente.
- `database/instances` sin SQLite de tenants; puede conservar `.gitkeep` y, si aplica, `platform.sqlite`.
- Cero `.env.client-*`.
- Cero `config/modules/instances/*`.
- Cero `storage/app/simulation-handoff/*`.
- Cero `storage/app/simulation-launchers/*`.
- Cero `storage/logs/simulation-*.log`.
- Control plane accesible y sin tenants comerciales.

---

## 7. Estrategia de Reconstruccion Limpia

### Flujo objetivo

```text
Control Plane limpio
  -> Bootstrap base
  -> Provisioning moderno
  -> Tenant temporal nuevo
  -> Lifecycle: Levantar
  -> Login en silo
  -> Branding por tenant
  -> Friendly Routing
  -> Configuracion explicita de modulos
  -> Simulacion elegible
  -> Operacion
  -> Limpieza final
```

### Reglas

- No usar `acme-retail`, `pruebas-retail`, `lifecycle-test`, `retail-norte`, `retail-sur` ni `unaprueba` para certificar v1.7.
- Usar slugs temporales con prefijo claro, por ejemplo:
  - `tenant-test-routing`
  - `tenant-test-branding`
  - `tenant-test-simulation`
- Cada tenant temporal debe crearse desde `/control/provisioning`.
- Cada tenant temporal debe tener operador propio, env propio, SQLite propio y entrada registry generada por provisioning moderno.
- El tenant de simulacion debe recibir modulos configurados explicitamente antes de ejecutar simulacion.

---

## 8. Estrategia de Certificacion GitHub Ready

El repositorio solo puede declararse GitHub Ready si cumple simultaneamente:

### Certificacion filesystem

- No existen `.env.client-*`.
- No existen SQLite de tenants temporales o historicos.
- No existen WAL/SHM.
- No existen catálogos por instancia.
- No existen handoffs, launchers ni logs de simulacion.
- No existen assets compilados nuevos no destinados a versionarse.
- No existen rutas duplicadas por separador Windows en el estado Git.

### Certificacion Control Plane

- La base del CP no contiene tenants comerciales de prueba.
- No contiene usuarios operadores asociados a tenants de prueba.
- No contiene runs de simulacion de tenants de prueba.
- No contiene registros huerfanos con `tenant_id` inexistente.

### Certificacion fleet

- `fleet-registry.json` no contiene clientes temporales.
- No hay puertos reservados para tenants eliminados.
- No hay procesos PHP sirviendo silos temporales.

### Certificacion docs

- El README debe documentar flujo baseline sin depender de datos heredados.
- Si se mantiene un flujo demo legacy, debe estar separado explicitamente de la ruta GitHub Ready.
- El runbook v1.7 debe registrar evidencia de limpieza final.

---

## 9. Fases del Runbook v1.7

### Fase 0 — Inventario tecnico completo

**Objetivo:** registrar todo dato, artefacto y configuracion que pueda contaminar la validacion.

**Acciones:**

- Inventariar tenants en control plane.
- Inventariar usuarios por `tenant_id`.
- Inventariar `fleet-registry.json`.
- Inventariar `.env.client-*`.
- Inventariar `database/instances/*`.
- Inventariar `config/modules/instances/*`.
- Inventariar runs de simulacion.
- Inventariar `storage/app/simulation-handoff`, `storage/app/simulation-launchers` y `storage/logs/simulation-*`.
- Inventariar procesos activos por puerto.
- Capturar `git status --short` antes de limpiar.

**Criterio de aceptacion:**

- Existe una matriz de artefactos con clasificacion: Control Plane, base, historico, temporal, huerfano, generado, desconocido.

### Fase 1 — Erradicacion total de legacy

**Objetivo:** eliminar todo tenant, silo y artefacto heredado.

**Acciones:**

- Detener procesos locales.
- Vaciar registry de clientes.
- Eliminar `.env.client-*`.
- Eliminar SQLite/WAL/SHM de tenants.
- Eliminar catálogos por instancia.
- Eliminar handoffs, launchers y logs de simulacion.
- Eliminar tenants comerciales y operadores asociados del CP.
- Eliminar runs de simulacion asociadas a tenants comerciales.
- Verificar inexistencia de `acme-retail`, `pruebas-retail`, `lifecycle-test`, `retail-norte`, `retail-sur`, `unaprueba` fuera de documentacion historica.

**Criterio de aceptacion:**

- El CP no lista tenants comerciales.
- El filesystem no contiene silos ni envs de cliente.
- No hay procesos de silos en puertos 8001+.

### Fase 2 — Reconstruccion limpia

**Objetivo:** levantar solo el Control Plane y configuracion base sin datos legacy.

**Acciones:**

- Ejecutar bootstrap base sin import legacy.
- Verificar que el CP responde.
- Verificar que `/control/companies` no muestra tenants.
- Verificar que no se crearon silos automaticamente.
- Verificar que `fleet-registry.json` sigue sin clientes.

**Criterio de aceptacion:**

- Control Plane operativo.
- Cero tenants comerciales.
- Cero silos.
- Cero simulaciones.

### Fase 3 — Provisioning moderno desde cero

**Objetivo:** crear tenants temporales exclusivamente por provisioning moderno.

**Acciones:**

- Crear `tenant-test-branding` con nombre visual unico.
- Crear `tenant-test-routing` con nombre visual unico.
- Crear `tenant-test-simulation` con nombre visual unico.
- Validar que cada tenant tiene:
  - fila en CP;
  - operador;
  - entrada registry;
  - `.env.client-*`;
  - SQLite;
  - `settings.deployment.local_instance.app_url`;
  - `settings.deployment.lifecycle`.

**Criterio de aceptacion:**

- Todos los tenants nuevos tienen metadata moderna.
- Ningun tenant historico reaparece.

### Fase 4 — Validacion Tenant Identity

**Objetivo:** demostrar si el branding incorrecto es legacy o bug real.

**Acciones:**

- Para cada tenant temporal, validar `.env.client-*`:
  - `APP_NAME`
  - `PLATFORM_CLIENT_NAME`
  - `PLATFORM_CLIENT_SLUG`
- Validar tabla `tenants` del silo:
  - `slug`
  - `name`
  - `status`
- Validar UI de login/dashboard del silo.
- Validar props compartidas de Inertia si aplica:
  - `company_name`
  - `company_slug`
- Validar que ningun silo temporal muestra `SaaS` salvo el Control Plane.

**Criterio de aceptacion:**

- Cada tenant muestra su identidad propia.
- Si aparece `SaaS`, clasificar punto exacto de fallo: env, bootstrap, mirror, tenant row o frontend.

### Fase 5 — Correccion Tenant Identity

**Objetivo:** definir correcciones solo si Fase 4 reproduce el problema en tenants nuevos.

**Acciones esperadas si falla:**

- Si falla env: corregir `LocalFleetEnvBuilder`.
- Si falla bootstrap: corregir `platform:instance:bootstrap` o seeder de instancia.
- Si falla mirror: corregir `LocalFleetTenantMirror`.
- Si falla fallback: corregir `ClientInstancePortalService` para no ocultar ausencia de tenant con un nombre generico.
- Si falla frontend: corregir componente o layout que renderiza nombre visual.

**Criterio de aceptacion:**

- Tenant temporal vuelve a mostrar identidad correcta tras reprovision limpio.
- No se acepta data fix manual como solucion final.

### Fase 6 — Validacion Friendly Routing

**Objetivo:** certificar routing amigable solo con tenants nuevos.

**Acciones:**

- Activar `PLATFORM_FRIENDLY_ROUTING=true` solo en CP.
- Verificar que `.env.control-plane` contiene `PLATFORM_FRIENDLY_ROUTING=true` y que los `.env.client-*` no lo activan.
- Levantar el servicio del tenant antes de certificar la URL amigable.
- Validar `GET /tenant-test-routing/login` retorna 302 al `app_url` del silo.
- Validar que el destino del 302 responde HTTP 200 en `/login`.
- Validar root `GET /tenant-test-routing` redirige a `/login`.
- Validar path anidado redirige correctamente.
- Validar tenant inexistente retorna 404.
- Validar tenant suspendido retorna 503.
- Validar tenant sin `local_instance.app_url` retorna 503.
- Validar tenant provisionado pero no levantado: no debe certificarse como operativo solo por recibir 302.
- Validar que rutas exactas `/control/*`, `/health/ready`, `/up`, `/login` no son capturadas por wildcard.

**Criterio de aceptacion:**

- Routing amigable funciona para tenants nuevos con metadata moderna.
- Ninguna conclusion se basa en `acme-retail`, `pruebas-retail` o `lifecycle-test`.

### Fase 7 — Correccion Elegibilidad Simulacion

**Objetivo:** impedir que empresas sin modulos configurados participen en simulaciones.

**Regla objetivo:**

Una empresa solo es simulable si cumple todo:

- `tenant.status === active`.
- `settings.modules_catalog` existe explicitamente.
- `modules_catalog.producers` contiene al menos un productor.
- Al menos un productor define `event_types_emitted` no vacio.
- El tenant tiene silo moderno si la simulacion se delega a cliente.
- El tenant no depende exclusivamente de fixture default.

**Acciones esperadas:**

- Ajustar el checker para distinguir catalogo explicito vs fallback.
- Ajustar UI para mostrar motivo de bloqueo.
- Ajustar backend para rechazar POST aunque el frontend sea manipulado.
- Agregar pruebas para tenant sin modulos, tenant solo con middleware, tenant con producer sin eventos y tenant elegible.

**Criterio de aceptacion:**

- `tenant-test-branding` sin modulos no es simulable.
- `tenant-test-simulation` sin modulos no es simulable.
- `tenant-test-simulation` con modulos explicitos si es simulable.
- La simulacion no arranca por fixture default cuando faltan modulos.

### Fase 8 — Pruebas Integrales

**Objetivo:** validar flujo completo post-correcciones.

**Matriz minima:**

- Control Plane limpio.
- Provisioning moderno.
- Lifecycle start/suspend/restore.
- Login por silo.
- Branding por tenant.
- Friendly routing.
- Simulacion bloqueada sin modulos.
- Simulacion permitida con modulos explicitos.
- Reporte de simulacion.
- Aislamiento de tenant_id.
- Rechazo de operador en silo incorrecto.
- Health endpoints.

**Criterio de aceptacion:**

- Suite automatizada verde.
- Validacion HTTP real verde.
- No hay artefactos legacy usados como precondicion.

### Fase 9 — Limpieza Final Operativa

**Objetivo:** eliminar todos los tenants temporales y artefactos creados por v1.7.

**Acciones:**

- Detener procesos de silos temporales.
- Eliminar tenants temporales del CP.
- Eliminar usuarios operadores temporales.
- Eliminar runs de simulacion temporales.
- Eliminar registry entries temporales.
- Eliminar `.env.client-tenant-test-*`.
- Eliminar SQLite/WAL/SHM de tenants temporales.
- Eliminar catálogos por instancia temporales.
- Eliminar handoffs, launchers y logs de simulacion.
- Limpiar caches generados si no son parte del baseline.

**Criterio de aceptacion:**

- No existe ningun `tenant-test-*` en CP, filesystem, registry, logs, handoffs, runs ni git status.

### Fase 10 — Certificacion GitHub Ready

**Objetivo:** demostrar que el repositorio queda como baseline oficial para futuras versiones.

**Checklist final:**

- Cero tenants de prueba.
- Cero tenants historicos operativos.
- Cero silos de prueba.
- Cero SQLite de prueba.
- Cero WAL/SHM.
- Cero `.env.client-*`.
- Cero mirrors temporales.
- Cero registros huerfanos.
- Cero simulaciones temporales.
- Cero handoffs/launchers/logs temporales.
- `git status --short` no muestra artefactos runtime generados.
- README y runbooks describen un flujo reproducible desde clone limpio.

**Declaracion de salida:**

```text
CERTIFICACION v1.7: GITHUB READY

Control Plane: presente
Configuracion base: presente
Codigo fuente: presente
Scripts: presentes
Documentacion: presente
Tenants comerciales/provisionales: ausentes
Silos locales: ausentes
Simulaciones: ausentes
Artefactos runtime: ausentes
```

---

## 10. Evidencia Encontrada

### Lifecycle y provisioning

- `ADR_010_tenant_lifecycle_management.md` define `tenants.status` como estado comercial y `settings.deployment.lifecycle` como estado de infraestructura.
- `LocalFleetInstanceProvisioner.php` escribe `settings.deployment.lifecycle = provisioned` y `local_instance.app_url`.
- `EnsureTenantOperationalStatus.php` bloquea acceso en silos suspendidos.
- `routes/control.php` expone endpoints lifecycle `start`, `suspend`, `restore` y `status`.

### Fleet y legacy

- `README.md` documenta que `instances:fleet-bootstrap` importa tenants legacy desde `database/database.sqlite`.
- `ControlPlaneFleetBootstrapService.php` importa `acme-retail` y `pruebas-retail`.
- `Informe_Fase_4.md` documenta contraseñas legacy incorrectas importadas.
- `fleet-registry.json` actual contiene tenants historicos y temporales.

### Routing

- `ADR_011_friendly_routing_multitenant.md` acepta routing por ruta con redirect 302.
- `routes/tenant_portal.php` implementa `/{tenant_slug}`.
- `ResolveTenantFromRoutePath.php` valida flag, slug, status y `local_instance.app_url`.
- `TenantPortalProxyController.php` redirige al silo.
- `Informe_Fase_7.md` certifica `lifecycle-test` y registra 503 para historicos sin metadata.

### Branding

- `LocalFleetEnvBuilder.php` genera `APP_NAME` y `PLATFORM_CLIENT_NAME`.
- `config/platform.php` resuelve `client_name`.
- `DatabaseInstanceTenantContext.php` expone `clientSlug()` y `clientName()`.
- `ClientInstancePortalService.php` usa el nombre del tenant si existe, si no cae a config.
- `LocalFleetTenantMirror.php` sincroniza `tenants.name` al silo.

### Simulacion

- `SimulationTenantEligibilityChecker.php` no valida modulos explicitos.
- `SimulationFixtureResolver.php` acepta fixture default como fuente.
- `TenantModuleCatalogService.php` retorna catalogo default cuando falta configuracion.
- `config/module_blueprint.php` define default sin producers/subscribers.
- `SimulationRunOrchestrator.php` valida elegibilidad en backend antes de iniciar run.
- `resources/js/Pages/Control/Companies/Index.vue` usa `can_simulate` para bloquear UI.

---

## 11. Decision Arquitectonica v1.7

Para v1.7, la compatibilidad con tenants historicos no debe condicionar la certificacion. Los tenants historicos solo sirven como evidencia forense. La plataforma se certifica con tenants nuevos, temporales y eliminados al final.

Si un flujo solo falla en `acme-retail`, `pruebas-retail`, `lifecycle-test`, `retail-norte`, `retail-sur` o `unaprueba`, el resultado se clasifica como contaminacion legacy salvo que se reproduzca en un tenant creado desde cero.

Si un flujo falla en `tenant-test-*` creado post-limpieza, el resultado se clasifica como bug real y debe corregirse antes de GitHub Ready.

---

## 12. Riesgos y Controles

| Riesgo | Control v1.7 |
|---|---|
| Reintroducir legacy via `instances:fleet-bootstrap` | Separar modo demo legacy de modo GitHub Ready |
| Certificar con tenants viejos | Prohibir historicos en Fases 3-8 |
| Data fix manual ocultando bug | Reprovisionar desde cero tras cada correccion |
| Simulacion por fixture default | Exigir modulos explicitos |
| Branding por fallback generico | Verificar env, tenant row, mirror e Inertia props |
| WAL/SHM retenidos por procesos vivos | Detener procesos antes de limpiar |
| Registry con puertos huerfanos | Validar registry contra CP y filesystem |
| Git status contaminado | Fase 10 bloquea si hay runtime artifacts |

---

## 13. Resultado Final Esperado

Al completar v1.7:

- Se habra demostrado si los hallazgos actuales son legacy o bugs reales.
- El branding por tenant estara validado desde provisioning moderno.
- Friendly routing estara validado con tenants nuevos.
- Simulacion estara restringida a empresas con modulos activos y configurados.
- Todos los tenants temporales habran sido eliminados.
- El repositorio quedara certificado como baseline GitHub Ready.

Este runbook es la hoja de ruta oficial para ejecutar esa certificacion sin depender de datos heredados ni de tenants historicos.
