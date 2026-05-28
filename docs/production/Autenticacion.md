# Autenticación — modelo dual

Implementación según `Plan_Autenticacion.md`, complementando `Plan_Seguridad.md`.

## Modelo dual

| Actor | Mecanismo | Uso |
|-------|-----------|-----|
| **Operador humano** | Sesión web (Sanctum stateful + cookies) | UI `/dashboard`, `/middleware` |
| **Integrador M2M** | Bearer Sanctum PAT o `X-API-Key` | ERP, POS, CI, webhooks |

```
Operador (browser) ──▶ login POST /login ──▶ session cookie ──▶ axios (withCredentials)
Integrador (M2M)   ──▶ Bearer / X-API-Key ──▶ auth.platform middleware
```

## Fase 1 — Base

- Sanctum instalado (`laravel/sanctum`)
- Tablas `users`, `personal_access_tokens`
- Seed operador: `PlatformOperatorSeeder` (via `db:seed`)
- APIs protegidas vía `auth.platform` (tokens + sesión)
- Token servicio: `php artisan platform:issue-api-token`

## Fase 2 — UI + gestión tokens

- Login Inertia: `GET/POST /login`, `POST /logout`
- Rutas web protegidas: middleware `auth.platform.web`
- Comandos: `platform:list-api-tokens`, `platform:revoke-api-token`
- Use cases DDD: `Shared/Identity/Application/*`

## Abilities (scopes)

| Ability | Endpoints |
|---------|-----------|
| `events:publish` | POST `/api/middleware/events/publish` |
| `bus:read` | GET cola, métricas, topología, eventos |
| `bus:admin` | sync-config, DLQ, metrics refresh, nodos |
| `dashboard:read` | GET dashboard APIs, SSE stream |

Operadores con sesión reciben todas las abilities configuradas en `platform_auth.operator_abilities`.

## Variables de entorno

| Variable | Default | Descripción |
|----------|---------|-------------|
| `PLATFORM_WEB_AUTH_ENABLED` | `true` | Gate login en UI |
| `PLATFORM_SEED_ADMIN_OPERATOR` | `true` | Seed admin en `db:seed` |
| `PLATFORM_ADMIN_EMAIL` | `admin@local` | Email operador inicial |
| `PLATFORM_ADMIN_PASSWORD` | `password` | Password inicial (**cambiar en prod**) |
| `PLATFORM_API_AUTH_ENABLED` | `true` | Gate APIs (Plan_Seguridad) |
| `SANCTUM_STATEFUL_DOMAINS` | localhost | Dominios cookie SPA |

## Onboarding operador

```bash
php artisan migrate --seed
# Login: admin@local / password (cambiar en prod)
```

## Onboarding integrador M2M

Ver [Flujo_M2M_Integradores.md](Flujo_M2M_Integradores.md).

## Fase 3 — Enterprise (diferida)

OAuth2 / IdP / MFA documentados en [ADR_002_autenticacion_enterprise.md](ADR_002_autenticacion_enterprise.md).
