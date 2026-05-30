# Runbook v1.5 — Gestión del ciclo de vida operativo de tenants (Lifecycle Management)

**Versión del plan:** 1.5  
**Estado del documento:** Borrador para revisión arquitectónica (sin implementación de código)  
**Fecha:** 2026-05-30  
**Repositorio:** `omnichannel-ddd-eda` (`platform/event-bus-core`)  
**Alcance:** Plan de implementación seguro y compatible con ADR-001 (instancia por cliente)

---

## Resumen ejecutivo

El sistema actual permite **registrar empresas** (tenants) desde el módulo **Gestión de Empresas** del control plane (`/control/companies`, `/control/provisioning`) y, en desarrollo local con `PLATFORM_LOCAL_FLEET_AUTO_PROVISION=true`, **provisionar artefactos** de silo (`.env.{id}`, SQLite, `fleet-registry.json`, catálogo) mediante `LocalFleetInstanceProvisioner`.

Sin embargo, la **activación operativa** de un silo recién provisionado en entorno local exige **reiniciar** el comando `npm run instances:serve`, porque ese script lee el manifest (`fleet-registry.json`) **una sola vez al arrancar** y lanza un proceso `php artisan serve` por instancia conocida en ese momento (`scripts/local-instances/serve.mjs`).

Paralelamente, las acciones **Suspender** y **Activar** existentes (`TenantAdminService::suspend` / `activate`) solo mutan `tenants.status` en la BD del control plane y **no bloquean** el acceso al portal del silo cliente: no hay middleware que intercepte rutas por estado suspendido, y `AuthenticateOperatorUseCase` no valida `tenant.status`.

La versión **1.5** debe introducir un **ciclo de vida operativo explícito** con tres acciones en Gestión de Empresas — **Levantar servicio**, **Suspender servicio**, **Restaurar servicio** — sin reiniciar backend, frontend ni servicios auxiliares en el escenario local, y con equivalente semántico en producción (orquestación de despliegue por instancia).

---

## 1. Diagnóstico del estado actual (evidencia)

### 1.1 Provisioning existente

| Paso | Componente | Qué hace | Evidencia |
|------|------------|----------|-----------|
| Alta tenant CP | `TenantAdminService::create` | Crea fila `tenants` con `status=active`, settings iniciales | `app/Control/Application/Services/Tenants/TenantAdminService.php` |
| Alta con operador | `ProvisionNewTenantService::provision` | Tenant + operador + fleet provision | `ProvisionNewTenantService.php` |
| Alta simple | `CompanyController::store` | Tenant + `localFleet->provision` | `CompanyController.php` |
| Fleet local | `LocalFleetInstanceProvisioner::provision` | Registry JSON, `.env`, SQLite, migrate, bootstrap, mirror | `LocalFleetInstanceProvisioner.php` |
| Mirror | `LocalFleetTenantMirror::mirror` | Copia settings, operadores, `modules_config.json` al silo | `LocalFleetTenantMirror.php` |

Tras provision exitoso, `markTenantProvisioned` escribe en `tenant.settings.deployment`:

```json
{
  "mode": "instance_per_client",
  "status": "active_on_instance",
  "local_instance": { "app_url", "port", "env_file", "env_id", "db_path" }
}
```

Si fleet está deshabilitado, queda `pending_dedicated_instance` (`ProvisionNewTenantService.php` líneas 67–77).

### 1.2 Por qué hoy se requiere reinicio

| Causa raíz | Explicación técnica | Evidencia |
|------------|---------------------|-----------|
| **Manifest estático al arranque** | `serve.mjs` invoca `loadManifest()` una vez; no observa cambios posteriores en `fleet-registry.json` | `scripts/local-instances/serve.mjs` L16–17, L58–72 |
| **Un proceso OS por silo** | Cada cliente corre `php artisan serve --env={id} --port={port}` como proceso hijo independiente | `scripts/local-instances/lib.mjs` → `spawnArtisanServe` |
| **Config inmutable por proceso** | `PLATFORM_CLIENT_SLUG`, `DB_DATABASE`, `APP_URL` se cargan al boot del proceso Laravel vía `.env.{id}` | ADR-001; `LocalInstanceEnvironmentLoader` |
| **Sin supervisor de procesos** | `LocalFleetInstanceProvisioner` termina tras bootstrap; **no** invoca `spawnArtisanServe` ni equivalente | `LocalFleetInstanceProvisioner.php` — ausencia de spawn |
| **Puerto asignado en registry** | Puerto reservado en JSON pero sin listener HTTP hasta reinicio de `instances:serve` | `LocalFleetRegistry::nextAvailablePort` |

