# Checklist — Alertas Manuales (pre-Alertmanager)

**Plan:** Plan_Monitoreo.md Fase 1

Use este checklist hasta que Prometheus + Alertmanager estén desplegados.

---

## Frecuencia recomendada

| Check | Frecuencia | Comando / URL |
|-------|------------|---------------|
| Liveness | 1 min (externo) | `GET /up` |
| Alert evaluation | 1 min (scheduler) | `php artisan platform:monitoring-evaluate` |
| Canary synthetic | 5 min (scheduler) | `php artisan platform:canary-publish` |
| UI middleware | Turno operador | `/middleware` |
| DLQ review | Diario | `/api/middleware/dead-letters` |

---

## Umbrales (defaults)

| Alerta | Umbral | Severidad |
|--------|--------|-----------|
| BusStopped | STOPPED ≥ 5 min | P1 |
| HighErrorRate | error_rate > 10% | P1 |
| DLQBacklog | unresolved > 10 | P2 |
| HighLatency | latency > 2000 ms | P2 |
| DiskSpace | BD > 80% | P2 |
| QueueBacklog | jobs > 1000 | P2 |

Configurables vía `PLATFORM_ALERT_*` en `.env`.

---

## Checklist diario operador

- [ ] `platform:monitoring-evaluate` exit code 0
- [ ] Último canary exitoso (`canary_last_success_age_seconds` en `/metrics`)
- [ ] `bus_stream_status` ≠ 0 (STOPPED)
- [ ] `bus_dlq_unresolved` ≤ 10
- [ ] Sin alertas P1 en logs estructurados
- [ ] Espacio BD < 80%

---

## Escalamiento

| Severidad | Acción |
|-----------|--------|
| P1 | On-call inmediato — ver Runbook |
| P2 | Ticket ops en < 4h |
| P3 | Backlog semanal |

Ver [Runbook_Alertas.md](Runbook_Alertas.md) por alerta.
