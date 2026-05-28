# Plan de APIs y Contratos

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Medio

---

## 1. Estado Actual

### Middleware API (`/api/middleware/`)

| Método | Ruta | Auth |
|--------|------|------|
| GET | metrics, queue, topology, status, events/{id}, dead-letters | No |
| POST | metrics/refresh, events/publish, registry/sync-config | No |
| PATCH | dead-letters/{id}/resolve | No |

### Dashboard API (`/api/dashboard/`)

| Método | Ruta | Auth |
|--------|------|------|
| GET | snapshot, metrics/*, modules/catalog, events/feed, nodes/*, stream | No |
| POST | nodes/{node}/refresh | No |
| PATCH | nodes/{node}/middleware-events | No |

### Qué existe

- JSON responses `{ success, data/error }`
- Validación 422 en publish
- Envelope contract en `dashboard_config.json`
- DTOs en Application layer

### Qué falta

- Versionado `/api/v1/`
- OpenAPI 3.0 spec
- Problem Details (RFC 7807)
- Idempotency-Key header
- Pagination estándar en queue/feed
- Webhook API endpoints
- Rate limit headers (`X-RateLimit-*`)

---

## 2. Objetivo

APIs **documentadas, versionadas y contract-first** para integradores omnicanal y operadores.

---

## 3. Problemas Detectados

1. Breaking changes posibles sin versionado
2. Integradores deben leer código fuente para contrato
3. Error responses inconsistentes
4. SSE stream sin documentación OpenAPI

---

## 4. Requerimientos

- [ ] OpenAPI 3.0 en `docs/api/openapi.yaml`
- [ ] Prefix `/api/v1/`
- [ ] Standard error envelope
- [ ] Pagination: `?page=&limit=` en queue y feed
- [ ] Idempotency-Key en publish
- [ ] Postman/Insomnia collection generada
- [ ] Changelog API por release

### Herramientas

- `darkaonline/l5-swagger` o `knuckleswtf/scribe`
- Spectral lint para OpenAPI en CI

---

## 5. Propuesta Técnica

### Versionado

- v1 = current behavior frozen
- v2 = cuando se añada auth obligatorio (breaking) — coordinar con clientes

### Contrato publish (canonical)

```yaml
PublishEventRequest:
  required: [event_id, event_type, payload, occurred_at]
  properties:
    event_id: { type: string, format: uuid }
    event_type: { type: string }
    payload: { type: object }
    occurred_at: { type: string, format: date-time }
    origin: { type: string }
    correlation_id: { type: string, format: uuid }
```

---

## 6. Roadmap

### Fase 1: OpenAPI from Scribe + document current endpoints
### Fase 2: /api/v1/ prefix + pagination
### Fase 3: Contract tests + breaking change policy

---

## 7. Prioridad

**Medio** (sube a Alto cuando haya integradores externos)

---

## 8. Riesgo si no se implementa

Integraciones frágiles; breaking changes silenciosos; onboarding lento de partners.

---

## Referencias

- [Plan_Autenticacion.md](Plan_Autenticacion.md)
- [Plan_Integraciones.md](Plan_Integraciones.md)
- `app/Middleware/Interfaces/Routes/api.php`
