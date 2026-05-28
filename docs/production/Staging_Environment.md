# Staging environment — Plan_Cloud Fase 2

Perfil de despliegue intermedio entre local y producción.

## Opción A — Docker Compose (recomendado staging on-prem)

```bash
cp .env.example .env
# APP_ENV=staging, credenciales staging
docker compose -f docker-compose.yml up -d --build
```

Variables sugeridas:

```env
APP_ENV=staging
APP_DEBUG=false
PLATFORM_CLIENT_SLUG=acme-staging
PLATFORM_API_AUTH_ENABLED=true
```

## Opción B — CI GitHub Actions

Workflow `.github/workflows/staging.yml`:

- Build imagen `platform-event-bus-core:staging`
- Smoke: `/up`, `/health/ready`, sync, publish
- Job `deploy-staging` — conectar registry + target cloud con secrets

Secrets requeridos (configurar en GitHub):

| Secret | Uso |
|--------|-----|
| `STAGING_REGISTRY` | Push imagen |
| `STAGING_KUBECONFIG` | kubectl apply |
| `STAGING_HOST` | VM SSH deploy |

## Validación staging

```bash
APP_URL=https://staging.cliente.com bash scripts/ci/smoke-test.sh
```

## Promote a producción

1. Tag release en main
2. Misma imagen probada en staging → prod registry
3. Manual approval en workflow `deploy-production`
