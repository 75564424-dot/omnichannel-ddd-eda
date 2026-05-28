# Flujo M2M — integradores externos

Guía para sistemas máquina (POS, ERP, ETL) que publican o consultan el bus.

## 1. Obtener credenciales

**Opción A — Sanctum token (recomendado)**

```bash
php artisan platform:issue-api-token \
  --email=erp-acme@integrations.local \
  --name="Acme ERP Production" \
  --abilities=events:publish,bus:read
```

Guardar el token mostrado **una sola vez** en el secret store del integrador.

**Opción B — API key estática**

En `.env` de la instancia:

```env
PLATFORM_API_KEYS=erp-prod-key|events:publish,bus:read
```

## 2. Publicar evento

```bash
curl -X POST "$BASE_URL/api/middleware/events/publish" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "event_id": "550e8400-e29b-41d4-a716-446655440000",
    "event_type": "Order.Placed",
    "occurred_at": "2026-05-21T12:00:00Z",
    "payload": {
      "event_id": "550e8400-e29b-41d4-a716-446655440000",
      "event": "Order.Placed",
      "occurred_at": "2026-05-21T12:00:00Z",
      "order_ref": "ORD-1001"
    }
  }'
```

Alternativa header: `-H "X-API-Key: erp-prod-key"`

## 3. Consultar estado / cola

```bash
curl -H "Authorization: Bearer $TOKEN" "$BASE_URL/api/middleware/status"
curl -H "Authorization: Bearer $TOKEN" "$BASE_URL/api/middleware/queue?limit=20"
```

## 4. SSE dashboard (solo lectura)

EventSource no envía headers custom:

```
GET /api/dashboard/stream?token={sanctum-or-api-key}
```

## 5. Rotación

```bash
php artisan platform:rotate-api-token --email=erp-acme@integrations.local
php artisan platform:list-api-tokens --email=erp-acme@integrations.local
php artisan platform:revoke-api-token {id}
```

## 6. Errores comunes

| HTTP | Causa |
|------|-------|
| 401 | Token/key ausente o inválido |
| 403 | Token sin ability requerida |
| 422 | Envelope incompleto o schema JSON inválido |
| 429 | Rate limit excedido |

## Modelo instancia por cliente

Cada despliegue (ADR-001) tiene su propio par de credenciales. No compartir tokens entre clientes.
