# ADR-010: Tenant Lifecycle Management — Vocabulario y modelo de estados operativos

**Estado:** Propuesto (pendiente aprobación antes de Fase 3)  
**Fecha:** 2026-05-30  
**Decisores:** Arquitectura de plataforma  
**Runbook relacionado:** `docs/Plan_Desarrollo_Serviciov1.5/Runbook_v1.5_Gestion_Ciclo_Vida_Tenants.md`  
**ADR relacionado:** [ADR-001](ADR_001_instancia_por_cliente.md)  
**Plan relacionado:** [Plan_Tenants.md](Plan_Tenants.md)

---

## Contexto

El sistema actual maneja el estado de un tenant mediante dos campos independientes y sin semántica coordinada:

1. **`tenants.status`** (`active` | `suspended`) — estado comercial; afecta únicamente la BD del control plane.
2. **`settings.deployment.status`** (`active_on_instance` | `pending_dedicated_instance`) — estado de provisioning de artefactos; escrito por `LocalFleetInstanceProvisioner`.

Esta separación genera tres problemas documentados en el Runbook v1.5:

- **Hallazgo 1:** Suspender no bloquea el acceso al silo (no hay enforcement en el proceso cliente).
- **Hallazgo 3:** Duplicidad semántica de estados sin máquina de estados unificada.
- **Hallazgo 4:** Un tenant creado aparece `status=active` antes de tener servicio HTTP operativo.

Adicionalmente, no existe concepto explícito de **"proceso silo activo"** separado de **"tenant comercialmente activo"**, lo que impide implementar las tres acciones requeridas por v1.5: Levantar, Suspender y Restaurar servicio.

---

## Decisión

Adoptamos un **vocabulario de dos dimensiones ortogonales** para el estado del tenant, sin eliminar los campos existentes:

### Dimensión 1: Estado Comercial (`tenants.status`)

| Valor | Semántica | Quién lo establece |
|-------|-----------|-------------------|
| `active` | Tenant comercialmente habilitado para operar | `TenantAdminService` |
| `suspended` | Tenant comercialmente suspendido; acceso bloqueado | `TenantAdminService` (via use case) |

**Sin cambio de valores.** Se mantiene compatibilidad total con datos existentes.

### Dimensión 2: Estado de Infraestructura (`settings.deployment.lifecycle`)

Campo **nuevo** a añadir dentro del JSON `settings.deployment`, sin eliminar campos existentes:

| Valor | Semántica | Cuándo se establece |
|-------|-----------|---------------------|
| `provisioned` | Artefactos creados (BD, `.env`, registry); **sin proceso HTTP activo** | Al finalizar `LocalFleetInstanceProvisioner::provision` |
| `running` | Proceso HTTP del silo activo y respondiendo | Al completar `StartTenantServiceUseCase` con health check OK |
| `stopped` | Proceso HTTP detenido explícitamente (ej.: suspensión con `PLATFORM_LOCAL_FLEET_STOP_ON_SUSPEND=true`) | Al completar `SuspendTenantServiceUseCase` con stop |

**Compatibilidad:** los campos `status` y `deployment.status` existentes no se eliminan. Solo se añade `deployment.lifecycle`.

### Matriz de estados combinada

| `lifecycle` | `status` | Significado operativo | Acciones disponibles en UI |
|-------------|----------|----------------------|---------------------------|
| `provisioned` | `active` | Provisionado, sin servicio HTTP | **Levantar servicio** |
| `running` | `active` | Silo operativo | **Suspender servicio** |
| `running` | `suspended` | Silo corriendo pero acceso bloqueado | **Restaurar servicio** |
| `stopped` | `suspended` | Silo detenido y acceso bloqueado | **Restaurar servicio** (+ Levantar implícito) |
| `stopped` | `active` | Estado transitorio; no debería persistir | Ninguna (estado inválido) |

### Regla de precedencia para bloqueo de acceso

**El `tenants.status` es autoritativo para el bloqueo de acceso al silo.** El `lifecycle` determina si el proceso existe; el `status` determina si el acceso está permitido.

---

## Implementación en `settings.deployment`

El JSON almacenado en la columna `settings` del control plane evolucionará de:

```json
{
  "deployment": {
    "mode": "instance_per_client",
    "status": "active_on_instance",
    "local_instance": { "app_url": "...", "port": 8001, "env_file": "...", "env_id": "...", "db_path": "..." },
    "provisioned_at": "2026-05-30T..."
  }
}
```

A:

