# Plan Middleware — Event Store y Orquestación

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Alto

---

## 1. Estado Actual

### Qué existe (runtime)

- `EventPublisherService` — valida, persiste cola, dispatch Laravel Event
- `BusTrackingListener` — escribe `message_queue`
- `SubscriptionRegistryService` — resuelve consumidores desde config
- Wildcard listeners: tracking, feed, metrics, module observation
- `SyncConfiguredModulesToRegistryUseCase` — B.2 implementado
- `PackSubscriptionCatalogMerger` — C implementado
- Tablas: `message_queue`, `dead_letter_queue`, `registered_modules`

### Qué existe (esquema sin código)

- `event_store` — append-only canónico
- `event_logs`, `workflows`, `workflow_steps`, `transactions`, `processing_jobs`

### Qué falta (documentado pero no implementado)

- Broker externo Kafka/RabbitMQ
- Event sourcing replay desde `event_store`
- Versionado de schema de eventos (`event_version`, `schema_version`)
- Orquestación multi-paso (workflows)
- Outbox pattern

### Riesgos

| Riesgo | Severidad |
|--------|-----------|
| `event_store` vacío — no hay fuente canónica | **Alto** |
| Bus in-process no escala horizontalmente | **Alto** |
| Workflows en BD sin motor | **Medio** |

---

## 2. Objetivo

Completar el **motor middleware omnicanal** según docs EDA:

1. Ingesta → validación → **event_store** → cola → enrutamiento → consumo
2. Correlación y causation entre eventos
3. Preparación para broker externo sin romper DDD

---

## 3. Problemas Detectados

1. Publish path salta `event_store`
2. Dos proyecciones duplican payload (message_queue + event_feed_projections)
3. `correlation_id` column exists but never set
4. No hay registry de schema por `event_type`
5. Documentación Flujo_Middleware 5 etapas vs implementación 2 etapas

---

## 4. Requerimientos

- [ ] `EventStoreRepository` — append on publish
- [ ] Propagar `correlation_id`, `causation_id` desde HTTP header/body
- [ ] Schema registry (JSON files o tabla `system_configurations`)
- [ ] `EventLogProjector` → `event_logs`
- [ ] Workflow engine mínimo (fase 3) o integración Temporal/Camunda
- [ ] Abstracción `EventBusPort` — Laravel Event vs Kafka driver
- [ ] Outbox table + relay job (fase 3)

---

## 5. Propuesta Técnica

### Pipeline objetivo

```
[Adapter/Ingest]
    → ValidateEnvelope
    → EventStore.append()
    → MessageQueue.enqueue()
    → EventBus.publish()  ← Laravel Event (hoy) / Kafka (mañana)
    → Consumers
    → Projections (feed, metrics, event_logs)
```

### DDD alignment

- **Middleware BC** owns write path
- **Dashboard BC** owns read projections only
- Domain events internos ≠ eventos de integración externa — mantener envelope contract

### Versionado

```json
{
  "event_type": "RetailCo.Order.Created",
  "event_version": 2,
  "schema_version": "2026-05-01"
}
```

---

## 6. Roadmap

### Fase 1: Wire event_store + correlation_id + event_logs
### Fase 2: Schema validation per event_type
### Fase 3: Outbox + Kafka adapter + workflow engine

---

## 7. Prioridad

**Alto**

---

## 8. Riesgo si no se implementa

Gap entre documentación enterprise y producto real; imposible replay/audit; escalabilidad limitada al single process.

---

## Referencias

- `docs/Plan_Desarrollo_Servicio_v0.1/Flujo_Middleware.md`
- `docs/architecture/middleware_database_architecture.md`
- [Plan_Integraciones.md](Plan_Integraciones.md)
