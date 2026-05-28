# Base de datos — operación y retención

Implementación según `Plan_BaseDeDatos.md`.

## Esquema activo

26 tablas middleware (post-migración 2026-05-21). Diccionario: `docs/architecture/middleware_database_dictionary.md`.

## Seeders

| Seeder | Contenido |
|--------|-----------|
| `InstanceTenantSeeder` | Tenant instancia (ADR-001) |
| `MiddlewareDatabaseSeeder` | Canal `middleware` + claves retención en `system_configurations` |
| `PlatformOperatorSeeder` | Operador admin |

```bash
php artisan migrate --seed
```

## Retención

| Tabla | Días default | Config key |
|-------|--------------|------------|
| message_queue | 30 | `retention.message_queue_days` |
| event_logs | 30 | `retention.event_logs_days` |
| observability_metrics | 14 | `retention.observability_metrics_days` |
| event_store | 90 | `retention.event_store_days` |
| audit_logs | 2555 (7 años) | `retention.audit_logs_days` |

### Comando

```bash
php artisan platform:purge-retention
php artisan platform:purge-retention --dry-run
php artisan platform:purge-retention --table=message_queue
```

Programado: diario 02:30 vía `routes/console.php` (requiere scheduler/worker).

### Overrides

- `config/platform_retention.php`
- Variables `.env`: `RETENTION_MESSAGE_QUEUE_DAYS`, etc.
- Filas `system_configurations` (prioridad sobre config file)

## Índices (Fase 2)

Migración `2026_05_21_140000_add_retention_query_indexes.php` — índices en columnas temporales para purge y scans.

Validación MySQL prod:

```sql
EXPLAIN ANALYZE SELECT id FROM message_queue WHERE published_at < NOW() - INTERVAL 30 DAY LIMIT 100;
```

## Backup

Ver [Runbook_Backup_Restore.md](Runbook_Backup_Restore.md) (Plan_Cloud).

## tenant_id

Ver [ADR_004_tenant_id_activation.md](ADR_004_tenant_id_activation.md).

## Greenfield / particionamiento

- [Migration_Greenfield.md](Migration_Greenfield.md)
- [ADR_005_event_store_partitioning.md](ADR_005_event_store_partitioning.md)
