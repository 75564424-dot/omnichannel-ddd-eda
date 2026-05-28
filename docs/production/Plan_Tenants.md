# Plan de Tenants y Modelo Multi-Cliente

**Versión:** 1.1 | **Fecha:** 2026-05-21 | **Prioridad global:** Alto | **Estado implementación:** Fase 1–2 completadas (2026-05-21)

---

## 1. Estado Actual

### Qué existe

- Tabla `tenants` en migración `2026_05_21_100000`
- Columna `tenant_id` nullable en tablas operativas
- Repositorios insertan `tenant_id = null` siempre
- **Decisión documentada:** instancia por cliente (Fase D) — `docs/personal_notes/Fase_D_arquitectura_cliente.md`
- Blueprint: `Fase_D_blueprint_instancia_por_cliente.md`

### Qué está incompleto

- Sin resolución de tenant en runtime
- Sin CRUD de tenants
- Sin aislamiento lógico en queries
- Sin seed de tenant por instancia
- Esquema preparado para multi-tenant futuro pero modelo operativo es silo

### Riesgos detectados

| Riesgo | Severidad |
|--------|-----------|
| Confusión producto: vender multi-tenant sin implementación | **Alto** |
| `tenant_id` siempre null — falsa sensación de aislamiento | **Medio** |
| Fleet de N instancias sin inventario | **Medio** |

---

## 2. Objetivo

Formalizar el **modelo de aislamiento de clientes** para el middleware omnicanal:

- **Corto plazo (recomendado):** instancia dedicada por cliente comercial
- **Largo plazo (opcional):** multi-tenant lógico con `tenant_id` + RLS

---

## 3. Problemas Detectados

1. Documentación de BD describe multi-tenant; código no lo usa
2. Sin ADR formal en `docs/production/` (solo personal_notes)
3. Sin plantilla de despliegue por cliente (env, config, dominio)
4. `tenants` table orphan — no hay relación en aplicación

---

## 4. Requerimientos

### Instancia por cliente (Fase D — actual)

- [x] ADR en `docs/production/ADR_001_instancia_por_cliente.md`
- [x] `.env.example` con variables por instancia (`PLATFORM_CLIENT_SLUG`, `APP_URL`)
- [x] Inventario de instancias (spreadsheet o CMDB mínimo)
- [x] Runbook de onboarding nuevo cliente
- [x] Seed opcional: un tenant row = instancia misma

### Multi-tenant futuro (si se requiere)

- [ ] Tenant resolver middleware — **Fase 3 diferida**
- [ ] Scope global Eloquent `tenant_id`
- [ ] Namespacing `event_type` por tenant
- [ ] RLS PostgreSQL o schema-per-tenant

---

## 5. Propuesta Técnica

### Modelo recomendado (Fase 1 producción)

```
Cliente A → Instancia A (VM/K8s namespace) → BD A → config A
Cliente B → Instancia B → BD B → config B
```

Ventajas: aislamiento fuerte, alineado con DDD (BC externos ya tienen BD propia), menor complejidad.

### Cuando considerar multi-tenant

- >20 clientes pequeños con mismo SLA
- Coste operativo de N instancias insostenible
- Requisito explícito de SaaS multi-cliente

---

## 6. Roadmap de Implementación

### Fase 1

- ADR instancia por cliente
- Template env + config por cliente
- Documentar en propuestas comerciales

### Fase 2

- Seed tenant = instance metadata
- `tenant_id` en logs/audit para trazabilidad externa

### Fase 3 (solo si negocio lo exige)

- Tenant resolver + RLS
- Portal admin multi-tenant

---

## 7. Prioridad

**Alto** — Decisión arquitectónica que afecta ventas y ops.

---

## 8. Riesgo si no se implementa

Vender capacidades multi-tenant inexistentes; costes operativos impredecibles; mezcla de datos si se intenta multi-tenant sin diseño.

---

## Referencias

- `docs/personal_notes/Fase_D_arquitectura_cliente.md`
- `docs/architecture/middleware_database_architecture.md` §16
