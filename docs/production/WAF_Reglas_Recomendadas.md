# WAF — reglas recomendadas

Documentación infra (Plan_Seguridad Fase 3). Aplicar en ALB / Cloudflare / Azure Front Door delante del middleware.

## Reglas base

| ID | Regla | Acción |
|----|-------|--------|
| WAF-01 | Bloquear SQLi en query/body | Block |
| WAF-02 | Bloquear XSS en User-Agent y Referer | Block |
| WAF-03 | Rate limit global IP (1000 req/5min) | Challenge / Block |
| WAF-04 | Geo-block países no operados (opcional) | Block |
| WAF-05 | Permitir solo TLS 1.2+ | Block downgrade |

## Rutas sensibles

| Path pattern | Regla adicional |
|--------------|-----------------|
| `POST /api/middleware/events/publish` | Body size ≤ 256 KB; rate 200/min por IP |
| `POST /api/middleware/registry/sync-config` | Rate 20/min por IP; requerir header `X-API-Key` |
| `GET /api/dashboard/stream` | Limitar conexiones concurrentes por IP (5) |

## Headers requeridos en origen

- `X-Forwarded-Proto: https`
- Eliminar `X-Powered-By` en respuesta (nginx/ingress)

## Excepciones

- Health checks del load balancer: ruta dedicada `/up` (Plan_Cloud) sin WAF agresivo.
- CI/staging: allowlist IP del pipeline si aplica.

## Referencias

- OWASP API Security Top 10
- `Matriz_Endpoints_Seguridad.md`
