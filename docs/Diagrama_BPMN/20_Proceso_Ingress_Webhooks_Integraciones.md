# PROC-011 — Ingress webhooks integraciones

**ID:** PROC-011  
**Versión documento:** 1.0  
**Fecha:** 2026-06-27  
**Estado:** Implementado parcial  
**Tipo:** Técnico — Integración / Operativo  
**Macroproceso:** MP-08 Integración Omnicanal

---

## Descripción

Proceso de recepción de webhooks desde canales externos (POS, ERP, e-commerce), validación de firma HMAC-SHA256, resolución de integración activa, transformación del payload a envelope de bus y publicación al middleware (PROC-001). Registra auditoría de cada request en tabla de webhooks. Estado **implementado parcial** según matriz: esquema BD y tests existen; cobertura operativa completa pendiente validación en producción.

---

## Objetivo

Convertir eventos HTTP inbound de sistemas fuente en publicaciones confiables al bus omnicanal, cumpliendo REQ-INT-01 y REQ-C1, con verificación criptográfica de origen y trazabilidad de ingress.

---

## Alcance

**Incluye:**

- ACT-023: recepción webhook externo (`ReceiveWebhookUseCase`).
- Endpoint `POST /api/integrations/webhooks/{integrationCode}`.
- Verificación HMAC vía `WebhookSignatureGateService` / `WebhookSignatureVerifier`.
- Resolución integración activa (`WebhookInboundIntegrationResolver`).
- Transformación y publish (`WebhookIngressProcessor` → PROC-001).
- Auditoría request (`WebhookIngressAuditor` — received/success/failure).
- Throttle `platform-publish` en ingress.

**Excluye:**

- CRUD configuración integraciones (PROC-012).
- Autenticación API key estándar en ingress (HMAC, no Bearer).
- Reglas negocio vertical sobre payload (REQ-RST-01).
- Conectores outbound (PROC-012 dispatch).

---

## Actores

| Actor | Rol |
|-------|-----|
| Canal externo | POS, ERP, e-commerce envía webhook |
| `WebhookIngressController` | Punto entrada HTTP |
| `ReceiveWebhookUseCase` | Orquesta verify → process → audit |
| `WebhookSignatureVerifier` | HMAC-SHA256 |
| Admin integraciones | Configura secret en PROC-012 |
| Middleware bus | Destino publicación PROC-001 |

---

## Entradas

| Entrada | Formato | Origen |
|---------|---------|--------|
| HTTP POST body | JSON raw | Canal externo |
| Header firma | `X-Webhook-Signature` (configurable) | Canal externo |
| `integrationCode` | Path param | URL ingress |
| Secret integración | Cifrado en BD | PROC-012 credentials |
| Config webhooks | `config/integrations.php` | Host |

---

## Salidas

| Salida | Descripción |
|--------|-------------|
| HTTP 202 | Webhook aceptado y publicado |
| JSON response | `webhook_request_id`, `event_id`, `entry_id` |
| Fila webhook audit | Request registrado |
| Evento en bus | Envelope publicado (PROC-001) |
| HTTP 401/422 | Firma inválida o procesamiento fallido |

---

## Reglas de negocio

| ID | Regla | Evidencia |
|----|-------|-----------|
| RN-011-01 | Ingress verifica firma HMAC antes de procesar | `WebhookSignatureVerifier`; Plan_Integraciones |
| RN-011-02 | Integración debe estar activa por código | `WebhookInboundIntegrationResolver` |
| RN-011-03 | Webhook válido → transform → ACT-002 publish | FLU-029 `flujo_bpmn.csv` |
| RN-011-04 | Secret requerido si `require_secret=true` | `WebhookSignatureGateService` |
| RN-011-05 | Core no muta semántica payload retail | REQ-RST-01, REQ-RST-02 |
| RN-011-06 | Ingress no requiere platform API key | `WebhookIngressController` docblock |

---

## Precondiciones

