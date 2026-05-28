# Plan Cloud y Despliegue

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Crítico

---

## 1. Estado Actual

### Qué existe

- Laravel 11 + PHP 8.2+ (`composer.json`)
- Vite build frontend (`npm run build`)
- Scripts composer: `test`, post-autoload
- Comandos artisan operativos: `demo:reset-operational`, `platform:demo-dashboard-events`
- Documentación runbooks manuales (sin automatización infra)

### Qué está incompleto

- Sin `Dockerfile` ni `docker-compose.yml` en raíz del proyecto
- Sin manifests Kubernetes, Terraform, Helm
- Sin health/readiness en `bootstrap/app.php`
- Sin estrategia de cache distribuido documentada en repo
- Sin CDN para assets (depende de deploy manual)

### Qué falta entirely

- `.env.example`
- Backup/restore runbook en `docs/production/`
- Blue/green o canary deployment
- Auto-scaling policies
- CDN + object storage para estáticos en cloud

### Riesgos detectados

| Riesgo | Severidad |
|--------|-----------|
| Despliegue no reproducible | **Crítico** |
| Sin health check para load balancer | **Alto** |
| SQLite en dev vs MySQL prod sin paridad documentada | **Medio** |
| Sin backup automatizado BD | **Alto** |

---

## 2. Objetivo

Habilitar **despliegue cloud enterprise** del middleware con:

- Contenedores reproducibles
- Orquestación (Docker Compose dev/staging, K8s prod)
- Health/readiness probes
- Persistencia MySQL/PostgreSQL + Redis cache/queue
- Alta disponibilidad (mínimo 2 réplicas app + BD managed)

---

## 3. Problemas Detectados

1. Cada entorno se monta manualmente siguiendo runbooks largos
2. `QUEUE_CONNECTION=sync` en tests y posiblemente dev — prod necesita `database` o `redis`
3. Migraciones complejas (legacy → new schema) requieren estrategia upgrade documentada
4. Sin separación web/worker/scheduler en deploy

---

## 4. Requerimientos

### Artefactos

- [ ] `Dockerfile` multi-stage (composer + node build + php-fpm)
- [ ] `docker-compose.yml` (app, nginx, mysql, redis)
- [ ] `.env.example` con todas las variables
- [ ] `docker/entrypoint.sh` — migrate, cache, queue worker
- [ ] Health: Laravel `/up` + custom `/health/ready` (DB + redis ping)
- [ ] K8s: Deployment, Service, Ingress, HPA (fase 2)
- [ ] Backup: mysqldump cron o managed snapshots

### Infraestructura cloud sugerida

| Componente | AWS | Azure | GCP |
|------------|-----|-------|-----|
| Compute | ECS/EKS | AKS | GKE |
| BD | RDS MySQL | Azure Database | Cloud SQL |
| Cache | ElastiCache Redis | Azure Cache | Memorystore |
| Secrets | Secrets Manager | Key Vault | Secret Manager |
| Logs | CloudWatch | Monitor | Cloud Logging |

---

## 5. Propuesta Técnica

### Topología mínima producción

```
                    ┌─────────────┐
    Integradores ──▶│   Ingress   │── TLS
                    └──────┬──────┘
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
         [App Pod 1] [App Pod 2]  [Queue Worker]
              │            │            │
              └────────────┼────────────┘
                           ▼
                    [MySQL + Redis]
```

### Procesos Laravel en prod

- **Web:** php-fpm + nginx (API + Inertia)
- **Worker:** `php artisan queue:work` (cuando listeners async)
- **Scheduler:** `schedule:run` para métricas snapshot, retention purge

### Variables críticas

```
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
```

---

## 6. Roadmap de Implementación

### Fase 1 (2–3 semanas)

- Dockerfile + docker-compose local
- `.env.example`
- `/up` health route
- Runbook deploy manual VM

### Fase 2 (4–6 semanas)

- K8s manifests básicos
- Redis queue + cache
- Backup automatizado BD
- Staging environment

### Fase 3 (8+ semanas)

- HPA, multi-AZ
- CDN para assets
- DR drill documentado
- Infrastructure as Code (Terraform)

---

## 7. Prioridad

**Crítico**

---

## 8. Riesgo si no se implementa

Imposible escalar horizontalmente; despliegues frágiles; downtime prolongado en incidentes; imposible cumplir SLAs enterprise.

---

## Referencias

- [Plan_CI_CD.md](Plan_CI_CD.md)
- [Plan_Monitoreo.md](Plan_Monitoreo.md)
- `docs/personal_notes/Prueba_para_simular_cliente_Despliegue.md`
