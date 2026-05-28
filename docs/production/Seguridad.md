# Seguridad — documentación operativa

Implementación según `Plan_Seguridad.md`.

## Capas activas

```
Internet → (WAF/LB TLS) → Rate Limiter → Auth (Sanctum / API Key) → Abilities → Controller
```

## Autenticación API

| Mecanismo | Uso |
|-----------|-----|
| `Authorization: Bearer {sanctum-token}` | Integradores / operadores M2M |
| `X-API-Key: {key}` | Claves estáticas por instancia (`PLATFORM_API_KEYS`) |
| `?token=` query | SSE `/api/dashboard/stream` (EventSource sin headers) |

### Abilities

| Ability | Descripción |
|---------|-------------|
| `events:publish` | POST publish |
| `bus:read` | GET middleware lectura |
| `bus:admin` | sync, DLQ, metrics refresh, nodos dashboard |
| `dashboard:read` | GET dashboard APIs + stream |

### Comandos

```bash
php artisan platform:issue-api-token --abilities=events:publish,bus:read
php artisan platform:rotate-api-token --keep=0
```

## Desactivar auth (solo tests)

```env
PLATFORM_API_AUTH_ENABLED=false
```

PHPUnit usa este valor por defecto en `phpunit.xml`.

## JSON Schema en publish

```env
EVENTBUS_SCHEMA_VALIDATION=true
```

Registrar schemas en `config/eventbus.php` → `publish_schemas`.

## Documentos relacionados

- [Matriz_Endpoints_Seguridad.md](Matriz_Endpoints_Seguridad.md)
- [Hardening_Por_Entorno.md](Hardening_Por_Entorno.md)
- [WAF_Reglas_Recomendadas.md](WAF_Reglas_Recomendadas.md)
- [Pentest_Checklist_Basico.md](Pentest_Checklist_Basico.md)