```json
{
  "deployment": {
    "mode": "instance_per_client",
    "status": "active_on_instance",
    "lifecycle": "running",
    "local_instance": { "app_url": "...", "port": 8001, "env_file": "...", "env_id": "...", "db_path": "..." },
    "provisioned_at": "2026-05-30T...",
    "lifecycle_updated_at": "2026-05-30T..."
  }
}
```

**Nota:** `status` pre-existente NO se elimina. `lifecycle` es el campo nuevo autoritativo para estado de infraestructura.

---

## Reglas de transición (Policy Domain)

Las siguientes transiciones son las ÚNICAS permitidas por `TenantLifecyclePolicy`:

```
provisioned  ──[Start]──▶  running      (requiere: status=active)
running      ──[Suspend]─▶  running|stopped (cambia status=suspended; lifecycle=stopped si flag activo)
running|stopped ──[Restore]─▶ running   (requiere: status=suspended → status=active; lifecycle=running)
```

**Transiciones inválidas** que deben rechazarse con excepción de dominio:
- Start sobre `lifecycle=running` (idempotente: no error, no-op)
- Suspend sobre `status=suspended`
- Restore sobre `status=active`

---

## Eventos de dominio

| Evento | Payload mínimo | Publicado por |
|--------|---------------|---------------|
| `Tenant.Lifecycle.Started` | `tenant_id`, `lifecycle`, `app_url`, `timestamp` | `StartTenantServiceUseCase` |
| `Tenant.Lifecycle.Suspended` | `tenant_id`, `status`, `lifecycle`, `timestamp` | `SuspendTenantServiceUseCase` |
| `Tenant.Lifecycle.Restored` | `tenant_id`, `status`, `lifecycle`, `timestamp` | `RestoreTenantServiceUseCase` |

Publicación vía eventos internos de Laravel (no requiere Kafka en v1.5). Los listeners son opcionales en v1.5; el bus interno es suficiente para auditoría.

---

## Feature flag

```ini
PLATFORM_TENANT_LIFECYCLE_V15=true
PLATFORM_LOCAL_FLEET_STOP_ON_SUSPEND=false
```

| Flag | Descripción | Default |
|------|-------------|---------|
| `PLATFORM_TENANT_LIFECYCLE_V15` | Habilita rutas `/lifecycle/*` y middleware de suspensión | `false` (opt-in) |
| `PLATFORM_LOCAL_FLEET_STOP_ON_SUSPEND` | Detiene proceso OS al suspender (solo dev local) | `false` |

---

## Mapeo desde datos pre-v1.5

Para tenants existentes que no tienen el campo `lifecycle`, la lógica de lectura debe inferir:

| Situación en BD | `lifecycle` inferido |
|-----------------|---------------------|
| `deployment.status = active_on_instance` AND `status = active` | `running` |
| `deployment.status = active_on_instance` AND `status = suspended` | `running` (proceso puede estar activo) |
| `deployment.status = pending_dedicated_instance` | `provisioned` |
| `deployment` ausente | `provisioned` |

---

## Consecuencias

### Positivas

- Semántica clara: `status` = acceso; `lifecycle` = proceso
- Compatible retroactivamente con todos los tenants existentes
- Permite implementar `EnsureTenantOperationalStatus` middleware con semántica correcta
- Alineado con ADR-001: lifecycle es por silo, no multi-tenant

### Negativas

- Introduce un segundo campo de estado que requiere sincronización explícita en cada transición
- El mirror CP→silo debe propagarse en toda transición (ya era deuda documentada en Hallazgo 5)

---

## Alternativas rechazadas

| Alternativa | Motivo de rechazo |
|-------------|-------------------|
| Tabla nueva `tenant_lifecycle_events` | Complejidad excesiva para v1.5; diferida a v2.0 si se requiere historial |
| Renombrar `tenants.status` a `commercial_status` | Breaking change en BD y mirror; incompatibilidad retroactiva |
| Un solo campo unificado | No permite representar `running+suspended` (silo activo, acceso bloqueado) |
| Usar solo `settings.deployment.status` existente | Campo no autoritativo; no propaga al silo vía mirror de forma confiable |

---

## Referencias

- `docs/Plan_Desarrollo_Serviciov1.5/Runbook_v1.5_Gestion_Ciclo_Vida_Tenants.md` §2
- `docs/production/ADR_001_instancia_por_cliente.md`
- `app/Shared/Infrastructure/Models/TenantModel.php`
- `app/Control/Application/Services/Tenants/TenantAdminService.php`
- `app/Shared/Platform/LocalFleet/LocalFleetInstanceProvisioner.php`
