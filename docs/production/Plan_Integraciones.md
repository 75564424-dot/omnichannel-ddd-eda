# Plan de Integraciones Omnicanal

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Alto

---

## 1. Estado Actual

### Qué existe (esquema BD)

- `channels`, `providers`, `integrations`, `adapters`, `connectors`, `integration_credentials`
- `webhook_requests`, `webhook_responses`
- `notifications`

### Qué existe (runtime)

- Config declarativa: `modules_config.json`, `eventbus.php`
- `registered_modules` — catálogo observado
- `EventOrigin` value object — infiere canal desde payload
- Demo pack: `Platform/Demo/DemoPackEventConsumers.php`
- Contrato: `EventConsumerRegistrationInterface`

### Qué falta entirely

- CRUD/API de channels e integrations
- HTTP controllers para webhooks inbound
- Connectors HTTP activos hacia ERP/CRM
- Uso de `integration_credentials`
- Adapters transform/validate/enrich en pipeline
- Notificaciones outbound

### Riesgos

| Riesgo | Severidad |
|--------|-----------|
| Producto vendido como "hub integración" pero solo config files | **Alto** |
| 15+ tablas sin servicios | **Medio** |
| Webhooks sin signature verification | **Crítico** (cuando se implementen) |

---

## 2. Objetivo

Capa de **integración multi-canal** operativa: POS, e-commerce, ERP, mobile, webhooks — configurable, observable, segura.

---

## 3. Problemas Detectados

1. Integraciones = editar JSON + redeploy (no runtime API)
2. Sin UI admin para alta de canal/proveedor
3. `channels` table empty except seed migration
4. Dual config (eventbus vs modules_config) mitigado por sync pero no unificado

---

## 4. Requerimientos

### Fase integración mínima

- [ ] API admin: CRUD channels, integrations (protegida)
- [ ] Webhook ingress: `POST /api/integrations/webhooks/{integration_code}`
- [ ] Signature verification (HMAC-SHA256)
- [ ] Adapter pipeline interface + 2 implementaciones (validate JSON, map fields)
- [ ] Connector HTTP outbound template
- [ ] Encrypt/decrypt `integration_credentials` via Laravel Crypt
- [ ] Link `channel_id` / `integration_id` en message_queue on publish

### Librerías

- `guzzlehttp/guzzle` — HTTP connectors
- Laravel HTTP client (built-in)

---

## 5. Propuesta Técnica

### Modelo operativo

```
Channel (POS) → Integration → Adapter chain → Event Store → Bus
Provider (ERP) ← Connector ← outbound events
```

### Webhook flow

```
POST webhook → webhook_requests → validate signature → transform → publish → webhook_responses
```

### DDD

- Nuevo subdominio **Integration** dentro de Middleware BC o BC separado Supporting
- Adapters como strategy pattern — registro en container por `adapter_type`

---

## 6. Roadmap

### Fase 1: Webhook ingress + signature + event_store
### Fase 2: Admin API channels/integrations + credentials
### Fase 3: Connector HTTP outbound + adapter marketplace

---

## 7. Prioridad

**Alto** (core value proposition del middleware)

---

## 8. Riesgo si no se implementa

El middleware permanece como "event bus demo" no como plataforma omnicanal enterprise; churn de clientes que esperan conectores listos.

---

## Referencias

- [Plan_Middleware.md](Plan_Middleware.md)
- [Plan_Seguridad.md](Plan_Seguridad.md)
- `docs/architecture/middleware_database_dictionary.md` §2
