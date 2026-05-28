# Logs y auditoría

Implementación según `Plan_Logs.md`.

## Tres capas de trazabilidad

| Capa | Destino | Contenido |
|------|---------|-----------|
| Técnico | `laravel.log` / stderr JSON | Debug, warnings, errores |
| Operacional | `event_logs` | Ciclo de vida de eventos (hash, no payload completo) |
| Compliance | `audit_logs` | Acciones admin/control-plane |

## Logging JSON (Fase 1)

```env
LOG_CHANNEL=stack
LOG_STACK=json          # local: storage/logs/laravel.json
# Cloud pods:
LOG_STACK=stderr_json   # JSON → stderr → CloudWatch / Loki / ELK
LOG_LEVEL=info
```

Contexto automático en API: `correlation_id`, `event_uuid`, `actor_id`, `tenant_id` (via `ShareCorrelationLogContext`).

## Servicios (Fase 2)

| Servicio | Tabla | Uso |
|----------|-------|-----|
| `AuditLogService` | `audit_logs` | Acciones admin (middleware `platform.audit`) |
| `EventLogService` | `event_logs` | `received`, `processed`, `failed` |
| `PlatformStructuredLogger` | Laravel Log | Mensajes con hash/redacción PII |

## Qué NO loguear

- Credenciales, tokens, API keys (`platform_logging.redact_keys`)
- Payloads completos en `laravel.log` — usar `payload_hash`
- PII en `audit_logs.changes` — middleware sanitiza inputs

## Retención (Fase 3)

Automatizada vía scheduler:

```bash
php artisan platform:purge-retention
# Cron: daily 02:30 (routes/console.php)
```

| Tabla | Default |
|-------|---------|
| `event_logs` | 30 días |
| `audit_logs` | 2555 días (7 años) |

Override: `RETENTION_EVENT_LOGS_DAYS`, `RETENTION_AUDIT_LOGS_DAYS`.

## Cloud shipping (Fase 3)

Patrón recomendado: **`LOG_STACK=stderr_json`** en contenedor → agregador cloud nativo.

Integración directa ELK/CloudWatch agent: ver `ADR_008_cloud_log_shipping.md`.

## Referencias

- [Plan_Logs.md](Plan_Logs.md)
- [Plan_Observabilidad.md](Plan_Observabilidad.md)
- [BaseDeDatos.md](BaseDeDatos.md)
