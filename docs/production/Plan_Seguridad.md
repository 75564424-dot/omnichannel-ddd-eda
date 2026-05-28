# Plan de Seguridad

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Crítico

---

## 1. Estado Actual

### Qué existe

| Elemento | Ubicación / evidencia |
|----------|----------------------|
| Validación de sobre en publish | `EventQueueController` → 422 si falta `event_id`, `event_type`, etc. |
| Strict types PHP | Widespread en `app/` |
| Pack merge con skip de registrars inválidos | `PackSubscriptionCatalogMerger.php` |
| Credenciales cifradas (esquema) | Tabla `integration_credentials.encrypted_value` — sin uso en runtime |
| Tabla `audit_logs` | Migración `2026_05_21_100004` — sin escrituras desde código |
| Sesiones (tabla) | `sessions` migration — sin flujo de login |
| Logs de advertencia en tracking | `BusTrackingListener` omite eventos sin `event_id` |

### Qué está incompleto

- CORS: no hay `config/cors.php` publicado; comportamiento Laravel por defecto
- CSRF: solo aplica a rutas web; APIs REST sin protección adicional
- Rate limiting: no configurado en rutas middleware/dashboard
- Headers de seguridad: no hay middleware CSP, HSTS, X-Frame-Options
- Validación de input: parcial en publish; resto de endpoints sin Form Requests dedicados

### Riesgos detectados

| Riesgo | Severidad |
|--------|-----------|
| `POST /api/middleware/events/publish` público | **Crítico** |
| `POST /api/middleware/registry/sync-config` público | **Crítico** |
| `PATCH /api/middleware/dead-letters/{id}/resolve` público | **Alto** |
| `GET /api/dashboard/stream` (SSE) sin auth | **Alto** |
| Sin `.env.example` → secretos mal configurados en nuevas instancias | **Medio** |
| Fallback silencioso si `modules_config.json` inválido | **Medio** |

---

## 2. Objetivo

Establecer una **postura de seguridad defensiva** para el middleware omnicanal desplegado en cloud, protegiendo:

- Plano de control (publish, sync, DLQ, configuración de nodos)
- Plano de observabilidad (feed, métricas, stream)
- Secretos y credenciales de integraciones
- Superficie web (Inertia/Vue)

El middleware actúa como **hub de integración**; un compromiso aquí afecta a todos los sistemas conectados.

---

## 3. Problemas Detectados

1. **Superficie de ataque amplia:** 25+ endpoints API sin autenticación
2. **Sin defensa en profundidad:** un WAF o API gateway no está documentado ni configurado en repo
3. **Sin rate limiting:** vulnerable a flood de eventos y DoS en publish
4. **Secretos:** `.env` presente localmente; no hay plantilla ni rotación documentada
5. **Auditoría inexistente en runtime:** tabla `audit_logs` vacía de lógica
6. **Cifrado en tránsito:** depende de infra (TLS); no enforced en app
7. **Sanitización:** payloads JSON almacenados sin validación de schema/contrato

---

## 4. Requerimientos

### Funcionalidades

- [ ] Middleware de autenticación en todas las rutas `/api/middleware/*` y `/api/dashboard/*`
- [ ] Rate limiting por IP y por API key
- [ ] CORS explícito con allowlist de orígenes
- [ ] Security headers (HSTS, CSP, X-Content-Type-Options)
- [ ] Validación JSON Schema opcional en publish
- [ ] Registro en `audit_logs` para acciones de control
- [ ] `.env.example` completo sin secretos reales
- [ ] Documentación de hardening por entorno

### Librerías sugeridas

- `laravel/sanctum` — tokens API
- `spatie/laravel-csp` — Content Security Policy (opcional)
- Validación: JSON Schema via `opis/json-schema` o contratos en `config/eventbus.php`

### Infraestructura

- TLS terminado en load balancer / ingress
- Secrets manager (AWS Secrets Manager, Azure Key Vault, K8s Secrets)
- WAF opcional para endpoints públicos de webhook (fase posterior)

---

## 5. Propuesta Técnica

### Capas de seguridad (defense in depth)

```
Internet → WAF/LB (TLS) → Rate Limiter → Auth Middleware → RBAC → Controller → Domain
```

### Integración DDD + EDA

- **Middleware BC:** `PublishEventPolicy`, `SyncRegistryPolicy` en capa Application
- **Shared:** `ApiKeyAuthenticator`, contrato `AuthenticatedIntegrator`
- Eventos de auditoría: `Security.Audit.ActionRecorded` → proyección en `audit_logs`
- No mezclar lógica de auth en listeners del bus

### Rate limiting sugerido

| Endpoint | Límite |
|----------|--------|
| `POST /events/publish` | 100/min por API key |
| `POST /registry/sync-config` | 10/min |
| `GET /stream` | 5 conexiones concurrentes por token |

### CORS

Publicar `config/cors.php` con orígenes desde `CORS_ALLOWED_ORIGINS` en `.env`.

---

## 6. Roadmap de Implementación

### Fase 1 (inmediata — 2 semanas)

- Crear `.env.example`
- Publicar `config/cors.php`
- Añadir `throttle` middleware a rutas API críticas
- Security headers middleware básico
- Documentar matriz de endpoints y nivel de sensibilidad

### Fase 2 (4 semanas)

- Integrar Sanctum + API keys por integrador
- Proteger todas las rutas API
- Escribir en `audit_logs` en sync, publish admin, DLQ resolve
- Validación JSON Schema en publish (opt-in por `event_type`)

### Fase 3 (8 semanas)

- RBAC granular (ver Plan_Usuarios.md)
- WAF rules documentadas
- Penetration test básico
- Rotación automática de API keys

---

## 7. Prioridad

**Crítico** — Bloqueante para cualquier exposición pública del middleware.

---

## 8. Riesgo si no se implementa

Un atacante puede **publicar eventos arbitrarios**, **alterar el registro de módulos**, **resolver dead letters** sin autorización y **consumir el stream SSE** con datos operativos. En un middleware omnicanal esto implica propagación de datos falsos a sistemas downstream (ERP, inventario, pedidos), con impacto financiero y de compliance directo.

---

## Referencias

- [Plan_Autenticacion.md](Plan_Autenticacion.md)
- [Plan_Usuarios.md](Plan_Usuarios.md)
- `app/Middleware/Interfaces/Routes/api.php`
- `docs/personal_notes/Observabilidad_pruebas_produccion_local.md`
