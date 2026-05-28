# Runbook — Deploy manual en VM

**Plan:** Plan_Cloud.md Fase 1 | **Audiencia:** DevOps / operador

## Prerrequisitos

- Ubuntu 22.04+ o RHEL 8+
- Docker Engine 24+ y Docker Compose v2
- Puertos: 80/443 (web), 3306 (MySQL interno), 6379 (Redis interno)
- DNS apuntando al host

## 1. Preparar servidor

```bash
sudo apt update && sudo apt install -y docker.io docker-compose-plugin git
sudo usermod -aG docker $USER
```

## 2. Clonar y configurar

```bash
git clone <repo-url> /opt/platform-middleware
cd /opt/platform-middleware
cp .env.example .env
```

Editar `.env`:

- `APP_ENV=production`, `APP_DEBUG=false`
- `APP_URL=https://middleware.cliente.com`
- `APP_KEY` — `php artisan key:generate`
- MySQL/Redis credentials
- `PLATFORM_ADMIN_PASSWORD`, `PLATFORM_API_KEYS`
- `PLATFORM_CLIENT_SLUG`, `PLATFORM_CLIENT_NAME`

## 3. Build y arranque

```bash
docker compose up -d --build
docker compose ps
curl -fsS http://127.0.0.1:8080/up
curl -fsS http://127.0.0.1:8080/health/ready
```

## 4. Post-deploy

```bash
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan platform:ensure-instance-tenant
curl -X POST http://127.0.0.1:8080/api/middleware/registry/sync-config \
  -H "X-API-Key: <key>" -H "Accept: application/json"
```

Smoke completo: `bash scripts/ci/smoke-test.sh`

## 5. TLS (reverse proxy externo)

Opción recomendada: Caddy o nginx en el host delante de `8080`:

```
middleware.cliente.com -> 127.0.0.1:8080
```

Certificado Let's Encrypt en el proxy, no en el contenedor app.

## 6. Actualización (rolling manual)

```bash
git pull
docker compose build app worker scheduler
docker compose up -d --no-deps app worker scheduler nginx
curl -fsS http://127.0.0.1:8080/health/ready
```

## 7. Rollback

```bash
git checkout <tag-anterior>
docker compose build && docker compose up -d
# Restaurar BD solo si migración incompatible — ver Runbook_Backup_Restore.md
```

## Checklist

- [ ] `/up` responde 200
- [ ] `/health/ready` responde 200 con DB + Redis ok
- [ ] Smoke test verde
- [ ] Password admin cambiado
- [ ] Backup programado (cron o managed snapshots)