**Conclusión:** el reinicio no es un requisito de Laravel en sí, sino una **limitación del orquestador local actual** (`serve.mjs`), no documentada como deuda explícita.

### 1.3 Componentes registrados solo en boot (afectados por nuevo silo)

| Componente | Carga en boot | ¿Dinámico por request? |
|------------|---------------|------------------------|
| Variables `.env.{instanceId}` | Sí — por proceso `--env=` | No — un proceso = un silo |
| `config/platform.php` (`client_slug`, `control_plane`) | Sí | No en silo dedicado |
| `config/eventbus.php` + overlays | Sí (`config:cache` en prod) | No sin recarga |
| Service Providers / DI bindings | Sí | No |
| `DatabaseInstanceTenantContext` cache tenant id | Sí — resuelve slug al primer uso | Parcial — `refreshTenantCache()` existe |
| Manifest `instances:serve` | Solo al iniciar Node script | No |
| Rutas Laravel | Boot de aplicación | No cambian por tenant en silo |
| Listeners / subscriptions eventbus | Boot (`EventBusIntegrationServiceProvider`) | No |

En **control plane** (`PLATFORM_CONTROL_PLANE=true`), múltiples tenants coexisten en una BD; no se requiere nuevo proceso PHP por tenant en CP — el reinicio afecta principalmente al **silo cliente local**.

### 1.4 Suspensión / activación actuales

| Aspecto | Comportamiento actual | Evidencia |
|---------|----------------------|-----------|
| UI | Botones «Suspender» / «Activar» en `Companies/Show.vue` | `tenant.status === 'active'` |
| Backend CP | `TenantAdminService` → `status = suspended \| active` | `TenantAdminService.php` |
| Rutas | `POST .../suspend`, `POST .../activate` | `routes/control.php` |
| Mirror | `LocalFleetTenantMirror::syncTenantSettings` copia `status` al silo **si se ejecuta mirror** | `LocalFleetTenantMirror.php` |
| Login silo | **No** consulta `tenant.status` | `AuthenticateOperatorUseCase.php` |
| Middleware portal | **No** consulta suspensión | `InstancePortalAccessGuard.php` |
| API silo | **No** bloqueo por suspensión | `MiddlewareApiRoutes.php` |
| Simulación | `SimulationTenantEligibilityChecker` rechaza `status !== 'active'` | Único uso encontrado de status operativo |

**Conclusión:** suspender hoy es un **flag administrativo en CP** sin efecto de bloqueo en el portal del cliente.

### 1.5 Modelo multi-tenant vs instancia por cliente

| Concepto documentado | Estado runtime |
|---------------------|----------------|
| ADR-001: instancia por cliente | **Activo** — silo dedicado por cliente comercial |
| Multi-tenant lógico en una URL | **Diferido** (ADR-001 Fase 3) |
| `tenant_id` en tablas operativas | Preparado; aislamiento físico por BD en silo |
| Control plane multi-tenant | **Activo** — registro de N empresas en BD `platform` |

El plan v1.5 debe respetar: **lifecycle del silo**, no convertir el silo en multi-tenant compartido.

---

## 2. Modelo de estados propuesto (v1.5)

### 2.1 Estados operativos

| Estado | Código propuesto | Descripción | Transiciones permitidas |
|--------|------------------|-------------|-------------------------|
| **Provisionado** | `provisioned` | Tenant y artefactos creados (BD, `.env`, registry); **proceso HTTP no activo** o instancia cloud no desplegada | → `active` (Levantar) |
| **Activo** | `active` | Silo operativo; login, dashboard y API habilitados | → `suspended` (Suspender) |
| **Suspendido** | `suspended` | Acceso bloqueado; página institucional de suspensión | → `active` (Restaurar) |
| **Restaurable** | *(derivado, no persistido)* | Alias lógico: `status === suspended` | — |

**Nota:** el código actual usa `tenants.status` con valores `active` / `suspended` (`middleware_database_dictionary.md`) y `settings.deployment.status` con `active_on_instance` / `pending_dedicated_instance`. v1.5 debe **unificar semántica** sin romper datos existentes.

### 2.2 Estrategia de persistencia recomendada