1. Integración registrada y activa (PROC-012).
2. Secret webhook configurado en credenciales.
3. Middleware operativo (PROC-001).
4. Canal externo configurado con URL y secret correctos.

---

## Postcondiciones

1. Request auditado con latencia registrada.
2. Si éxito: evento publicado con `event_id` y `entry_id`.
3. Si fallo: audit failure; excepción propagada (422).
4. Dashboard puede observar evento (PROC-004).

---

## Flujo principal (paso a paso)

| Paso | Actividad | Descripción |
|------|-----------|-------------|
| 1 | Evento inicio | Canal externo `POST /api/integrations/webhooks/{code}` |
| 2 | **ACT-023** Recibir webhook | `WebhookIngressController::receive` |
| 3 | Resolver integración | `WebhookInboundIntegrationResolver::resolve` |
| 4 | Verificar firma | `WebhookSignatureGateService::assertValid` (HMAC) |
| 5 | Auditar received | `WebhookIngressAuditor::recordReceived` |
| 6 | Procesar ingress | `WebhookIngressProcessor::process` → envelope |
| 7 | Publicar bus | Delega pipeline PROC-001 (FLU-029 → ACT-002) |
| 8 | Auditar success | `recordSuccess` con `event_id`, `entry_id` |
| 9 | **Fin** | HTTP 202 JSON |

---

## Flujos alternativos

### FA-01 — Firma inválida

- **Condición:** HMAC no coincide con body + secret.
- **Acción:** Excepción antes de publish; HTTP 401.
- **Audit:** No success record (fallo temprano).

### FA-02 — Integración inactiva o código desconocido

- **Condición:** `findActiveByCode` null.
- **Acción:** Error resolución; HTTP 404/422.

### FA-03 — Fallo transformación/publish

- **Condición:** Excepción en `ingressProcessor`.
- **Acción:** `recordFailure` HTTP 422; no evento en bus.

### FA-04 — Idempotencia downstream

- **Condición:** Mismo evento reenviado por canal.
- **Acción:** PROC-001 idempotencia por `event_id`.

---

## Excepciones

| Escenario | Causa | Tratamiento |
|-----------|-------|-------------|
| EX-011-01 | Firma HMAC inválida | HTTP 401 |
| EX-011-02 | Secret ausente con require_secret | HTTP 401 |
| EX-011-03 | Integración no encontrada | HTTP 404 |
| EX-011-04 | Payload no transformable | HTTP 422 + audit failure |
| EX-011-05 | Throttle excedido | HTTP 429 |
| EX-011-06 | Fallo publish bus | HTTP 422; audit failure |

---

## Eventos

| Evento BPMN | Tipo | Descripción |
|-------------|------|-------------|
| Webhook POST | Evento inicio | Canal externo |
| Firma validada | Intermedio | HMAC OK |
| Evento publicado | Intermedio | PROC-001 completado |
| Fin ingress | Evento fin | HTTP 202 o error |

---

## Dependencias

| Dependencia | Tipo | Proceso |
|-------------|------|---------|
| PROC-012 | Previo | Config integración y secret |
| PROC-001 | Posterior | Publicación al bus |
| DEP-006 | Arquitectura | Integration → Middleware |
| Plan_Integraciones | Doc | §5 ingress |

---

## Riesgos

| ID | Riesgo | Mitigación |
|----|--------|------------|
| R1 | Secret comprometido | Rotación credenciales PROC-012 |
| R2 | Replay attacks | Timestamp/nonce — PENDIENTE_VALIDACION Plan |
| R3 | Implementación parcial | WebhookIngressTest; pmv PMV-004 |
| R4 | Payload malicioso | Validación estructural en transform |

---

## Indicadores

| Indicador | Fuente |
|-----------|--------|
| Webhooks received/success/failure | Tabla webhook audit |
| Latencia ingress ms | `ReceiveWebhookUseCase` latency |
| Criterios C09–C10 | `docs/evaluation/03_Matriz_Integracion.csv` |

