# Middleware — Event Store y Orquestación

Implementación según `Plan_Middleware.md`.

## Pipeline de ingesta

```
ValidateEnvelope
  → EventStore.append()      (event_store — fuente canónica)
  → EventLogProjector        (event_logs — status received)
  → MessageQueue.enqueue()   (message_queue)
  → Outbox (opcional) | ProcessEventJob | sync dispatch
  → EventBusPort             (Laravel Event | Kafka stub)
  → WorkflowEngine (opcional)
  → Consumers / proyecciones Dashboard
```

## Correlación

| Origen | Campo |
|--------|-------|
| Body | `correlation_id`, `causation_id` |
| Headers | `X-Correlation-Id`, `X-Causation-Id` |

Se persisten en `event_store`, `event_logs` y `message_queue`.

## Schema registry (Fase 2)

Definición por `event_type` en `config/eventbus.php` → `schema_registry`:

```php
'Platform.Smoke.Probe' => [
    'path'           => config_path('schemas/platform_smoke_probe.json'),
    'event_version'  => 1,
    'schema_version' => '2026-05-01',
],
```

Alternativa: fila `system_configurations` con clave `event_schema.{EventType}`.

Validación activa con `EVENTBUS_SCHEMA_VALIDATION=true`.

## EventBusPort (Fase 3)

| Driver | Env | Comportamiento |
|--------|-----|----------------|
| `laravel` | `EVENTBUS_DRIVER=laravel` (default) | `Event::dispatch` |
| `kafka` | `EVENTBUS_DRIVER=kafka` | Stub log — broker real en plan futuro |

## Outbox

| Variable | Default |
|----------|---------|
| `EVENTBUS_OUTBOX_ENABLED` | `false` |

Cuando está activo: fila en `outbox_messages` + `RelayOutboxJob` (cola `middleware`).

## Workflows mínimos

| Variable | Default |
|----------|---------|
| `EVENTBUS_WORKFLOWS_ENABLED` | `false` |

Con workflows `active` en BD cuyo `trigger_event_type` coincide, se crea `processing_jobs` tipo `workflow_trigger`.

Orquestación completa (Temporal/Camunda): ver `ADR_007_workflow_orchestration.md`.

## Referencias

- [Plan_Middleware.md](Plan_Middleware.md)
- [Resiliencia.md](Resiliencia.md)
- `docs/architecture/middleware_database_architecture.md`
