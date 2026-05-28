# Matriz de endpoints — sensibilidad de seguridad

Documento operativo (Plan_Seguridad Fase 1). Clasifica la superficie API del middleware omnicanal.

## Leyenda

| Nivel | Descripción |
|-------|-------------|
| **Público** | Sin datos sensibles; no expuesto en producción sin auth |
| **Lectura operativa** | Observabilidad; requiere `bus:read` o `dashboard:read` |
| **Control** | Acciones administrativas; requiere `bus:admin` |
| **Ingesta** | Publicación de eventos; requiere `events:publish` |

## Middleware (`/api/middleware`)

| Método | Ruta | Nivel | Ability | Rate limit |
|--------|------|-------|---------|------------|
| GET | `/status` | Lectura | `bus:read` | 120/min |
| GET | `/queue` | Lectura | `bus:read` | 120/min |
| GET | `/metrics` | Lectura | `bus:read` | 120/min |
| GET | `/topology` | Lectura | `bus:read` | 120/min |
| GET | `/events/{id}` | Lectura | `bus:read` | 120/min |
| GET | `/dead-letters` | Lectura | `bus:read` | 120/min |
| POST | `/events/publish` | **Ingesta** | `events:publish` | 100/min |
| POST | `/registry/sync-config` | **Control** | `bus:admin` | 10/min |
| POST | `/metrics/refresh` | **Control** | `bus:admin` | 120/min |
| PATCH | `/dead-letters/{id}/resolve` | **Control** | `bus:admin` | 120/min |
| POST | `/dead-letters/{id}/requeue` | **Control** | `bus:admin` | 120/min |

## Integrations (`/api/integrations`)

| Método | Ruta | Nivel | Ability | Rate limit |
|--------|------|-------|---------|------------|
| POST | `/webhooks/{code}` | **Ingesta** | HMAC signature | 100/min |
| GET | `/channels` | Control | `integrations:admin` | 120/min |
| POST | `/channels` | Control | `integrations:admin` | 120/min |
| GET/PATCH/DELETE | `/channels/{id}` | Control | `integrations:admin` | 120/min |
| GET/POST | `/` (integrations) | Control | `integrations:admin` | 120/min |
| GET/PATCH/DELETE | `/{id}` | Control | `integrations:admin` | 120/min |
| POST | `/{id}/credentials` | Control | `integrations:admin` | 120/min |
| POST | `/{id}/connectors/{connectorId}/dispatch` | Control | `integrations:admin` | 120/min |

## Dashboard (`/api/dashboard`)

| Método | Ruta | Nivel | Ability | Rate limit |
|--------|------|-------|---------|------------|
| GET | `/snapshot` | Lectura | `dashboard:read` | 120/min |
| GET | `/metrics/*` | Lectura | `dashboard:read` | 120/min |
| GET | `/events/feed` | Lectura | `dashboard:read` | 120/min |
| GET | `/stream` | Lectura (SSE) | `dashboard:read` | 60/min |
| GET | `/modules/catalog` | Lectura | `dashboard:read` | 120/min |
| GET | `/nodes/status` | Lectura | `dashboard:read` | 120/min |
| POST | `/nodes/{node}/refresh` | **Control** | `bus:admin` | 120/min |
| PATCH | `/nodes/{node}/middleware-events` | **Control** | `bus:admin` | 120/min |

## Web UI (`/dashboard`, `/middleware`)

| Ruta | Nivel | Notas |
|------|-------|-------|
| `/dashboard` | Lectura | UI Inertia; APIs subyacentes protegidas |
| `/middleware` | Lectura + control | Botones invocan APIs con auth |

## Auditoría

Acciones registradas en `audit_logs`:

- `registry.sync`
- `events.publish`
- `dead_letter.resolve`
- `metrics.refresh`

## Referencias

- `config/security.php`
- `Plan_Autenticacion.md` (login UI — plan separado)
- `Plan_Usuarios.md` (RBAC granular — Fase 3 diferida)
