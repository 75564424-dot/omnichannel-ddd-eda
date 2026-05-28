# Unit — configuración y catálogo declarativo

## 1. Objetivo de la prueba
Garantizar que la capa de presentación del catálogo de módulos **normaliza** datos del JSON (`config/modules/modules_config.json` expuesto vía `config/modules.php`) de forma predecible, coherente con el dashboard y con el mensaje de contacto del proveedor.

## 2. Alcance
Clase `ConfigModulesCatalogDataProvider` y prueba `Tests\Unit\Dashboard\ConfigModulesCatalogPresentationTest`. Sin HTTP ni base de datos.

## 3. Flujo probado
1. Fijar `config('modules.catalog')` y `config('modules.service_contact_message')` en memoria.
2. Invocar `getPresentationCatalog()`.
3. Verificar omisión de filas inválidas, deduplicación de tipos de evento y valores por defecto de middleware.

## 4. Datos de entrada
Arreglos PHP en el test: productores/suscriptores con `id`/`name` vacíos, tipos repetidos, mensaje de contacto vacío o personalizado.

## 5. Resultado esperado
Solo filas válidas en `producers`/`subscribers`; listas de tipos sin duplicados ni strings vacíos; defaults de middleware y mensaje acorde a documentación (`modules.php`).

## 6. Resultado obtenido (si aplica)
Ejecutar: `php vendor/bin/phpunit --testsuite Unit --filter ConfigModulesCatalogPresentationTest`.

## 7. Relación con el middleware (qué valida del sistema)
Valida **consistencia configuración ↔ visualización**: el middleware y el bus pueden reconfigurarse por cliente mientras el dashboard refleja fielmente el catálogo declarativo, sin acoplarse a un dominio fijo.