---

## Relación con otros procesos

| Proceso | Relación |
|---------|----------|
| PROC-001 | Publicación envelope transformado |
| PROC-004 | Observación evento publicado |
| PROC-012 | CRUD integraciones y credenciales |
| PROC-006 | No aplica en ingress (HMAC) |
| PROC-017 | Etapa 1 ingesta documental (referencia) |

---

## Componentes involucrados

| Capa | Componente |
|------|------------|
| HTTP | `WebhookIngressController` |
| Aplicación | `ReceiveWebhookUseCase`, `WebhookIngressProcessor`, `WebhookSignatureGateService` |
| Dominio | `WebhookSignatureVerifier` |
| Infra | `EloquentIntegrationRepository`, webhook audit persistence |
| Rutas | `IntegrationApiRoutes` L19–20 |

---

## Documentación relacionada

- `docs/production/Plan_Integraciones.md` §5
- `docs/Diagrama_BPMN/21_Proceso_Gestion_Canales_Integraciones.md`
- `docs/Diagrama_BPMN/10_Proceso_Publicacion_Eventos_Bus.md`
- `tests/Feature/Integration/WebhookIngressTest.php`

---

## Trazabilidad

| Elemento | Evidencia |
|----------|-----------|
| PROC-011 | `docs/Patente/matriz_generada/procesos.csv` |
| ACT-023 | `docs/Patente/matriz_generada/actividades_bpmn.csv` |
| FLU-029 | `docs/Patente/matriz_generada/flujo_bpmn.csv` |
| REQ-INT-01, REQ-C1 | `docs/Patente/matriz_generada/requerimientos.csv` |
| DEP-006 | `docs/Patente/matriz_generada/dependencias.csv` |
| Use case | `app/Integration/Application/UseCases/ReceiveWebhookUseCase.php` |

---

## Diagrama Mermaid

```mermaid
flowchart TD
    START([POST /webhooks/{integrationCode}]) --> A23[ACT-023 ReceiveWebhookUseCase]
    A23 --> RES[Resolver integración activa]
    RES --> G1{¿Integración OK?}
    G1 -->|No| E404[HTTP 404]
    G1 -->|Sí| HMAC[Verificar HMAC-SHA256]
    HMAC --> G2{¿Firma válida?}
    G2 -->|No| E401[HTTP 401]
    G2 -->|Sí| AUD1[Auditar received]
    AUD1 --> PROC[WebhookIngressProcessor → publish]
    PROC --> G3{¿Publish OK?}
    G3 -->|Sí| AUD2[Auditar success 202]
    G3 -->|No| AUDF[Auditar failure 422]
    AUD2 --> END([Fin HTTP 202])
    AUDF --> E422[HTTP 422]
    E404 --> ENDE([Fin error])
    E401 --> ENDE
    E422 --> ENDE
```

---

## BPMN Mapping

| Elemento BPMN | Identificador / descripción |
|---------------|----------------------------|
| **Evento Inicio** | POST webhook canal externo |
| **Eventos Intermedios** | Firma validada; evento publicado en bus |
| **Evento Final** | HTTP 202; o 401/404/422 |
| **Actividades** | ACT-023 Recibir webhook externo |
| **Subprocesos** | Verificación HMAC; transformación envelope; publish PROC-001 |
| **Gateways** | GW-INT: integración activa; GW-SIG: firma válida; GW-PUB: publish OK |
| **Pools** | Pool Canal Externo; Pool Silo Integration |
| **Lanes** | Lane Ingress HTTP; Lane Signature; Lane Processor |
| **Mensajes** | Msg-Webhook-Request; Msg-Bus-Publish |
| **Objetos de datos** | Raw body; envelope JSON; webhook audit row |
| **Almacenes** | `integrations`; webhook requests audit |
| **Artefactos** | Plan_Integraciones.md; OpenAPI integrations |

---

*Fin del documento PROC-011*