| Campo | Ubicación | Propósito |
|-------|-----------|-----------|
| `tenants.status` | BD control plane **y** silo (via mirror) | `active` \| `suspended` — **autoritativo para acceso** |
| `settings.deployment.lifecycle` | JSON settings (nuevo objeto) | `provisioned` \| `running` \| `stopped` — **estado infra local/cloud** |
| `settings.deployment.local_instance` | Ya existe | Metadatos puerto, env_id, app_url |
| `fleet-registry.json` | `deploy/local-instances/` | Manifest procesos locales; añadir `runtime_status` opcional |
| Cache | Redis/file opcional | Lectura rápida en middleware; **invalidar en transición** |
| Eventos | Bus interno (EDA) | `Tenant.Lifecycle.*` — auditoría y sync asíncrono |

**Recomendación:** no introducir tabla nueva en v1.5 salvo necesidad de historial; extender `settings.deployment` y reutilizar `tenants.status` para suspensión.

### 2.3 Mapeo desde estado actual

| Situación actual | Estado v1.5 propuesto |
|------------------|----------------------|
| Tenant creado + fleet provision OK + **sin** `serve` | `lifecycle=provisioned`, `status=active` (pendiente Levantar) |
| Tenant creado + proceso `serve` activo | `lifecycle=running`, `status=active` |
| `pending_dedicated_instance` (prod sin fleet) | `lifecycle=provisioned`, despliegue manual pendiente |
| `suspend()` actual | `status=suspended`, `lifecycle=stopped` (proceso detenido opcional) |
| `activate()` actual | Solo `status=active`; **falta** Levantar si proceso no corre |

---

## 3. Diseño funcional de las tres acciones

### 3.1 Levantar servicio

**Objetivo:** tenant provisionado pasa a operativo **sin reiniciar** control plane ni otros silos.

#### Precondiciones

- Tenant existe en CP con artefactos de silo (`local_instance` o guía de despliegue completada).
- `settings.deployment.lifecycle` ∈ `{ provisioned, stopped }`.
- `tenants.status !== suspended`.

#### Flujo propuesto (desarrollo local)

```text
[UI] Levantar servicio
  → POST /control/companies/{tenant}/lifecycle/start
  → TenantLifecycleOrchestrator (Application)
      1. Validar precondiciones
      2. Si falta silo: LocalFleetInstanceProvisioner::provision (idempotente)
      3. LocalFleetProcessSupervisor::ensureRunning(env_id, port)
           → spawn php artisan serve --env={id} --port={port} (detached)
      4. Actualizar settings.deployment.lifecycle = running
      5. LocalFleetTenantMirror::mirror (operadores + status)
      6. Emitir evento Tenant.Lifecycle.Started
  → Respuesta UI: URL operativa + health check
```

#### Flujo propuesto (producción)

Equivalente semántico vía **orquestador externo** (no reinicio monolito):

- Disparar pipeline CI/CD o API Kubernetes (scale deployment 0→1, o crear release Helm).
- Actualizar `settings.deployment.lifecycle = running` tras health `/health/ready` OK.
- Documentar en runbook cloud existente (`Plan_Cloud.md`, `Runbook_Deploy_VM.md`).

#### Artefactos nuevos (planificados, no implementados)

| Artefacto | Capa DDD | Responsabilidad |
|-----------|----------|-----------------|
| `TenantLifecycleOrchestrator` | Control/Application | Coordina transiciones |
| `StartTenantServiceUseCase` | Control/Application | Caso de uso Levantar |
| `LocalFleetProcessSupervisor` | Shared/Platform | Gestión procesos OS |
| `TenantLifecyclePolicy` | Control/Domain | Reglas de transición |
| `EnsureTenantServiceRunningMiddleware` | Http | Opcional health en CP |

#### Criterio de éxito

- `curl {app_url}/health/ready` → 200 sin reiniciar `instances:serve` global.
- Login operador en `{app_url}/login` funcional.
- Otros silos en ejecución **no** se interrumpen.

---

### 3.2 Suspender servicio

**Objetivo:** bloquear acceso al silo; mostrar página controlada; **no** login ni dashboard.

#### Punto de interceptación recomendado

| Capa | Orden | Justificación |
|------|-------|---------------|
| **`EnsureTenantOperationalStatus`** (nuevo) | **Antes** de `auth` en rutas web/API del silo | Bloquea incluso `/login`; cumple requisito «NO login» |
| Excluir | `/up`, `/health/ready`, assets estáticos | Probes y Vite build |
| Control plane | No aplicar bloqueo total; CP sigue gestionando | `EnsureControlPlaneHost` |

