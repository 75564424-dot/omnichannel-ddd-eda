# Runbook — Backup y restore de base de datos

**Plan:** Plan_Cloud.md Fase 2

## Backup manual

```bash
export DB_HOST=127.0.0.1
export DB_DATABASE=platform_middleware
export DB_USERNAME=platform
export DB_PASSWORD=secret
bash scripts/ops/backup-database.sh
```

Salida: `storage/backups/<database>_<timestamp>.sql.gz`

Retención default: 14 días (`RETENTION_DAYS`).

## Backup automatizado

### Docker Compose (cron en host)

```cron
0 3 * * * cd /opt/platform-middleware && DB_HOST=127.0.0.1 DB_DATABASE=platform_middleware DB_USERNAME=platform DB_PASSWORD=*** ./scripts/ops/backup-database.sh
```

### Kubernetes

Aplicar `deploy/k8s/cronjob-backup.yaml` con PVC para `/backups`.

### Cloud managed

| Proveedor | Recomendación |
|-----------|---------------|
| AWS RDS | Automated backups + manual snapshot pre-release |
| Azure Database | Point-in-time restore |
| GCP Cloud SQL | Automated backups |

## Restore

```bash
gunzip -c storage/backups/platform_middleware_YYYYMMDD_HHMMSS.sql.gz | \
  mysql -h "$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"
```

## Procedimiento post-restore

1. Verificar migraciones: `php artisan migrate:status`
2. `curl /health/ready`
3. Smoke test
4. Validar cola y métricas en `/middleware`

## RPO / RTO orientativos

| Escenario | RPO | RTO |
|-----------|-----|-----|
| Backup diario + managed snapshots | 24h / 5min | 1–4h |
| Backup horario | 1h | 30min–2h |

Ajustar según SLA del cliente (ADR-001 instancia por cliente).
