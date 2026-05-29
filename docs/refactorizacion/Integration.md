# Auditoría — Integration (Conectores / Webhooks)

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Integration/` |
| **Namespace** | `App\Integration\` |
| **Tipo** | Bounded Context |
| **Archivos PHP** | 48 |
| **LOC aprox.** | 1 481 |
| **Controllers** | 5 delgados en `Interfaces/Http/Controllers/` |
| **Tests** | 11 (Unit 7 · Feature 4) |

> **Última refactorización:** 2026-05-28 — pipeline webhook dividido, port explícito al bus, controllers por recurso.

## ¿Qué hace?

Gestiona **integraciones externas**: canales, credenciales, webhooks entrantes, conectores HTTP salientes y pipeline de adaptadores. Permite registrar integraciones por tenant e ingerir eventos externos hacia el bus.

## ¿Para qué sirve?

- APIs admin de integraciones y canales.
- Endpoint de ingress webhook con verificación de firma.
- Dispatch outbound hacia sistemas terceros vía `HttpOutboundConnector`.
- Extensión del bus hacia el mundo exterior sin acoplar Middleware a HTTP externo.

## Estructura DDD (post-refactor)

```text
app/Integration/
├── Domain/
│   ├── Contracts/           ExternalEventPublisher, OutboundConnector, Adapter
│   ├── Repositories/        interfaces persistencia
│   └── Services/            WebhookSignatureVerifier
├── Application/
│   ├── Services/            pipeline webhook + adapters + auditoría
│   └── UseCases/            CRUD + ReceiveWebhook (orquestador)
├── Infrastructure/
│   ├── Middleware/          BusExternalEventPublisher (ACL → EventPublisher)
│   ├── Adapters/            JsonValidate, FieldMap, Registry
│   ├── Connectors/          HttpOutbound
│   └── Persistence/         repos Eloquent
└── Interfaces/Http/Controllers/
    ├── IntegrationController           CRUD integrations
    ├── IntegrationCredentialController credenciales
    ├── IntegrationOutboundController   dispatch outbound
    ├── ChannelController               CRUD channels
    └── WebhookIngressController        ingress público
```

| Capa | Archivos | Estado |
|------|----------|--------|
| Domain | 10 | ✅ Contratos + verificador firma + port bus |
| Application | 22 | ✅ Use cases + servicios webhook pipeline |
| Infrastructure | 11 | ✅ ACL Middleware + repos |
| Interfaces | 6 | ✅ Controllers por aggregate |

## Servicios extraídos en esta refactorización

| Servicio | Reemplaza lógica en |
|----------|---------------------|
| `WebhookInboundIntegrationResolver` | Lookup + validación inbound en `ReceiveWebhookUseCase` |
| `WebhookSignatureGateService` | Verificación HMAC + secret config |
| `WebhookEventEnvelopeBuilder` | `buildEnvelope()` privado |
| `WebhookIngressAuditor` | record received / success / failure |
| `WebhookRequestHeadersNormalizer` | Normalización headers en controller |
| `BusExternalEventPublisher` | Dependencia directa a `EventPublisherService` |
| `ExternalEventPublisherInterface` | Port documentado Integration → Middleware |

## Métricas de deuda (actualizadas)

| Indicador | Antes | **Ahora** | Detalle |
|-----------|-------|-----------|---------|
| **% código sucio** | 26% | **12%** | Use case webhook ~85 LOC; sin clases >150 LOC |
| **% código espagueti** | 18% | **9%** | Pipeline verify → adapt → publish con dueños claros |
| **Ratio tests/archivos** | ~46% | **~23%** | +6 unit tests (firma + envelope) |
| **Archivos >150 LOC** | 1 | **0** | Mayor: `IntegrationController` ~90 LOC |

### Archivos más pesados (post-refactor)

| Archivo | LOC | Notas |
|---------|-----|-------|
| `IntegrationController.php` | ~90 | Solo CRUD integrations |
| `ChannelController.php` | ~88 | CRUD channels |
| `ReceiveWebhookUseCase.php` | ~85 | Orquestación pura |
| `WebhookEventEnvelopeBuilder.php` | ~55 | Mapping payload → bus envelope |

## Resuelto en esta refactorización

1. ~~`ReceiveWebhookUseCase` monolítico~~ → pipeline en 5 servicios + use case delgado.
2. ~~`IntegrationController` denso~~ → split credentials + outbound.
3. ~~Publicación al bus sin contrato~~ → `ExternalEventPublisherInterface` + `BusExternalEventPublisher`.
4. ~~Tests unitarios escasos~~ → `WebhookSignatureVerifierTest`, `WebhookEventEnvelopeBuilderTest`.

## Cosas sueltas / inconsistentes (restantes)

1. **`AdapterRegistry`** — pocos adaptadores reales (JsonValidate, FieldMap); extensible pero mayormente config-driven.
2. **`Gate::authorize` duplicado** — routes ya usan `platform.ability:integrations:admin`; defense in depth en controllers.
3. **Idempotencia concurrente** — sin test dedicado de carrera en webhook ingress.

## Acoplamientos

| Hacia | Tipo | Riesgo |
|-------|------|--------|
| Shared/Identity | Gate, policies | ✅ Bajo |
| Middleware | `ExternalEventPublisherInterface` (ACL) | ✅ Bajo |
| Dashboard / Control | No directo | ✅ Bajo |

## Cobertura de tests

- **Verificado (2026-05-28):** 11 tests Unit + Feature Integration — todos pasan.
- **Presente:** webhook ingress, admin API CRUD, outbound connector, adapter pipeline, firma HMAC, envelope builder.
- **Gaps:** credential rotation, idempotencia concurrente, contract tests OpenAPI completos.

## Recomendaciones de refactor (futuro)

| Prioridad | Acción |
|-----------|--------|
| P3 | Form Request classes para admin API. |
| P4 | Tests de idempotencia webhook bajo carga. |
| P4 | Inventario/adaptadores adicionales según integraciones reales del producto. |

## Veredicto

**BC sano** tras refactor: ingress webhook con pipeline claro, port explícito al bus y controllers alineados con aggregates. Deuda restante menor (tests edge-case, Form Requests).
