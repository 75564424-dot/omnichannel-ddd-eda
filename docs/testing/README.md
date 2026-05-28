# Sistema de pruebas — omnichannel-ddd-eda (DDD + EDA + middleware)

Este directorio documenta la estrategia de validación automática del proyecto **omnichannel-ddd-eda**: capas `tests/Unit`, `tests/Integration`, `tests/Feature`, `tests/E2E`, y su relación con **Domain-Driven Design**, **Event-Driven Architecture** y el **middleware de integración** (bus observable, sin lógica de negocio de dominio en la publicación).

## Documentos estratégicos (QA / arquitectura)

| Documento | Contenido |
|-----------|-----------|
| [matrix_validacion_middleware.md](./matrix_validacion_middleware.md) | Criterios de arquitectura ↔ pruebas (desacoplamiento, sync, API, coherencia docs) |
| [audit_suite_redundancia.md](./audit_suite_redundancia.md) | Limpieza de docs duplicados, decisiones de alcance E2E vs Feature |
| [unit_configuracion_catalogo_declarativo.md](./unit_configuracion_catalogo_declarativo.md) | Enfoque Unit: catálogo JSON ↔ presentación |
| [feature_api_middleware_control.md](./feature_api_middleware_control.md) | Enfoque Feature: HTTP de control middleware |
| [integration_flujo_eventos_bus.md](./integration_flujo_eventos_bus.md) | Enfoque Integration: publicación y trazas |
| [e2e_simulacion_cliente.md](./e2e_simulacion_cliente.md) | Enfoque E2E: simulación tipo instancia cliente |

## Catálogos auto-generados (una ficha por método)

| Archivo | Carpeta de tests |
|---------|------------------|
| [unit_catalogo_autogenerado.md](./unit_catalogo_autogenerado.md) | `tests/Unit` |
| [integration_catalogo_autogenerado.md](./integration_catalogo_autogenerado.md) | `tests/Integration` |
| [feature_catalogo_autogenerado.md](./feature_catalogo_autogenerado.md) | `tests/Feature` |
| [e2e_catalogo_autogenerado.md](./e2e_catalogo_autogenerado.md) | `tests/E2E` |

### Regenerar catálogos

Tras añadir o renombrar clases de test:

```bash
php docs/testing/tools/generate_test_catalogs.php
```

Cada catálogo incluye un **preámbulo** con las siete secciones estándar (objetivo, alcance, flujo, datos, resultado esperado, resultado obtenido, relación con middleware). Las fichas por método detallan propósito y módulo.

Los métodos con **`#[DataProvider]`** pueden ejecutar más casos de los listados en Markdown.

### Autoload

Si se eliminan archivos de test versionados, ejecutar `composer dump-autoload` para limpiar el classmap.

## Ejecución

Desde la raíz del proyecto Laravel:

```bash
php vendor/bin/phpunit
```

Por suite:

```bash
php vendor/bin/phpunit --testsuite Unit
php vendor/bin/phpunit --testsuite Integration
php vendor/bin/phpunit --testsuite Feature
php vendor/bin/phpunit --testsuite E2E
```

## Entorno de pruebas relevante (`phpunit.xml`)

- `QUEUE_CONNECTION=sync`: listeners y jobs en el mismo proceso; determinismo en CI (la semántica de eventual consistency en producción se documenta en la matriz).
- `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`: base en memoria por ejecución.

## Resultado real (auto-sincronizado)

- **Fecha:** 2026-05-22  
- **Comando:** \`composer test\` / \`php vendor/bin/phpunit\`  
- **Resultado:** OK (160 tests, 517 assertions)

### Desglose por suite (métodos de test)

- **Unit:** 54 métodos `#[Test]`
- **Integration:** 20 métodos `#[Test]`
- **Feature:** 83 métodos `#[Test]`
- **E2E:** 2 métodos `#[Test]`

> Actualizado por \`php docs/testing/tools/sync_test_stats.php\` — ejecutar tras añadir tests o en CI (\`composer test:stats\`).


## Observaciones

- Las pruebas **no** añaden reglas de negocio al middleware: observan rutas HTTP, contratos de evento, persistencia de trazas y coherencia con el catálogo declarativo.
- Los tipos de evento bajo namespace `Platform.*` en tests son **ejemplos**; en despliegue real se alinean con `config/eventbus.php` y `config/modules/modules_config.json` del cliente.