#### Comportamiento HTTP

| Ruta solicitada | Respuesta si `status=suspended` |
|-----------------|--------------------------------|
| `/`, `/login`, `/dashboard`, `/middleware` | **200** vista `TenantSuspended` (Inertia) o **503** HTML estático |
| `/api/*` | **403** Problem Details: `tenant_suspended` |
| Sesión existente | Invalidar sesión; redirect a página suspensión |

#### Mensaje UI (requerimiento negocio)

> «El servicio asociado a esta empresa se encuentra temporalmente suspendido. Contacte al administrador para obtener más información.»

Vista propuesta: `resources/js/Pages/Tenant/Suspended.vue` (sin formulario login).

#### Flujo CP → silo

```text
[UI] Suspender
  → TenantAdminService::suspend (existente)
  → LocalFleetTenantMirror::mirror (propagar status al silo)
  → Opcional: LocalFleetProcessSupervisor::stop(env_id) — política configurable
  → Emitir Tenant.Lifecycle.Suspended
```

**Decisión de diseño:** ¿detener proceso en suspensión?

| Opción | Pros | Contras |
|--------|------|---------|
| A — Solo flag BD | Restaurar instantáneo | Proceso sigue consumiendo puerto/RAM |
| B — Flag + stop proceso | Ahorro recursos local | Levantar de nuevo al restaurar |

**Recomendación v1.5:** Opción A en producción cloud; Opción B opcional en local dev (`PLATFORM_LOCAL_FLEET_STOP_ON_SUSPEND=true`).

---

### 3.3 Restaurar servicio

**Objetivo:** reactivar tenant suspendido.

#### Precondiciones

- `tenants.status === suspended` (**obligatorio**; rechazar si ya `active`).
- Operador SaaS con ability `control:manage` o `tenants:manage`.

#### Flujo

```text
[UI] Restaurar servicio (visible solo si suspended)
  → RestoreTenantServiceUseCase
      1. Validar status === suspended
      2. TenantAdminService::activate
      3. LocalFleetTenantMirror::mirror
      4. Si lifecycle != running: invocar StartTenantServiceUseCase
      5. Emitir Tenant.Lifecycle.Restored
```

#### Reglas UI

Reemplazar par actual Suspender/Activar en `Show.vue` por tres acciones contextuales:

| Estado lifecycle | status | Acciones visibles |
|------------------|--------|-------------------|
| provisioned | active | **Levantar servicio** |
| running | active | **Suspender servicio** |
| running | suspended | **Restaurar servicio** |
| stopped | suspended | **Restaurar servicio** (+ Levantar si aplica) |

---

## 4. Análisis arquitectónico

### 4.1 DDD — Ubicación de responsabilidades

| Concepto | Bounded Context | Capa |
|----------|-----------------|------|
| Política lifecycle | Control | Domain — `TenantLifecyclePolicy` |
| Orquestación | Control | Application — `TenantLifecycleOrchestrator` |
| Persistencia estado | Shared/Infrastructure | `TenantModel` (existente) |
| Supervisor procesos local | Shared/Platform | Infrastructure — acoplado a dev fleet |
| Bloqueo acceso silo | Http + Shared/Platform | Middleware + `TenantOperationalStatusReader` |
| Eventos dominio | Control/Domain o Shared/Events | `TenantLifecycleSuspended`, etc. |

**No modificar** núcleo Middleware/Dashboard más allá del middleware transversal de suspensión.

### 4.2 EDA — Eventos propuestos

| Evento | Productor | Consumidores potenciales |
|--------|-----------|------------------------|
| `Tenant.Lifecycle.Provisioned` | Provision existente | Auditoría, inventario |
| `Tenant.Lifecycle.Started` | Start use case | Mirror async, métricas |
| `Tenant.Lifecycle.Suspended` | Suspend use case | Invalidación cache, alertas |
| `Tenant.Lifecycle.Restored` | Restore use case | Mirror, reinicio worker simulación |

Publicación vía `EventBusPort` o eventos Laravel internos; **no** requiere Kafka en v1.5.

### 4.3 CQRS

- **Commands:** Start, Suspend, Restore → mutan `tenants` + `settings.deployment` + procesos.
- **Queries:** `TenantPresentationService` extendido con `lifecycle_status`, `runtime_health`, `actions_available[]`.

