# Feature — API de control del middleware

## 1. Objetivo de la prueba
Verificar los **endpoints HTTP** del módulo de control middleware: registro/sincronización de configuración, publicación de eventos, cola, topología y consulta de estado de evento, alineados al contrato operativo del sistema.

## 2. Alcance
Archivos bajo `tests/Feature/Middleware/` (`MiddlewareControlApiTest`, `MiddlewarePipelineEndToEndTest`). Incluye regresiones B.2 (sync + publicación + observabilidad).

## 3. Flujo probado
1. `POST /api/middleware/registry/sync-config` con `eventbus.*` y/o catálogo declarativo.
2. `POST /api/middleware/events/publish` con envelope de evento.
3. `GET` de cola, topología, evento por id, y rutas de dashboard cuando aplica.

## 4. Datos de entrada
JSON de request según implementación de cada test; UUIDs generados en runtime; `config()->set` para simular distintos clientes/instancias.

## 5. Resultado esperado
Códigos HTTP y cuerpos JSON según aserciones; persistencia de módulos registrados; evento procesado y visible en cola/API; idempotencia en sync repetido.

## 6. Resultado obtenido (si aplica)
`php vendor/bin/phpunit --testsuite Feature --filter Middleware`

## 7. Relación con el middleware (qué valida del sistema)
Confirma el **cumplimiento del rol de middleware**: exposición estable para integrar sistemas externos vía HTTP, sin lógica de negocio en la capa de transporte; propagación y trazabilidad observables por operación.
