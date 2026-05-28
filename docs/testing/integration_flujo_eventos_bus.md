# Integration — flujo productor → bus → trazas

## 1. Objetivo de la prueba
Validar el **camino interno** desde la publicación de un evento hasta el estado persistido en la cola del bus (y listeners de observación), con límites claros entre aplicación e infraestructura.

## 2. Alcance
Tests en `tests/Integration/Middleware/` y escenarios relacionados en `tests/Integration/Dashboard/` que observan efectos del bus en métricas o feed.

## 3. Flujo probado
1. Resolver `EventPublisherService` (u otra fachada de aplicación).
2. Publicar envelope válido.
3. Comprobar filas en `bus_queue_entries` (estados, tiempos) y reglas de idempotencia (`event_id` único).
4. Donde aplica: dispatch y listeners de tracking o dashboard sin acoplar dominios concretos.

## 4. Datos de entrada
Payloads de prueba `Platform.*`; base SQLite en memoria; sin colas externas (`QUEUE_CONNECTION=sync`).

## 5. Resultado esperado
Transición a estado procesado cuando el bus y los listeners de infraestructura completan el ciclo; errores esperados en duplicados (constraint).

## 6. Resultado obtenido (si aplica)
`php vendor/bin/phpunit --testsuite Integration`

## 7. Relación con el middleware (qué valida del sistema)
Demuestra **desacoplamiento**: el contrato es el tipo y el sobre del evento; el middleware enruta y registra, permitiendo que productores y consumidores evolucionen por separado respetando idempotencia y trazabilidad.
