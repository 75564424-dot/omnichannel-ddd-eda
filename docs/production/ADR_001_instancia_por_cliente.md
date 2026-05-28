# ADR-001: Modelo de aislamiento — Instancia por cliente

**Estado:** Aceptado  
**Fecha:** 2026-05-21  
**Decisores:** Arquitectura de plataforma  
**Plan relacionado:** [Plan_Tenants.md](Plan_Tenants.md)

---

## Contexto

El middleware omnicanal (`platform/event-bus-core`) debe servir a múltiples clientes comerciales. Existen dos modelos posibles:

1. **Instancia por cliente** — silo desplegable (app + BD + config dedicados)
2. **Multi-tenant lógico** — una app compartida con `tenant_id` / RLS

El esquema de BD incluye tabla `tenants` y columnas `tenant_id` preparadas para evolución futura, pero el runtime históricamente no las utilizaba.

---

## Decisión

Adoptamos **instancia por cliente** como modelo operativo de producción (Fase D).

Cada cliente comercial recibe:

- Proceso de aplicación dedicado (VM, contenedor o namespace K8s)
- Base de datos dedicada (mismas migraciones)
- Archivos de configuración propios (`eventbus.php`, `modules_config.json`, `.env`)
- URL y secretos propios

La fila en tabla `tenants` representa **metadatos de la instancia** (trazabilidad, logs, correlación externa), **no** partición multi-tenant dentro de la misma app.

---

## Consecuencias

### Positivas

- Aislamiento fuerte (físico), auditable
- Alineado con DDD: bounded contexts externos ya tienen BD propia
- Compatible con B.2/C actuales (config merge al arranque del proceso)
- Menor complejidad operativa inicial

### Negativas

- Coste de N instancias vs una app multi-tenant
- Fleet management requerido (inventario, onboarding)
- `tenant_id` en BD no implica ACL runtime hasta Fase 3

---

## Alternativas rechazadas

| Alternativa | Motivo de rechazo |
|-------------|-------------------|
| Multi-tenant en una app (ahora) | Refactor amplio: tenant resolution, catálogos por request, riesgo de fuga |
| Sin tabla `tenants` | Pérdida de trazabilidad unificada en logs y métricas |
| Schema-per-tenant en una BD | Complejidad migraciones; pospuesto |

---

## Implementación (Plan_Tenants)

| Fase | Alcance | Estado |
|------|---------|--------|
| 1 | ADR, `.env.example`, templates, runbook onboarding | Implementado |
| 2 | Seed tenant = instancia, `tenant_id` en persistencia y logs | Implementado |
| 3 | Tenant resolver, RLS, portal admin | **Diferido** — ver § Fase 3 |

---

## Fase 3 — Diferida explícitamente

Multi-tenant lógico **no se implementa** hasta que el negocio cumpla criterios:

- Más de ~20 clientes pequeños con mismo SLA
- Coste operativo de N instancias insostenible
- Requisito contractual de SaaS multi-cliente en una URL

Cuando se active, requerirá ADR-002 y refactor de: tenant resolution middleware, scope Eloquent global, namespacing `event_type`, RLS o schema-per-tenant.

---

## Referencias

- `docs/personal_notes/Fase_D_arquitectura_cliente.md`
- `docs/architecture/middleware_database_architecture.md` §16
- [Runbook_Onboarding_Cliente.md](Runbook_Onboarding_Cliente.md)