### 4.4 Multi-tenant

v1.5 **no** introduce multi-tenant runtime. Refuerza lifecycle **por silo** coherente con ADR-001. El control plane sigue siendo multi-tenant registry; cada acción lifecycle opera sobre **metadatos + proceso del silo asociado**.

### 4.5 Componentes afectados vs intocables

| Componente | v1.5 | Acción |
|------------|------|--------|
| `LocalFleetInstanceProvisioner` | Afectado | Extraer spawn a supervisor; idempotencia |
| `scripts/local-instances/serve.mjs` | Afectado | Modo supervisor persistente o deprecar en favor de daemon |
| `TenantAdminService` | Afectado | Integrar lifecycle; no romper API actual |
| `CompanyController` | Afectado | Nuevas rutas lifecycle |
| `Companies/Show.vue` | Afectado | Tres botones + estados |
| `InstancePortalAccessGuard` | Afectado | O middleware previo para status |
| `AuthenticateOperatorUseCase` | Opcional | Segunda línea defensa status |
| `Middleware/*` Use Cases | **No tocar** | — |
| `EventPublisherService` | **No tocar** | — |
| `config/eventbus.php` | **No tocar** | — |
| ADR-001 decisión instancia/cliente | **Preservar** | — |

---

## 5. Plan de implementación por fases

### Fase 1 — Análisis y preparación

**Objetivos**

- Validar ADR interno lifecycle v1.5 (extensión ADR-001).
- Inventariar tenants existentes y mapear estados reales en BD.
- Documentar matriz de transiciones y compatibilidad retroactiva.
- Definir feature flags: `PLATFORM_TENANT_LIFECYCLE_V15=true`.

**Riesgos:** estados inconsistentes en `settings.deployment` vs `tenants.status`.  
**Dependencias:** acceso a entornos dev multi-instancia.  
**Artefactos:** ADR-010 (propuesto), script auditoría estados.  
**Criterios de aceptación:** inventario 100% tenants documentado; ADR aprobado.

---

### Fase 2 — Diseño técnico

**Objetivos**

- Especificar `TenantLifecycleOrchestrator` y contratos.
- Diseñar `LocalFleetProcessSupervisor` (API: `ensureRunning`, `stop`, `isRunning`).
- Diseñar middleware `EnsureTenantOperationalStatus`.
- Diseñar vista `TenantSuspended`.
- Definir contrato API interna CP→silo si mirror no basta (opcional).

**Riesgos:** diferencias Windows vs Linux en spawn de procesos.  
**Dependencias:** Fase 1.  
**Artefactos:** diagramas secuencia, OpenAPI rutas `/control/companies/{id}/lifecycle/*`.  
**Criterios de aceptación:** revisión arquitectura sin objeciones bloqueantes.

---

### Fase 3 — Implementación backend

**Objetivos**

- Use cases: `StartTenantServiceUseCase`, `SuspendTenantServiceUseCase`, `RestoreTenantServiceUseCase`.
- Extender `TenantPresentationService` con lifecycle.
- Implementar supervisor procesos (Node o PHP `Symfony Process` detached).
- Middleware suspensión en silo.
- Rutas `routes/control.php` + registro middleware silo.
- Mirror obligatorio post-transición.
- Eventos lifecycle + auditoría (`AuditLogWriter`).

**Riesgos:** race conditions spawn duplicado mismo puerto.  
**Dependencias:** Fase 2.  
**Artefactos:** ~15–25 archivos PHP estimados en Control + Shared/Platform + Http.  
**Criterios de aceptación:** pruebas unitarias policy + integration spawn mock; suspend bloquea `/login` en silo.

---

### Fase 4 — Implementación frontend

**Objetivos**

- Actualizar `Companies/Show.vue` — tres acciones contextuales.
- Crear `Tenant/Suspended.vue`.
- Indicadores estado en `Companies/Index.vue`.
- Confirmaciones modales (Suspender/Restaurar).
- Feedback health check post-Levantar.

**Riesgos:** UX confusa entre «Activar» legacy y «Restaurar».  
**Dependencias:** Fase 3 endpoints.  
**Artefactos:** 3–4 componentes Vue.  
**Criterios de aceptación:** acciones visibles según matriz §3.3; página suspensión sin login.

---

### Fase 5 — Integración

**Objetivos**

