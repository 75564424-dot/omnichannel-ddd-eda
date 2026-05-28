# ADR-007 — Orquestación workflow enterprise

**Estado:** Propuesto | **Fecha:** 2026-05-21

## Contexto

`Plan_Middleware.md` Fase 3 contempla motor de workflows multi-paso. El esquema `workflows` / `workflow_steps` existe; la implementación actual solo dispara `processing_jobs` al recibir el evento configurado.

## Decisión

**Implementación mínima in-process** (trigger → `processing_jobs`) y **diferir** orchestrator completo hasta:

1. Flujos multi-paso con compensación definidos en negocio
2. Requisito de durabilidad cross-service (horas/días)
3. Evaluación Temporal vs Camunda vs choreography pura

## Alternativas

| Opción | Cuándo |
|--------|--------|
| Trigger + processing_jobs (actual) | Piloto, workflows simples |
| Temporal / Camunda | SLA enterprise, sagas largas |
| Kafka Streams / choreography | Alta escala, equipos autónomos |

## Consecuencias

- Tablas `workflow_steps` sin ejecutor de pasos aún
- `EVENTBUS_WORKFLOWS_ENABLED=false` por default
- No bloquea piloto con event_store + outbox + bus Laravel

## Referencias

- [Plan_Middleware.md](Plan_Middleware.md)
- [ADR_006_saga_transactions.md](ADR_006_saga_transactions.md)
