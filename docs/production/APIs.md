# APIs y Contratos — Middleware Omnicanal

**Plan:** `Plan_APIs.md` | **Estado:** Implementado (Fases 1–3)

---

## Bases URL

| Versión | Prefijo | Estado |
|---------|---------|--------|
| **v1 (recomendado)** | `/api/v1/middleware`, `/api/v1/dashboard`, `/api/v1/integrations` | Activo |
| Legacy | `/api/middleware`, `/api/dashboard`, `/api/integrations` | Compatible |

---

## Autenticación

- Header `X-API-Key` o Bearer Sanctum (según `Plan_Autenticacion`)
- Abilities por ruta: `bus:read`, `events:publish`, `dashboard:read`, `integrations:admin`

---

## Contratos clave

### Publish event

```http
POST /api/v1/middleware/events/publish
Idempotency-Key: <uuid>
X-Correlation-Id: <uuid>
Content-Type: application/json
```

Body: `event_id`, `event_type`, `payload`, `occurred_at` (requeridos).

### Paginación

```http
GET /api/v1/middleware/queue?page=1&limit=50
GET /api/v1/dashboard/events/feed?page=1&limit=50
```

Respuesta incluye `pagination: { page, limit, total, total_pages }`.

### Errores (RFC 7807)

```http
Content-Type: application/problem+json
```

Campos: `type`, `title`, `status`, `detail`, `instance`.

### Rate limits

Headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`.

---

## Documentación OpenAPI

| Artefacto | Ruta |
|-----------|------|
| OpenAPI 3.0 | `docs/api/openapi.yaml` |
| Postman/Insomnia | `docs/api/postman_collection.json` |
| Changelog | `docs/api/CHANGELOG.md` |
| Breaking change policy | `docs/api/BREAKING_CHANGE_POLICY.md` |

---

## Validación CI

```bash
bash scripts/ci/lint-openapi.sh
composer test -- --filter Api
```

---

## Variables de entorno

Ver `.env.example` sección APIs y `config/platform_api.php`.
