# Plan de Autenticación

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Crítico

---

## 1. Estado Actual

### Qué existe

- Tabla `sessions` (`2026_05_01_234418`) con `user_id` nullable — **sin tabla users**
- `HandleInertiaRequests` comparte `'auth' => []` siempre vacío
- Comentario en `EventStreamController` menciona Sanctum vía query param — **no implementado**
- Validación estructural del envelope en publish (no es autenticación)

### Qué está incompleto

- No hay login, logout, registro ni recuperación de contraseña
- No hay tokens API, JWT ni OAuth2
- No hay middleware `auth` o `auth:sanctum` en rutas API
- No hay gestión de API keys para integradores externos (POS, ERP, webhooks)

### Riesgos detectados

| Riesgo | Severidad |
|--------|-----------|
| Cualquier cliente HTTP puede invocar APIs | **Crítico** |
| Imposible auditar quién publicó un evento | **Alto** |
| SSE stream accesible sin token | **Alto** |
| Sin rotación de credenciales | **Medio** |

---

## 2. Objetivo

Implementar un **modelo de autenticación dual**:

1. **Operadores humanos** — sesión web (Sanctum cookie o login Inertia) para UI `/dashboard` y `/middleware`
2. **Integradores máquina** — API Keys o JWT de corta duración para `POST /events/publish` y webhooks

Debe alinearse con el modelo **instancia por cliente** (Fase D): una instancia = un conjunto de credenciales por despliegue, no multi-tenant en auth layer inicialmente.

---

## 3. Problemas Detectados

1. `composer.json` no incluye `laravel/sanctum` ni passport
2. No existe entidad `User` ni migración
3. OAuth2 mencionado en docs aspiracionales pero sin diseño concreto
4. `integration_credentials` en BD preparada para OAuth/API keys de **proveedores externos**, no para **clientes del middleware**
5. Confusión potencial entre auth de operador vs auth de integración

---

## 4. Requerimientos

### Funcionalidades

- [ ] Modelo User + migración `users`, `personal_access_tokens` (Sanctum)
- [ ] Login/logout web para operadores
- [ ] API tokens con scopes: `publish`, `read:metrics`, `admin:sync`
- [ ] Middleware `AuthenticateApi` en grupos de rutas
- [ ] Documentación de flujo M2M para integradores
- [ ] (Fase 3) OAuth2 client credentials para partners enterprise

### Librerías sugeridas

- `laravel/sanctum` ^4.x (Laravel 11)
- Opcional futuro: `laravel/passport` si se requiere OAuth2 completo

### Infraestructura

- HTTPS obligatorio en producción
- Almacenamiento seguro de `APP_KEY` y tokens
- K8s: secrets para `SANCTUM_STATEFUL_DOMAINS`

---

## 5. Propuesta Técnica

### Arquitectura sugerida

```
┌─────────────────┐     ┌──────────────────┐
│  Operador (UI)  │────▶│ Sanctum Session  │
└─────────────────┘     └────────┬─────────┘
                                 │
┌─────────────────┐     ┌────────▼─────────┐     ┌─────────────┐
│ ERP / POS (M2M) │────▶│ API Key / Token  │────▶│ Middleware  │
└─────────────────┘     └──────────────────┘     │   BC APIs   │
                                                 └─────────────┘
```

### Scopes propuestos

| Scope | Endpoints |
|-------|-----------|
| `events:publish` | POST `/events/publish` |
| `bus:read` | GET queue, metrics, topology, events |
| `bus:admin` | sync-config, DLQ resolve, metrics refresh |
| `dashboard:read` | GET dashboard APIs, stream |

### DDD

- **Nuevo contexto Supporting:** `Identity` o módulo en `Shared/Security`
- Domain: `ApiToken`, `Operator` — sin lógica en listeners del bus
- Application: `IssueApiTokenUseCase`, `ValidateTokenUseCase`

---

## 6. Roadmap de Implementación

### Fase 1 (2–3 semanas)

- Instalar Sanctum
- Migración users + seed operador admin
- Proteger rutas API con `auth:sanctum`
- Token de servicio para smoke tests / CI

### Fase 2 (4 semanas)

- UI login mínima o basic auth temporal para Inertia
- CRUD API keys en panel admin (o artisan `platform:issue-token`)
- Scopes por token
- Tests feature con autenticación

### Fase 3 (8+ semanas)

- OAuth2 client credentials (Passport)
- Integración con IdP enterprise (Azure AD, Okta)
- MFA para operadores admin

---

## 7. Prioridad

**Crítico** — Prerequisito de producción cloud.

---

## 8. Riesgo si no se implementa

Sin autenticación, el middleware es equivalente a un **bus de eventos abierto**. Cualquier actor en la red puede simular ventas, pedidos o sincronizaciones de stock, provocando inconsistencia en sistemas downstream y violaciones de integridad de datos.

---

## Referencias

- [Plan_Seguridad.md](Plan_Seguridad.md)
- [Plan_Usuarios.md](Plan_Usuarios.md)
- `docs/personal_notes/Fase_D_arquitectura_cliente.md`
