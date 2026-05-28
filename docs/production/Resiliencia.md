# Resiliencia del bus omnicanal

Implementación según `Plan_Resiliencia.md`.

## Ciclo de resiliencia

```
Publish → message_queue (pending)
       → ProcessEventJob (sync o async)
       → retries table (cada intento)
       → completed | dead_letter_queue
       → operador: resolve | requeue
```

## Idempotencia publish

Duplicado de `event_id` → **HTTP 200** con `status: already_processed` (no 500).

## Comandos y jobs

| Componente | Rol |
|------------|-----|
| `ProcessEventJob` | Procesamiento async con retry/backoff Laravel |
| `EventProcessingService` | Dispatch + registro retries + DLQ |
| `platform:purge-retention` | Purge cola antigua (Plan_BaseDeDatos) |

## API DLQ

| Método | Ruta | Acción |
|--------|------|--------|
| PATCH | `/api/middleware/dead-letters/{id}/resolve` | Descartar manualmente |
| POST | `/api/middleware/dead-letters/{id}/requeue` | Reencolar a message_queue |

Requiere ability `bus:admin`.

## Configuración (`config/eventbus.php`)

| Variable | Propósito | Default |
|----------|-----------|---------|
| `EVENTBUS_ASYNC_PROCESSING` | Encolar `ProcessEventJob` | `false` |
| `EVENTBUS_ASYNC_LISTENERS` | Reservado packs ShouldQueue | `false` |
| `EVENTBUS_PROCESSING_TIMEOUT` | Timeout job (seg) | `30` |
| `EVENTBUS_CIRCUIT_BREAKER_ENABLED` | Circuit breaker | `false` |
| `EVENTBUS_CIRCUIT_BREAKER_FAILURES` | Umbral fallos | `5` |
| `EVENTBUS_CIRCUIT_BREAKER_OPEN_SECONDS` | Ventana abierta | `60` |

Retry: `eventbus.retry.max_attempts`, `eventbus.retry.backoff`.

## Producción

```env
EVENTBUS_ASYNC_PROCESSING=true
QUEUE_CONNECTION=redis
# worker: php artisan queue:work redis
```

## Fase 3 — Sagas

Compensación / tabla `transactions`: diferida — ver `ADR_006_saga_transactions.md`.

## Referencias

- [Plan_Resiliencia.md](Plan_Resiliencia.md)
- [BaseDeDatos.md](BaseDeDatos.md)
