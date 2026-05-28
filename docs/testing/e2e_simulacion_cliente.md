# E2E — simulación tipo cliente / producción

## 1. Objetivo de la prueba
Ejecutar un **escenario multi-paso** que simula una instancia para un cliente: catálogo declarativo, sincronización, publicación de **varios tipos de evento** con payloads de dominio distintos, y comprobación de observabilidad (cola, topología, snapshot de dashboard).

## 2. Alcance
`tests/E2E/Middleware/ClientProductionLikeSimulationTest`. Complementa sin sustituir las regresiones Feature que ya cubren sync + flujo completo de un solo tipo.

## 3. Flujo probado
1. Definir `modules.catalog` y `eventbus.producers` / `eventbus.subscriptions` para dos tipos de evento y dos consumidores lógicos.
2. `POST sync-config`.
3. Validar catálogo expuesto vía API dashboard.
4. `POST publish` dos veces con payloads heterogéneos (`order_ref` vs `sku`/`delta`).
5. Verificar `PROCESADO` por `GET /api/middleware/events/{id}`, presencia en cola, topología y snapshot.

## 4. Datos de entrada
Tipos `Platform.E2E.Client.OrderPlaced` y `Platform.E2E.Client.StockAdjusted`; UUIDs nuevos por ejecución.

## 5. Resultado esperado
Coherencia extremo a extremo entre configuración declarada, ejecución del bus y vistas JSON de operación/dashboard; segunda sync sin romper consistencia.

## 6. Resultado obtenido (si aplica)
`php vendor/bin/phpunit --testsuite E2E`

## 7. Relación con el middleware (qué valida del sistema)
Valida **reutilización por cliente** y **listo para simulación productiva**: el mismo esqueleto soporta distintos contratos de evento y payloads sin codificar reglas de negocio en el middleware; el foco es enrutado, trazas y superficies de observación.
