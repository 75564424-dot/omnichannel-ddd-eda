# Plan Base de Datos

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Medio

---

## 1. Estado Actual

### Qué existe

- **26 tablas** middleware (post migración 2026-05-21)
- Migración legacy → new → drop (cadena completa)
- Modelos Eloquent alineados a tablas activas
- Documentación: `middleware_database_architecture.md`, `middleware_database_dictionary.md`
- SQLite dev / MySQL prod compatible

### Qué está incompleto

- ~60% del esquema sin capa de aplicación
- `SystemMetricsSnapshotModel` posible código muerto (tabla eliminada)
- `tenant_id` siempre null
- Sin seeders de datos demo para integrations/channels
- Sin política de retención automatizada (solo config comment)

### Riesgos

| Riesgo | Severidad |
|--------|-----------|
| migrate en upgrade largo (create legacy → migrate → drop) | **Medio** |
| Crecimiento message_queue/event_store sin purge | **Alto** |
| Índices no validados bajo carga | **Medio** |

---

## 2. Objetivo

BD **estable, escalable y coherente** con el middleware: esquema alineado con código, retención, índices optimizados, estrategia multi-tenant clara.

---

## 3. Problemas Detectados

1. Schema ahead of code — confusión para desarrolladores
2. Migraciones May 2026 crean tablas que se eliminan — ruido en fresh install
3. Sin particionamiento temporal (event_store, audit_logs)
4. Sin RLS para multi-tenant futuro
5. FKs mínimas — integridad por convención UUID

---

## 4. Requerimientos

- [ ] Seeders: default channel `middleware`, system_configurations retention keys
- [ ] Artisan `platform:purge-retention` — message_queue, event_logs, observability_metrics
- [ ] Consolidar migraciones greenfield (fase 3 — squash)
- [ ] Eliminar `SystemMetricsSnapshotModel` si no usado
- [ ] Índices review bajo explain analyze (MySQL)
- [ ] Backup strategy documentada
- [ ] ADR: cuándo activar tenant_id

---

## 5. Propuesta Técnica

### Retención sugerida (system_configurations)

| Tabla | Días |
|-------|------|
| message_queue | 30 |
| event_logs | 30 |
| observability_metrics (granular) | 14 |
| event_store | 90–365 |
| audit_logs | 2555 (7 años) |

### Escalabilidad futura

- Particionamiento `event_store` por `occurred_at` (mensual)
- Read replica para Dashboard queries
- Connection pooling (PgBouncer / ProxySQL)

---

## 6. Roadmap

### Fase 1: Retention job + seeders + cleanup dead models
### Fase 2: Index optimization + backup runbook
### Fase 3: Migration squash + partitioning POC

---

## 7. Prioridad

**Medio** (urgente retención = Alto para prod con volumen)

---

## 8. Riesgo si no se implementa

BD crece sin control; costes cloud; degradación de queries; restore imposible sin backups.

---

## Referencias

- `docs/architecture/middleware_database_dictionary.md`
- `database/migrations/2026_05_21_*`
