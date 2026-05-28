# Monitoreo y Alertas — Middleware Omnicanal

**Plan:** `Plan_Monitoreo.md` | **Estado:** Implementado (Fases 1–3)

---

## Resumen

Monitoreo proactivo 24/7 mediante:

1. **Uptime externo** en `/up` + readiness `/health/ready`
2. **Prometheus + Alertmanager** (templates en repo)
3. **Canary sintético** cada 5 min (`platform:canary-publish`)
4. **Evaluación interna** cada min (`platform:monitoring-evaluate`)

---

## Arquitectura

```
/metrics (Prometheus) → alert_rules.yml → Alertmanager → Slack/PagerDuty
        ↑
BusHealthService + QueueDepth + DatabaseCapacity + Canary

Scheduler → platform:monitoring-evaluate (logs P1/P2)
         → platform:canary-publish (synthetic)
         → platform:purge-retention (trace_logs incluido)
```

BC **Monitoring** (`app/Monitoring/`) — Supporting Domain, desacoplado de Middleware/Dashboard Application.

---

## Comandos

```bash
php artisan platform:monitoring-evaluate
php artisan platform:monitoring-evaluate --json
php artisan platform:canary-publish
```

---

## Infraestructura en repo

| Artefacto | Ruta |
|-----------|------|
| Reglas Prometheus | `docs/monitoring/prometheus/alert_rules.yml` |
| Scrape config | `docs/monitoring/prometheus/prometheus.yml` |
| Alertmanager | `docs/monitoring/alertmanager/alertmanager.yml` |
| Runbook | `docs/monitoring/Runbook_Alertas.md` |
| Uptime checklist | `docs/monitoring/Uptime_Checklist.md` |
| Alertas manuales | `docs/monitoring/Alertas_Manual_Checklist.md` |
| SLO dashboard | `docs/observability/grafana/slo_dashboard.json` |

---

## Variables de entorno

Ver `.env.example` sección Monitoreo y `config/platform_monitoring.php`.

---

## Referencias

- [Plan_Observabilidad.md](Plan_Observabilidad.md) — `/metrics`, Grafana base
- [Observabilidad.md](Observabilidad.md) — SLIs
- [Plan_Cloud.md](Plan_Cloud.md) — despliegue scheduler/worker
