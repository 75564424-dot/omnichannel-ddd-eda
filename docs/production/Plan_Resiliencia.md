# Plan de Resiliencia

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Alto

---

## 1. Estado Actual

### Qué existe

- `message_queue` con `attempt_count`, `max_attempts`, status lifecycle
- `dead_letter_queue` + sync desde `failed_jobs`
- `retries` table — sin uso en código
- `config/eventbus.php`: retry config, thresholds
- Idempotencia: `event_uuid` UNIQUE en message_queue y event_feed_projections
- `BusTrackingListener`: segundo dispatch idempotente (marca processed)
- Listeners **síncronos** — no `ShouldQueue`

### Qué está incompleto

- Reintentos no automatizados vía tabla `retries`
- Publish duplicado falla con QueryException — no respuesta idempotente 200
- Cola Laravel async no usada en runtime
- Sin circuit breaker para integraciones externas
- Sin compensación (sagas) — tabla `transactions` sin código

### Riesgos

| Riesgo | Severidad |
|--------|-----------|
| Fallo en listener bloquea request sync | **Alto** |
| Sin retry backoff exponencial | **Medio** |
| DLQ sync manual desde failed_jobs | **Medio** |

---

## 2. Objetivo

Garantizar **entrega confiable** de eventos omnicanal: at-least-once delivery, idempotencia, reintentos, DLQ operable, tolerancia a fallos parciales.

---

## 3. Problemas Detectados

1. `QUEUE_CONNECTION=sync` en dev/test — prod behavior diferente
2. `retries` table orphan
3. No hay política de requeue desde DLQ automatizada
4. Sin timeout en processing

---

## 4. Requerimientos

- [ ] Listeners críticos implementan `ShouldQueue` (configurable)
- [ ] Job `ProcessEventJob` con retry backoff (config/eventbus.php)
- [ ] Población `retries` en cada attempt
- [ ] Publish idempotente: mismo event_id → 200 OK (no 500)
- [ ] API requeue DLQ → message_queue
- [ ] Retention purge job para message_queue antigua
- [ ] Circuit breaker pattern para connectors (fase 3)

---

## 5. Propuesta Técnica

### Ciclo de resiliencia

```
Publish → message_queue (pending)
       → [retry 1..N via retries table]
       → completed | dead_letter_queue
       → operador: resolve | requeue
```

### Idempotencia publish

```php
if ($repo->existsByEventId($id)) {
    return response()->json(['status' => 'already_processed'], 200);
}
```

### DDD

- Resiliencia en **Infrastructure** — no contaminar Domain entities
- `RetryPolicy` value object en Middleware Domain

---

## 6. Roadmap

### Fase 1: Idempotent publish response + retention config
### Fase 2: Async queue workers + retries table wiring
### Fase 3: Circuit breaker + saga transactions

---

## 7. Prioridad

**Alto**

---

## 8. Riesgo si no se implementa

Pérdida de eventos en picos de carga; DLQ ignorada; inconsistencia entre canales; incidentes en Black Friday / peak retail.

---

## Referencias

- [Plan_Middleware.md](Plan_Middleware.md)
- `config/eventbus.php`
- `app/Middleware/Infrastructure/Persistence/EloquentDeadLetterRepository.php`
