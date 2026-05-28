# Checklist — Uptime Monitoring Externo

**Plan:** Plan_Monitoreo.md Fase 1

---

## Endpoints a monitorear

| Probe | URL | Esperado | Uso |
|-------|-----|----------|-----|
| Liveness | `GET {APP_URL}/up` | HTTP 200 | UptimeRobot, Pingdom, Route53 health |
| Readiness | `GET {APP_URL}/health/ready` | HTTP 200 + `"status":"ready"` | LB/orquestador (K8s readiness) |
| Metrics | `GET {APP_URL}/metrics` | HTTP 200 + texto Prometheus | Prometheus scrape (interno) |

**Nota:** `/up` no requiere autenticación. `/metrics` debe restringirse en producción (security group / nginx allowlist).

---

## Configuración UptimeRobot (ejemplo)

1. Monitor type: **HTTP(s)**
2. URL: `https://<cliente>.example.com/up`
3. Interval: **1 min**
4. Alert contacts: email + Slack webhook
5. Optional: keyword monitor en `/health/ready` buscando `"ready"`

---

## Configuración Pingdom (ejemplo)

1. Check type: **Uptime**
2. URL: `/up`
3. Response time threshold: 3000 ms
4. Integration: PagerDuty para P1

---

## Checklist operativo manual (sin herramienta externa)

- [ ] `/up` responde 200 desde red externa
- [ ] `/health/ready` responde 200 con BD ok
- [ ] Scheduler ejecutando (`platform:canary-publish`, `platform:monitoring-evaluate`, `platform:purge-retention`)
- [ ] Worker Redis activo si `QUEUE_CONNECTION=redis`
- [ ] Prometheus scrape `/metrics` cada 30s
- [ ] Alertmanager enruta a Slack/PagerDuty
- [ ] Runbook `Runbook_Alertas.md` accesible al on-call

---

## Variables relacionadas

Ver `config/platform_monitoring.php` → `uptime` y `.env.example` sección Monitoreo.
