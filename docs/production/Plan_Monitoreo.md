# Plan de Monitoreo y Alertas

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Alto

---

## 1. Estado Actual

### Qué existe

- Umbrales en `config/eventbus.php` (`thresholds`: error_rate, latency, dead_letters)
- `BusStatus` evaluation: ACTIVE, DEGRADED, HI-LOAD, STOPPED
- UI indicadores en `/middleware` (polling APIs)
- Sin integración externa (Datadog, Prometheus, CloudWatch)

### Qué falta

- Reglas de alerta (PagerDuty, Slack, email)
- SLO/SLA documentados
- Uptime monitoring externo
- Synthetic checks (canary publish)

---

## 2. Objetivo

**Monitoreo proactivo** 24/7: detectar degradación del bus antes que el cliente, con alertas accionables.

---

## 3. Problemas Detectados

1. Operador debe mirar UI manualmente
2. Sin alerta si `stream_status = STOPPED`
3. Sin monitor de espacio BD / cola Laravel jobs
4. Health check no expuesto para LB

---

## 4. Requerimientos

- [ ] Alertas: error_rate > 10%, latency > 2s, DLQ > 10, bus STOPPED
- [ ] Uptime check externo (Pingdom, UptimeRobot) en `/up`
- [ ] Synthetic: publish canary cada 5 min
- [ ] Dashboard Grafana o Datadog template en repo
- [ ] Runbook por alerta (qué hacer)

---

## 5. Propuesta Técnica

### Alertas sugeridas

| Alerta | Condición | Severidad |
|--------|-----------|-----------|
| BusStopped | stream_status = STOPPED 5min | P1 |
| HighErrorRate | error_rate > 10% 5min | P1 |
| DLQBacklog | unresolved DLQ > 10 | P2 |
| HighLatency | p95 latency > 2000ms | P2 |
| DiskSpace | BD > 80% | P2 |

### Integración

- Export metrics → Prometheus → Alertmanager
- O Datadog MCP/agent si ya en stack cliente

---

## 6. Roadmap

### Fase 1: `/up` + uptime externo + alertas manuales checklist
### Fase 2: Prometheus + Grafana + Alertmanager
### Fase 3: Synthetic canary + SLO dashboards

---

## 7. Prioridad

**Alto**

---

## 8. Riesgo si no se implementa

Outages prolongados sin detección; violación de SLA; pérdida de confianza del cliente.

---

## Referencias

- [Plan_Observabilidad.md](Plan_Observabilidad.md)
- [Plan_Cloud.md](Plan_Cloud.md)
