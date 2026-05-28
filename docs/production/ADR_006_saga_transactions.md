# ADR-006 — Sagas y compensación (transactions)

**Estado:** Propuesto | **Fecha:** 2026-05-21

## Contexto

`Plan_Resiliencia.md` Fase 3 contempla compensación (sagas) usando la tabla `transactions` del esquema middleware. No hay código de aplicación que la utilice.

## Decisión

**Diferir** implementación de sagas hasta:

1. Flujos multi-paso con rollback de negocio definidos (ej. orden + pago + inventario)
2. Integraciones externas con requisito de compensación explícito
3. Volumen que justifique orchestrator/choreography dedicado

## Alternativas evaluadas

| Opción | Cuándo |
|--------|--------|
| Retry + DLQ (actual) | Eventos at-least-once, listeners idempotentes |
| Outbox + saga orchestrator | Procesos multi-servicio con compensación |
| Choreography vía eventos | Dominios desacoplados con eventos de compensación |

## Consecuencias

- Tabla `transactions` permanece schema-only
- Resiliencia actual cubre publish, retry, DLQ, requeue, circuit breaker
- No bloquea piloto omnicanal con listeners idempotentes

## Referencias

- [Plan_Resiliencia.md](Plan_Resiliencia.md)
- `docs/architecture/middleware_database_dictionary.md`
