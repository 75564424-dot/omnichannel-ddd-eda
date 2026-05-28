# Runbook de Alertas — Middleware Omnicanal

**Plan:** Plan_Monitoreo.md | **Versión:** 1.0

---

## BusStopped {#busstopped}

**Severidad:** P1 | **Condición:** `bus_stream_status == 0` durante 5 min

### Qué significa

El bus no está procesando eventos (throughput idle). Puede ser caída de workers, scheduler detenido o ausencia total de tráfico en producción.

### Acciones

1. Verificar `/health/ready` y `/up`
2. Revisar workers: `php artisan queue:work` / contenedor `worker` en docker compose
3. Revisar scheduler: contenedor `scheduler` o cron `schedule:run`
4. Consultar `message_queue` recientes y `event_logs` con status `failed`
5. Ejecutar canary manual: `php artisan platform:canary-publish`
6. Escalar a on-call si persiste > 15 min en producción

---

## HighErrorRate {#higherrorrate}

**Severidad:** P1 | **Condición:** `bus_error_rate_percent > 10` durante 5 min

### Acciones

1. GET `/api/middleware/metrics` o panel Grafana
2. Identificar `event_type` con más fallos en `event_logs` / DLQ
3. Revisar circuit breaker y conectores (`Plan_Resiliencia`)
4. Pausar integraciones afectadas si es un canal externo

---

## DLQBacklog {#dlqbacklog}

**Severidad:** P2 | **Condición:** `bus_dlq_unresolved > 10`

### Acciones

1. GET `/api/middleware/dead-letters`
2. Analizar `failure_reason` por entrada
3. Resolver o requeue según runbook de resiliencia
4. Verificar que `platform:purge-retention` no borre evidencia prematuramente

---

## HighLatency {#highlatency}

**Severidad:** P2 | **Condición:** `bus_processing_latency_ms > 2000` durante 5 min

### Acciones

1. Revisar profundidad de cola (`queue_jobs_pending`)
2. Escalar workers Redis/horizon
3. Verificar latencia BD y Redis
4. Revisar listeners síncronos pesados

---

## DiskSpace {#diskspace}

**Severidad:** P2 | **Condición:** `database_usage_percent > 80`

### Acciones

1. Ejecutar `php artisan platform:purge-retention --dry-run`
2. Ajustar retención en `system_configurations` o `.env` (`RETENTION_*_DAYS`)
3. Planificar aumento de volumen RDS/disco
4. Verificar crecimiento de `event_store` y `trace_logs`

---

## CanaryStale {#canarystale}

**Severidad:** P2 | **Condición:** `canary_last_success_age_seconds > 600`

### Acciones

1. `php artisan platform:canary-publish` manualmente
2. Revisar logs del scheduler
3. Verificar auth/API si canary usa HTTP en despliegues custom
4. Confirmar `PLATFORM_CANARY_ENABLED=true`

---

## QueueBacklog {#queuebacklog}

**Severidad:** P2 | **Condición:** `queue_jobs_pending > 1000`

### Acciones

1. Verificar workers activos
2. `php artisan queue:monitor middleware,dashboard-feed --max=1000`
3. Revisar jobs fallidos en `failed_jobs`
4. Escalar réplicas worker

---

## Comandos útiles

```bash
php artisan platform:monitoring-evaluate
php artisan platform:monitoring-evaluate --json
php artisan platform:canary-publish
curl -s http://localhost/metrics
curl -s http://localhost/up
curl -s http://localhost/health/ready
```