- Integrar supervisor con `npm run instances:serve` (modo daemon `--supervisor`) o reemplazo documentado.
- Sincronizar `docs/Plan_Desarrollo_Serviciov1.5` y README.
- Actualizar `deploy/local-instances/README.md`.
- Hook provisioning existente → estado `provisioned` explícito (no `running` hasta Levantar).

**Riesgos:** regresión en `instances:bootstrap` / `fleet-bootstrap`.  
**Dependencias:** Fases 3–4.  
**Criterios de aceptación:** flujo completo provisioning → Levantar → operar sin reinicio manual.

---

### Fase 6 — Pruebas

**Objetivos**

- Unit: `TenantLifecyclePolicy`, transiciones inválidas.
- Feature: POST lifecycle endpoints, RBAC `saas_admin`.
- Feature: middleware suspensión silo (web + API).
- Integration: provision → start → HTTP 200 dashboard.
- E2E Playwright: suspensión muestra página dedicada.
- Regression: silos existentes siguen operando al añadir nuevo tenant.

**Artefactos:** ~10–15 archivos test nuevos.  
**Criterios de aceptación:** CI verde; checklist manual runbook §7 completado.

---

### Fase 7 — Validación final

**Objetivos**

- Validación en clone limpio del repo (sin rutas absolutas).
- Documentar procedimiento en runbook operativo.
- Sign-off arquitectura + producto.

**Criterios de aceptación:**

- [ ] Nuevo tenant operativo sin reiniciar CP ni otros silos.
- [ ] Suspendido: sin login/dashboard/API operativa.
- [ ] Restaurar desde suspendido: acceso inmediato.
- [ ] Compatible ADR-001 y fleet local documentado.

---

## 6. Matriz de impacto

| Componente | Impacto | Riesgo | Acción |
|------------|---------|--------|--------|
| `LocalFleetInstanceProvisioner` | Alto | Medio | Refactor: separar provision vs start |
| `LocalFleetRegistry` | Medio | Bajo | Añadir campos runtime opcionales |
| `scripts/local-instances/serve.mjs` | Alto | Alto | Evolucionar a supervisor daemon |
| `scripts/local-instances/lib.mjs` | Medio | Medio | Exportar spawn reutilizable |
| `TenantAdminService` | Medio | Bajo | Deprecar activate directo en UI; usar Restore |
| `CompanyController` | Medio | Bajo | Rutas lifecycle |
| `LocalFleetTenantMirror` | Medio | Medio | Mirror obligatorio en transiciones |
| `AuthenticateOperatorUseCase` | Bajo | Bajo | Validación status opcional (defensa profunda) |
| `InstancePortalAccessGuard` | Medio | Medio | O nuevo middleware previo |
| `routes/web.php` (silo) | Medio | Medio | Grupo middleware suspensión |
| `MiddlewareApiRoutes` | Medio | Bajo | 403 tenant_suspended |
| `Companies/Show.vue` | Alto | Bajo | UX tres acciones |
| `SimulationTenantEligibilityChecker` | Bajo | Bajo | Ya valida status; alinear mensajes |
| `docker-compose.yml` | Bajo | Bajo | Sin cambio v1.5 local |
| K8s manifests | Medio | Medio | Documentar equivalente Levantar/Suspender |
| `config/platform.php` | Bajo | Bajo | Flags lifecycle |
| Tests CI | Medio | Medio | Ampliar suite |
| Documentación | Alto | Bajo | README, deploy, runbooks |

---

## 7. Matriz de riesgos

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Proceso duplicado mismo puerto al Levantar | Media | Alto | Lock file por puerto; `isRunning` check; idempotencia |
| Spawn falla en Windows (permisos/firewall) | Media | Alto | Tests en Win + Linux CI; fallback doc manual |
| Suspend no propagado al silo sin mirror | Media | Alto | Mirror síncrono obligatorio en use case |
| Confusión status vs lifecycle | Alta | Medio | ADR + UI labels claros; migración datos |
| Regresión fleet-bootstrap | Media | Medio | Tests regression existentes + nuevos |
| Usuario suspendido con sesión activa | Media | Medio | Middleware invalida sesión en cada request |
| Producción sin equivalente supervisor | Alta | Alto | Documentar K8s/CI; no mezclar con hack local |
| Race: Levantar durante provision | Baja | Medio | Transacción + lock tenant row |
| Rutas absolutas en scripts worker | Media | Bajo | Usar `base_path()` / paths relativos repo |
| Breaking change API activate/suspend | Baja | Medio | Mantener rutas legacy deprecadas una versión |

