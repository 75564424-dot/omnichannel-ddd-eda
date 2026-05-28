# Hardening por entorno

Guía de configuración defensiva (Plan_Seguridad).

## Local / desarrollo

```env
APP_DEBUG=true
PLATFORM_API_AUTH_ENABLED=false   # opcional para DX; preferible true con key local
PLATFORM_API_KEYS=dev-local|events:publish,bus:read,bus:admin,dashboard:read
PLATFORM_SECURITY_HEADERS=true
CORS_ALLOWED_ORIGINS=http://localhost:8000,http://127.0.0.1:8000
EVENTBUS_SCHEMA_VALIDATION=false
```

- No commitear `.env` ni claves reales.
- Ejecutar `php artisan platform:issue-api-token` para tokens Sanctum de prueba.

## Staging

```env
APP_DEBUG=false
PLATFORM_API_AUTH_ENABLED=true
PLATFORM_API_KEYS=<rotating-staging-key>|events:publish,bus:read,bus:admin,dashboard:read
PLATFORM_AUDIT_ENABLED=true
CORS_ALLOWED_ORIGINS=https://staging.cliente.example
SANCTUM_STATEFUL_DOMAINS=staging.cliente.example
EVENTBUS_SCHEMA_VALIDATION=true
```

- TLS terminado en load balancer.
- Secretos en vault (no variables planas en compose).
- Smoke CI usa `X-API-Key` dedicada de corta vida.

## Producción

```env
APP_DEBUG=false
APP_ENV=production
PLATFORM_API_AUTH_ENABLED=true
PLATFORM_API_KEYS=<from-secrets-manager>
PLATFORM_SECURITY_HEADERS=true
PLATFORM_HEADER_HSTS=max-age=31536000; includeSubDomains; preload
PLATFORM_AUDIT_ENABLED=true
CORS_ALLOWED_ORIGINS=https://middleware.cliente.example
EVENTBUS_SCHEMA_VALIDATION=true
```

Checklist:

1. `APP_KEY` único por instancia (ADR-001).
2. Rotación periódica: `php artisan platform:rotate-api-token`.
3. Rate limits acordes a volumen EPS del cliente.
4. Backup y retención de `audit_logs`.
5. WAF delante del ingress (ver `WAF_Reglas_Recomendadas.md`).

## Secretos

| Secreto | Almacenamiento |
|---------|----------------|
| `APP_KEY` | Secrets Manager / K8s Secret |
| `PLATFORM_API_KEYS` | Secrets Manager — una clave por integrador en Fase posterior |
| Sanctum tokens | Emitidos on-demand; no en repo |
| DB credentials | Managed DB + IAM |
