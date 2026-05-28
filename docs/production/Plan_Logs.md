# Plan de Logs y Auditoría

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Alto

---

## 1. Estado Actual

### Qué existe

- Laravel Log facade en: `EventPublisherService`, `BusTrackingListener`, `EventFeedProjector`, `PackSubscriptionCatalogMerger`, `UniversalDashboardFeedListener`
- Formato: texto plano en `storage/logs/laravel.log`
- Tabla `audit_logs` (append-only design)
- Tabla `event_logs` (operacional) — sin escritura
- Mensajes clave documentados en observabilidad guide

### Qué falta

- JSON structured logging
- Log aggregation (ELK, CloudWatch, Loki)
- Retention policy automatizada
- Población de `audit_logs` y `event_logs`
- Log levels por entorno configurados

---

## 2. Objetivo

Trazabilidad **completa y searchable** de operaciones del middleware: quién hizo qué, cuándo, con qué payload hash, correlacionado con eventos de dominio.

---

## 3. Problemas Detectados

1. Logs locales no escalan en cloud multi-pod
2. Sin `correlation_id` en log context
3. Payloads completos en logs — riesgo PII (solo loguear hash/metadata)
4. `audit_logs` diseñada pero vacía

---

## 4. Requerimientos

- [ ] Logging JSON (`LOG_CHANNEL=stack`, formatter Monolog JSON)
- [ ] Context fields: `correlation_id`, `event_uuid`, `tenant_id`, `actor_id`
- [ ] Writer `AuditLogService` → tabla `audit_logs`
- [ ] Writer `EventLogService` → tabla `event_logs` (summary + hash)
- [ ] Retention job: purge `event_logs` > 30 días
- [ ] Documentar qué NO loguear (credenciales, PII)

---

## 5. Propuesta Técnica

### Event log vs audit log

| Tabla | Contenido |
|-------|-----------|
| `event_logs` | Cada evento procesado — operacional |
| `audit_logs` | Acciones humanas/admin — compliance |
| `laravel.log` | Debug técnico, errores, warnings |

### Ejemplo log estructurado

```json
{
  "level": "info",
  "message": "Event published",
  "correlation_id": "uuid",
  "event_uuid": "uuid",
  "event_type": "RetailCo.Order.Created",
  "origin": "POS"
}
```

---

## 6. Roadmap

### Fase 1: JSON logs + correlation in context
### Fase 2: audit_logs + event_logs services
### Fase 3: ELK/CloudWatch integration + retention automation

---

## 7. Prioridad

**Alto**

---

## 8. Riesgo si no se implementa

Imposible forensics post-incidente; incumplimiento auditoría; debugging lento en producción.

---

## Referencias

- [Plan_Observabilidad.md](Plan_Observabilidad.md)
- [Plan_Usuarios.md](Plan_Usuarios.md)