---

## 8. Estrategia «sin reinicio» — detalle técnico

### 8.1 Desarrollo local (evidencia + propuesta)

**Hoy:**

```text
npm run instances:serve
  → loadManifest()  # fleet-registry.json frozen
  → foreach instance: spawn php artisan serve --env=id --port=N
```

**v1.5 propuesto:**

```text
npm run instances:supervise   # nuevo comando
  → Supervisor daemon (Node)
  → Watch fleet-registry.json (opcional)
  → Map<instanceId, ChildProcess>
  → API interna o artisan platform:fleet:start-silo --slug=x

Levantar servicio (desde CP UI)
  → TenantLifecycleOrchestrator
  → supervisor.ensureRunning(instanceId, port)
  → NO reinicia otros children
```

Alternativa mínima (menor alcance): comando Artisan `platform:fleet:start-silo {slug}` invocable desde CP que ejecuta spawn detached — **sin** reescribir `serve.mjs` completo en Fase 3.

### 8.2 Producción

Reinicio de pod/deployment **no** es «reiniciar la plataforma principal»: es escalar instancia cliente independiente (ADR-001). Levantar = deploy/scale; Suspender = scale 0 + flag BD + página mantenimiento en ingress.

### 8.3 Configuración dinámica

**No** se propone cargar múltiples tenants en un solo proceso Laravel (contradice ADR-001). La «carga dinámica» es **dinámica de procesos/silos**, no dinámica de tenant_id por request.

---

## 9. Rutas API propuestas (diseño)

| Método | Ruta | Ability | Descripción |
|--------|------|---------|-------------|
| POST | `/control/companies/{tenant}/lifecycle/start` | `tenants:manage` | Levantar servicio |
| POST | `/control/companies/{tenant}/lifecycle/suspend` | `tenants:manage` | Suspender servicio |
| POST | `/control/companies/{tenant}/lifecycle/restore` | `tenants:manage` | Restaurar servicio |
| GET | `/control/companies/{tenant}/lifecycle/status` | `control:read` | Estado lifecycle + health |

Rutas legacy `suspend` / `activate` — deprecar en favor de lifecycle/suspend y lifecycle/restore; mantener alias una versión.

---

## 10. Checklist de validación manual (post-implementación)

```text
[ ] Clonar repo en máquina limpia
[ ] composer install && npm install && npm run instances:bootstrap
[ ] npm run instances:supervise (o serve legacy + nuevo start)
[ ] Crear empresa en /control/provisioning
[ ] Verificar lifecycle=provisioned, URL no responde aún
[ ] Clic «Levantar servicio» — URL responde /health/ready
[ ] Login operador en silo — dashboard OK
[ ] Desde CP «Suspender» — silo muestra página suspensión (no login)
[ ] API publish en silo — 403
[ ] «Restaurar» — acceso inmediato
[ ] Otros silos no se cayeron durante todo el flujo
```

---

# POR CORREGIR EN VERSIONES FUTURAS

## Hallazgo 1 — Suspender no bloquea acceso al silo

**Descripción.** `TenantAdminService::suspend` actualiza solo la BD del control plane. El operador puede seguir accediendo al silo en `:8001` si el proceso sigue activo.

**Impacto.** Suspensión comercial no tiene efecto operativo; incumple expectativa de negocio v1.5.

**Posible causa raíz.** Ausencia de middleware de estado operativo en rutas del silo; diseño inicial centrado en registro SaaS, no en enforcement.

**Recomendación.** Implementar en v1.5 según §3.2; no parchear solo en CP.

---

## Hallazgo 2 — Reinicio obligatorio tras provisioning local

**Descripción.** Nuevo cliente en `fleet-registry.json` no recibe proceso HTTP hasta reiniciar `instances:serve`.

**Impacto.** Fricción operativa; percepción de sistema inestable en demo.

**Posible causa raíz.** `serve.mjs` diseñado como batch estático, no supervisor.

**Recomendación.** v1.5 supervisor de procesos (§8.1).

---

## Hallazgo 3 — Duplicidad semántica de estados

**Descripción.** Coexisten `tenants.status`, `settings.deployment.status`, y labels UI sin máquina de estados unificada.

**Impacto.** Confusión operativa; bugs en transiciones.

**Posible causa raíz.** Evolución incremental Fase D fleet sin ADR lifecycle.

