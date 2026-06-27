# Instrumento — Capacidades middleware C1–C5 vs pruebas

**Versión:** 1.0  
**Fecha:** 2026-06-27  
**Fuente requisitos:** [docs/Patente/matriz_generada/requerimientos.csv](../../Patente/matriz_generada/requerimientos.csv) (REQ-C1–C5)  
**Matriz evaluation:** [docs/evaluation/02_Matriz_Middleware.csv](../../evaluation/02_Matriz_Middleware.csv) (C05–C08, C28)

## 1. Propósito

Medir cobertura de prueba de las **cinco capacidades** del módulo Control Middleware frente a la suite automatizada y comandos operativos.

## 2. Resumen por capacidad

| Capacidad | Requisito | Estado impl. | Tests directos | Brecha |
|-----------|-----------|--------------|----------------|--------|
| **C1** Recepción eventos | REQ-C1 | Implementado | ~35 métodos | Webhook + HTTP + facade |
| **C2** Tracking operativo | REQ-C2 | Implementado | ~28 métodos | tenant_id seed (1 fallo) |
| **C3** Consultas bus | REQ-C3 | Implementado | ~22 métodos | — |
| **C4** Registry declarativo | REQ-C4 | Parcial | ~18 métodos | Divergencia eventbus vs JSON |
| **C5** Observación wildcard | REQ-C5 | Implementado | ~15 métodos | SSE UI pendiente |

## 3. Restricciones arquitectónicas (RST)

| Restricción | Test ancla |
|-------------|------------|
| REQ-RST-01 Sin negocio en core | `ClientProductionLikeSimulationTest`, `MiddlewareDomainTest` |
| REQ-RST-02 No mutar payload | `EventPublisherServiceIntegrationTest` |

## 4. Criterios evaluation middleware (C05–C28)

Los criterios C05–C08 y C28 de `02_Matriz_Middleware.csv` se alimentan de esta instrumentación en revisiones de aceptación (A02).

## 5. CSV

[Instrumentos_Middleware.csv](./Instrumentos_Middleware.csv)

## 6. Ejecución focalizada

```bash
php vendor/bin/phpunit tests/Feature/Middleware tests/Integration/Middleware tests/E2E/Middleware
php vendor/bin/phpunit tests/Unit/Middleware
```
