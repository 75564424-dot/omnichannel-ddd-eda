# Integraciones omnicanal

Implementación según `Plan_Integraciones.md`.

## Modelo operativo

```
Channel → Integration → Adapter chain → Event Store → Bus
Provider ← Connector ← outbound events
```

## Webhook ingress (Fase 1)

| Método | Ruta | Auth |
|--------|------|------|
| POST | `/api/integrations/webhooks/{integration_code}` | HMAC-SHA256 |

Header: `X-Webhook-Signature: sha256=<hex>` (configurable).

Flujo: `webhook_requests` → verify → adapters → publish → `webhook_responses`.

## Admin API (Fase 2)

Requiere ability `integrations:admin` (incluida en `platform_admin` / `bus_operator`).

| Recurso | Rutas |
|---------|-------|
| Channels | `GET/POST /api/integrations/channels`, `GET/PATCH/DELETE .../{id}` |
| Integrations | `GET/POST /api/integrations`, `GET/PATCH/DELETE .../{id}` |
| Credentials | `POST /api/integrations/{id}/credentials` (Laravel Crypt) |

Tipos de credencial: `webhook_hmac_secret`, `api_bearer_token`.

## Adapters (Fase 3)

| Tipo | Rol |
|------|-----|
| `json_validate` | Campos requeridos |
| `field_map` | Renombrar campos a envelope canónico |

Registro en `adapters` table o `integration.config.adapters`.

## Outbound HTTP connector

`POST /api/integrations/{id}/connectors/{connectorId}/dispatch` con body `{ "payload": {...} }`.

Implementación: `HttpOutboundConnector` (Laravel HTTP client).

## Variables de entorno

| Variable | Default |
|----------|---------|
| `INTEGRATIONS_WEBHOOK_SIGNATURE_HEADER` | `X-Webhook-Signature` |
| `INTEGRATIONS_WEBHOOK_REQUIRE_SECRET` | `true` |

## Referencias

- [Plan_Integraciones.md](Plan_Integraciones.md)
- [Middleware.md](Middleware.md)
- [Seguridad.md](Seguridad.md)