**Recomendación.** ADR-010 unificando vocabulario (§2).

---

## Hallazgo 4 — Tenant creado con status=active antes de estar operativo

**Descripción.** `TenantAdminService::create` asigna `status=active` inmediatamente aunque el silo no esté levantado.

**Impacto.** UI muestra «Activo» cuando el servicio no responde; `SimulationTenantEligibilityChecker` permite simulación sobre tenant no operativo.

**Posible causa raíz.** `status` modelado como estado comercial, no operativo.

**Recomendación.** Separar `commercial_status` vs `operational_status` o introducir `lifecycle` (§2.2).

---

## Hallazgo 5 — Mirror no automático tras suspend/activate

**Descripción.** Suspender/activar en CP no invoca `LocalFleetTenantMirror` automáticamente.

**Impacto.** BD silo desincronizada respecto a CP.

**Posible causa raíz.** Acciones admin pensadas solo para vista CP.

**Recomendación.** Mirror síncrono en cada transición lifecycle v1.5.

---

## Hallazgo 6 — Rutas absolutas en logs de simulación worker

**Descripción.** Logs en `storage/logs/simulation-worker-*.log` referencian rutas como `D:\omnichannel-ddd-eda\` y `C:\xampp\php\php.exe`.

**Impacto.** Portabilidad reducida entre equipos; no bloquea v1.5 pero contradice requisito clone-friendly.

**Posible causa raíz.** Hardcode en launcher de workers Windows.

**Recomendación.** v1.6 — usar `PHP_BINARY` configurable y paths relativos (`SimulationWorkerLauncher`).

---

## Hallazgo 7 — Botón «Activar» vs «Restaurar servicio»

**Descripción.** UI actual usa «Activar» genérico para cualquier `status !== active`, sin distinguir provisionado vs suspendido.

**Impacto.** Operador puede activar flag BD sin levantar proceso.

**Posible causa raíz.** UI mínima pre-v1.5.

**Recomendación.** Reemplazar por acciones contextuales §3.3.

---

## Hallazgo 8 — Soft delete tenants en control plane

**Descripción.** `TenantModel` usa `SoftDeletes`; fleet-bootstrap tuvo conflictos con tenants soft-deleted (unique slug).

**Impacto.** Re-provision puede fallar; estados lifecycle ambiguos.

**Posible causa raíz.** `InstanceTenantSeeder` en CP borraba tenants (corregido parcialmente para control_plane).

**Recomendación.** v1.6 — política explícita archive vs delete; `withTrashed()` en imports.

---

## Hallazgo 9 — Pasarela de pago pendiente

**Descripción.** Commit inicial menciona «falta pasarela de pago».

**Impacto.** Fuera de alcance lifecycle; no bloquea v1.5.

**Recomendación.** Backlog comercial separado.

---

## Referencias internas

| Documento | Relevancia |
|-----------|------------|
| `docs/production/ADR_001_instancia_por_cliente.md` | Modelo despliegue |
| `deploy/local-instances/README.md` | Fleet local |
| `docs/production/Guia_Despliegue_Instancia_Cliente.md` | Flujo producción |
| `app/Shared/Platform/LocalFleet/*` | Provisioning actual |
| `scripts/local-instances/serve.mjs` | Causa reinicio |
| `docs/Patente/Ficha_Tecnica_Software_INDECOPI.md` | Contexto arquitectura |
| `docs/Patente/EjemplarSoftware.md` | Manual operación |

---

## Conclusión

La versión **1.5** debe formalizar un **Tenant Lifecycle Management** coherente con **instancia por cliente (ADR-001)**: provisioning crea artefactos; **Levantar servicio** inicia el proceso silo sin reinicio global; **Suspender** aplica enforcement real en el portal; **Restaurar** revierte suspensión con precondiciones estrictas.

La implementación se concentra en el bounded context **Control** (orquestación) y **Shared/Platform** (supervisor local), con middleware transversal en **Http**, preservando intactos Middleware, Dashboard e Integration.

Este runbook está listo para **revisión arquitectónica** previa a cualquier desarrollo. No incluye código; todas las afirmaciones sobre el estado actual están respaldadas por el código fuente analizado al 2026-05-30.

---

*Documento generado sin modificación de código fuente. Incertidumbres marcadas explícitamente donde el comportamiento en producción cloud depende de infraestructura externa no versionada en el repositorio.*
