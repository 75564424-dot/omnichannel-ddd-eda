# Cloud — Despliegue y operación

Implementación según `Plan_Cloud.md`.

## Artefactos

| Artefacto | Ubicación |
|-----------|-----------|
| Dockerfile multi-stage | `Dockerfile` (`fpm` prod, `serve` CI/smoke) |
| Stack local | `docker-compose.yml` |
| Nginx | `docker/nginx/default.conf` |
| Entrypoint | `docker/entrypoint.sh` |
| K8s manifests | `deploy/k8s/` |
| Terraform skeleton | `deploy/terraform/` |
| Backup BD | `scripts/ops/backup-database.sh` |

## Health checks

| Ruta | Uso | Auth |
|------|-----|------|
| `GET /up` | Liveness (Laravel built-in) | No |
| `GET /health/ready` | Readiness (DB + Redis si configurado) | No |

Load balancers e Ingress deben usar `/up` para liveness y `/health/ready` para readiness.

## Stack local (Docker Compose)

```bash
cp .env.example .env
# Editar APP_KEY, passwords MySQL, PLATFORM_API_KEYS
docker compose up -d --build
curl http://localhost:8080/up
curl http://localhost:8080/health/ready
```

Servicios: `mysql`, `redis`, `app` (php-fpm), `nginx`, `worker`, `scheduler`.

## Producción recomendada

```
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
```

## Kubernetes

```bash
kubectl apply -f deploy/k8s/namespace.yaml
kubectl apply -f deploy/k8s/configmap-env.yaml
kubectl apply -f deploy/k8s/configmap-nginx.yaml
kubectl apply -f deploy/k8s/deployment-web.yaml
kubectl apply -f deploy/k8s/deployment-worker.yaml
kubectl apply -f deploy/k8s/service.yaml
kubectl apply -f deploy/k8s/ingress.yaml
kubectl apply -f deploy/k8s/hpa.yaml
kubectl apply -f deploy/k8s/cronjob-backup.yaml
```

Ajustar imagen, secrets y host TLS antes de producción.

## Referencias

- [Runbook_Deploy_VM.md](Runbook_Deploy_VM.md)
- [Runbook_Backup_Restore.md](Runbook_Backup_Restore.md)
- [Runbook_DR_Drill.md](Runbook_DR_Drill.md)
- [Cloud_CDN_Assets.md](Cloud_CDN_Assets.md)
