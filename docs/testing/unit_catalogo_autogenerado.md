# Catálogo — Unit

## 1. Objetivo de la prueba
Documentar y rastrear la suite **Unit** del proyecto: cada ficha siguiente describe un método de prueba y su lectura arquitectónica respecto al **middleware** (transporte/enrutado sin negocio de dominio).

## 2. Alcance
Capa **Unit**: reglas puras, VOs, normalización de configuración y catálogo sin I/O externa. No sustituye la lectura del código fuente de los asserts.

## 3. Flujo probado (capa)
Ejecución PHPUnit con entorno `phpunit.xml` (SQLite en memoria, cola `sync`). Arranque de aplicación Laravel cuando aplica.

## 4. Datos de entrada
Por método: ver implementación (fixtures, `config()->set`, cuerpos HTTP, UUIDs de evento).

## 5. Resultado esperado
Todos los tests de la capa en verde; contratos públicos estables ante refactors que preserven el middleware como integración desacoplada.

## 6. Resultado obtenido (si aplica)
Regenerar tras cambios: `php docs/testing/tools/generate_test_catalogs.php`. Ejecutar `php vendor/bin/phpunit --testsuite Unit`.

## 7. Relación con el middleware
Valida propagación/nombre de eventos, trazabilidad en cola, registro declarativo, o coherencia config ↔ ejecución ↔ vistas API según la capa — alineado a `docs/Modulos/Modulo_Control_Middleware.md` y planes de servicio.

---

Este archivo lista las pruebas de la capa **Unit** con la plantilla estándar del proyecto.

> Generado por `docs/testing/tools/generate_test_catalogs.php`. Regenerar tras añadir o renombrar tests.

---

# PurgePlatformRetentionTableValidatorTest::normalize_empty_string_to_null

## Objetivo
Validar el comportamiento descrito por el método `normalize_empty_string_to_null` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Ops/PurgePlatformRetentionTableValidatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PurgePlatformRetentionTableValidatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PurgePlatformRetentionTableValidatorTest::is_valid_accepts_allowed_tables_and_null

## Objetivo
Validar el comportamiento descrito por el método `is_valid_accepts_allowed_tables_and_null` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Ops/PurgePlatformRetentionTableValidatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PurgePlatformRetentionTableValidatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PurgePlatformRetentionConsoleReporterTest::report_invalid_table_returns_failure

## Objetivo
Validar el comportamiento descrito por el método `report_invalid_table_returns_failure` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Presenters/PurgePlatformRetentionConsoleReporterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PurgePlatformRetentionConsoleReporterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PurgePlatformRetentionConsoleReporterTest::report_purge_results_renders_skipped_and_summary

## Objetivo
Validar el comportamiento descrito por el método `report_purge_results_renders_skipped_and_summary` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Presenters/PurgePlatformRetentionConsoleReporterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PurgePlatformRetentionConsoleReporterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulateClientConsoleReporterTest::report_fixture_not_found_writes_error_and_returns_failure

## Objetivo
Validar el comportamiento descrito por el método `report_fixture_not_found_writes_error_and_returns_failure` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Presenters/SimulateClientConsoleReporterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulateClientConsoleReporterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulateClientConsoleReporterTest::report_simulation_result_returns_failure_on_validation_errors

## Objetivo
Validar el comportamiento descrito por el método `report_simulation_result_returns_failure_on_validation_errors` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Presenters/SimulateClientConsoleReporterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulateClientConsoleReporterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulateClientConsoleReporterTest::report_publish_plan_outputs_rate_summary

## Objetivo
Validar el comportamiento descrito por el método `report_publish_plan_outputs_rate_summary` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Presenters/SimulateClientConsoleReporterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulateClientConsoleReporterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulateClientCommandOptionsTest::publish_plan_uses_burst_mode_when_per_minute_is_absent

## Objetivo
Validar el comportamiento descrito por el método `publish_plan_uses_burst_mode_when_per_minute_is_absent` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Simulation/SimulateClientCommandOptionsTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulateClientCommandOptionsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulateClientCommandOptionsTest::publish_plan_computes_rate_and_duration

## Objetivo
Validar el comportamiento descrito por el método `publish_plan_computes_rate_and_duration` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Simulation/SimulateClientCommandOptionsTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulateClientCommandOptionsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulateClientOrchestratorTest::missing_fixture_slugs_returns_available_list_for_unknown_slug

## Objetivo
Validar el comportamiento descrito por el método `missing_fixture_slugs_returns_available_list_for_unknown_slug` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Simulation/SimulateClientOrchestratorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulateClientOrchestratorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulateClientOrchestratorTest::missing_fixture_slugs_is_null_for_known_fixture

## Objetivo
Validar el comportamiento descrito por el método `missing_fixture_slugs_is_null_for_known_fixture` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Simulation/SimulateClientOrchestratorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulateClientOrchestratorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulateClientOrchestratorTest::simulate_delegates_with_zero_events_without_sync_or_validate

## Objetivo
Validar el comportamiento descrito por el método `simulate_delegates_with_zero_events_without_sync_or_validate` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Console/Simulation/SimulateClientOrchestratorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulateClientOrchestratorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientDashboardMetricsCatalogServiceTest::catalog_empty_without_tenant_modules

## Objetivo
Validar el comportamiento descrito por el método `catalog_empty_without_tenant_modules` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/ClientDashboardMetricsCatalogServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientDashboardMetricsCatalogServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientDashboardMetricsCatalogServiceTest::catalog_and_series_when_modules_configured

## Objetivo
Validar el comportamiento descrito por el método `catalog_and_series_when_modules_configured` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/ClientDashboardMetricsCatalogServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientDashboardMetricsCatalogServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientDashboardModulesConfigurationTest::presentation_is_empty_until_dashboard_visibility_is_configured

## Objetivo
Validar el comportamiento descrito por el método `presentation_is_empty_until_dashboard_visibility_is_configured` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/ClientDashboardModulesConfigurationTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientDashboardModulesConfigurationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientDashboardModulesServiceTest::presentation_filters_by_visible_ids_and_exposes_available

## Objetivo
Validar el comportamiento descrito por el método `presentation_filters_by_visible_ids_and_exposes_available` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/ClientDashboardModulesServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientDashboardModulesServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientDashboardModulesServiceTest::update_visible_modules_persists_subset

## Objetivo
Validar el comportamiento descrito por el método `update_visible_modules_persists_subset` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/ClientDashboardModulesServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientDashboardModulesServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientInstancePortalServiceTest::branding_and_live_modules_follow_instance_tenant_catalog

## Objetivo
Validar el comportamiento descrito por el método `branding_and_live_modules_follow_instance_tenant_catalog` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/ClientInstancePortalServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientInstancePortalServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientInstancePortalServiceTest::producer_and_subscriber_node_keys_are_stable

## Objetivo
Validar el comportamiento descrito por el método `producer_and_subscriber_node_keys_are_stable` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/ClientInstancePortalServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientInstancePortalServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientIncidentReportPresenterTest::status_and_severity_labels_match_spanish_contract

## Objetivo
Validar el comportamiento descrito por el método `status_and_severity_labels_match_spanish_contract` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/Presenters/ClientIncidentReportPresenterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientIncidentReportPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientIncidentReportPresenterTest::to_client_inbox_preserves_response_and_unread_flags

## Objetivo
Validar el comportamiento descrito por el método `to_client_inbox_preserves_response_and_unread_flags` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/Presenters/ClientIncidentReportPresenterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientIncidentReportPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientIncidentReportPresenterTest::to_control_presentation_includes_diagnostic_summary

## Objetivo
Validar el comportamiento descrito por el método `to_control_presentation_includes_diagnostic_summary` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/Presenters/ClientIncidentReportPresenterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientIncidentReportPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ProvisionNewTenantResultPresenterTest::it_presents_success_message_when_fleet_provisioned

## Objetivo
Validar el comportamiento descrito por el método `it_presents_success_message_when_fleet_provisioned` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/Presenters/ProvisionNewTenantResultPresenterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ProvisionNewTenantResultPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ProvisionNewTenantResultPresenterTest::it_presents_deployment_guide_when_fleet_not_provisioned

## Objetivo
Validar el comportamiento descrito por el método `it_presents_deployment_guide_when_fleet_not_provisioned` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/Presenters/ProvisionNewTenantResultPresenterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ProvisionNewTenantResultPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationRunFailureHandlerTest::it_registers_incident_and_failure_report_on_control_plane

## Objetivo
Validar el comportamiento descrito por el método `it_registers_incident_and_failure_report_on_control_plane` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/SimulationRunFailureHandlerTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationRunFailureHandlerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationRunHandoffStoreTest::it_writes_and_reads_handoff_payload

## Objetivo
Validar el comportamiento descrito por el método `it_writes_and_reads_handoff_payload` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/SimulationRunHandoffStoreTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationRunHandoffStoreTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationRunHandoffSyncTest::it_syncs_progress_from_handoff_file_to_run_row

## Objetivo
Validar el comportamiento descrito por el método `it_syncs_progress_from_handoff_file_to_run_row` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/SimulationRunHandoffSyncTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationRunHandoffSyncTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationRunHandoffSyncTest::it_completes_run_from_handoff_terminal_state_without_http

## Objetivo
Validar el comportamiento descrito por el método `it_completes_run_from_handoff_terminal_state_without_http` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/SimulationRunHandoffSyncTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationRunHandoffSyncTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationRunWorkerMonitorTest::max_wall_clock_minutes_scales_with_planned_events

## Objetivo
Validar el comportamiento descrito por el método `max_wall_clock_minutes_scales_with_planned_events` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/SimulationRunWorkerMonitorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationRunWorkerMonitorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationRunWorkerMonitorTest::dispatched_handoff_without_worker_log_is_not_treated_as_alive

## Objetivo
Validar el comportamiento descrito por el método `dispatched_handoff_without_worker_log_is_not_treated_as_alive` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/SimulationRunWorkerMonitorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationRunWorkerMonitorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationRunWorkerMonitorTest::publishing_handoff_with_stale_progress_is_not_treated_as_alive

## Objetivo
Validar el comportamiento descrito por el método `publishing_handoff_with_stale_progress_is_not_treated_as_alive` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/SimulationRunWorkerMonitorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationRunWorkerMonitorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientIncidentReportSupportServicesTest::severity_normalizer_defaults_unknown_values_to_normal

## Objetivo
Validar el comportamiento descrito por el método `severity_normalizer_defaults_unknown_values_to_normal` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/Support/ClientIncidentReportSupportServicesTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientIncidentReportSupportServicesTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientIncidentReportSupportServicesTest::tenant_resolver_prefers_configured_client_slug

## Objetivo
Validar el comportamiento descrito por el método `tenant_resolver_prefers_configured_client_slug` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/Support/ClientIncidentReportSupportServicesTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientIncidentReportSupportServicesTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientIncidentReportSupportServicesTest::tenant_resolver_falls_back_to_first_tenant_when_slug_missing

## Objetivo
Validar el comportamiento descrito por el método `tenant_resolver_falls_back_to_first_tenant_when_slug_missing` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/Support/ClientIncidentReportSupportServicesTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientIncidentReportSupportServicesTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ProvisionNewTenantFleetFallbackHandlerTest::it_persists_pending_deployment_settings_when_fleet_is_unavailable

## Objetivo
Validar el comportamiento descrito por el método `it_persists_pending_deployment_settings_when_fleet_is_unavailable` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/Support/ProvisionNewTenantFleetFallbackHandlerTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ProvisionNewTenantFleetFallbackHandlerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ProvisionNewTenantInputMapperTest::it_filters_empty_profile_fields_and_ensures_middleware_module

## Objetivo
Validar el comportamiento descrito por el método `it_filters_empty_profile_fields_and_ensures_middleware_module` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/Support/ProvisionNewTenantInputMapperTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ProvisionNewTenantInputMapperTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ProvisionNewTenantInputMapperTest::it_keeps_middleware_when_already_present

## Objetivo
Validar el comportamiento descrito por el método `it_keeps_middleware_when_already_present` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/Support/ProvisionNewTenantInputMapperTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ProvisionNewTenantInputMapperTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TenantLifecyclePolicyTest::test_can_start_blocks_suspended_and_allows_expected_lifecycles

## Objetivo
Validar el comportamiento descrito por el método `test_can_start_blocks_suspended_and_allows_expected_lifecycles` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/TenantLifecyclePolicyTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TenantLifecyclePolicyTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TenantLifecyclePolicyTest::test_can_suspend_only_allows_active_status

## Objetivo
Validar el comportamiento descrito por el método `test_can_suspend_only_allows_active_status` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/TenantLifecyclePolicyTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TenantLifecyclePolicyTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TenantLifecyclePolicyTest::test_can_restore_only_allows_suspended_status

## Objetivo
Validar el comportamiento descrito por el método `test_can_restore_only_allows_suspended_status` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/TenantLifecyclePolicyTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TenantLifecyclePolicyTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TenantLifecyclePolicyTest::test_infer_lifecycle_handles_missing_or_legacy_deployment_settings

## Objetivo
Validar el comportamiento descrito por el método `test_infer_lifecycle_handles_missing_or_legacy_deployment_settings` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/TenantLifecyclePolicyTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TenantLifecyclePolicyTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TenantLifecyclePolicyTest::test_infer_lifecycle_prefers_explicit_lifecycle_field

## Objetivo
Validar el comportamiento descrito por el método `test_infer_lifecycle_prefers_explicit_lifecycle_field` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/TenantLifecyclePolicyTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TenantLifecyclePolicyTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TenantModuleCatalogServiceTest::it_loads_pruebas_retail_catalog_from_instance_files_when_settings_empty

## Objetivo
Validar el comportamiento descrito por el método `it_loads_pruebas_retail_catalog_from_instance_files_when_settings_empty` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Control/TenantModuleCatalogServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TenantModuleCatalogServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ConfigModulesCatalogPresentationTest::presentation_catalog_skips_invalid_rows_and_deduplicates_event_type_lists

## Objetivo
Validar el comportamiento descrito por el método `presentation_catalog_skips_invalid_rows_and_deduplicates_event_type_lists` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/ConfigModulesCatalogPresentationTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ConfigModulesCatalogPresentationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ConfigModulesCatalogPresentationTest::presentation_catalog_uses_default_contact_message_when_empty

## Objetivo
Validar el comportamiento descrito por el método `presentation_catalog_uses_default_contact_message_when_empty` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/ConfigModulesCatalogPresentationTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ConfigModulesCatalogPresentationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventFeedEntryLatencyTest::latency_ms_is_non_negative_difference_between_received_and_occurred

## Objetivo
Validar el comportamiento descrito por el método `latency_ms_is_non_negative_difference_between_received_and_occurred` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/EventFeedEntryLatencyTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventFeedEntryLatencyTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventImpactTest::uses_impact_hint_when_present

## Objetivo
Validar el comportamiento descrito por el método `uses_impact_hint_when_present` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/EventImpactTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventImpactTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventImpactTest::formats_numeric_delta_when_present

## Objetivo
Validar el comportamiento descrito por el método `formats_numeric_delta_when_present` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/EventImpactTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventImpactTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventImpactTest::falls_back_to_event_type

## Objetivo
Validar el comportamiento descrito por el método `falls_back_to_event_type` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/EventImpactTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventImpactTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventOriginTest::maps_channel_hints_to_labels

## Objetivo
Validar el comportamiento descrito por el método `maps_channel_hints_to_labels` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/EventOriginTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventOriginTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventOriginTest::prefers_explicit_origin_fields

## Objetivo
Validar el comportamiento descrito por el método `prefers_explicit_origin_fields` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/EventOriginTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventOriginTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventOriginTest::unknown_event_type_without_channel_maps_to_unknown_origin

## Objetivo
Validar el comportamiento descrito por el método `unknown_event_type_without_channel_maps_to_unknown_origin` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/EventOriginTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventOriginTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ModuleActivationGateServiceTest::simulation_blocked_when_middleware_inactive

## Objetivo
Validar el comportamiento descrito por el método `simulation_blocked_when_middleware_inactive` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/ModuleActivationGateServiceTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ModuleActivationGateServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ModuleActivationGateServiceTest::simulation_blocked_when_all_producers_inactive

## Objetivo
Validar el comportamiento descrito por el método `simulation_blocked_when_all_producers_inactive` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/ModuleActivationGateServiceTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ModuleActivationGateServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# NodeStatusTest::known_states_round_trip

## Objetivo
Validar el comportamiento descrito por el método `known_states_round_trip` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/NodeStatusTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `NodeStatusTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# NodeStatusTest::unknown_label_maps_to_offline

## Objetivo
Validar el comportamiento descrito por el método `unknown_label_maps_to_offline` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/NodeStatusTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `NodeStatusTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# DynamicMetricSeriesPresenterTest::bar_series_preserves_metric_contract_fields

## Objetivo
Validar el comportamiento descrito por el método `bar_series_preserves_metric_contract_fields` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/Presenters/DynamicMetricSeriesPresenterTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DynamicMetricSeriesPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# DynamicMetricSeriesPresenterTest::dual_origin_consumer_series_builds_two_panels

## Objetivo
Validar el comportamiento descrito por el método `dual_origin_consumer_series_builds_two_panels` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/Presenters/DynamicMetricSeriesPresenterTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DynamicMetricSeriesPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# DynamicMetricSeriesPresenterTest::empty_bar_series_includes_meta_reason

## Objetivo
Validar el comportamiento descrito por el método `empty_bar_series_includes_meta_reason` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/Presenters/DynamicMetricSeriesPresenterTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DynamicMetricSeriesPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# DynamicMetricSeriesBuilderTest::sum_by_day_delegates_to_event_feed_repository

## Objetivo
Validar el comportamiento descrito por el método `sum_by_day_delegates_to_event_feed_repository` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/Services/DynamicMetricSeriesBuilderTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DynamicMetricSeriesBuilderTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# DynamicMetricSeriesBuilderTest::dual_origin_consumer_uses_bus_queue_analytics

## Objetivo
Validar el comportamiento descrito por el método `dual_origin_consumer_uses_bus_queue_analytics` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/Services/DynamicMetricSeriesBuilderTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DynamicMetricSeriesBuilderTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# StreamStatusTest::from_metrics_maps_idle_to_stopped

## Objetivo
Validar el comportamiento descrito por el método `from_metrics_maps_idle_to_stopped` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/StreamStatusTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `StreamStatusTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# StreamStatusTest::from_metrics_high_volume_becomes_degraded

## Objetivo
Validar el comportamiento descrito por el método `from_metrics_high_volume_becomes_degraded` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/StreamStatusTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `StreamStatusTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# StreamStatusTest::from_metrics_normal_load_is_active

## Objetivo
Validar el comportamiento descrito por el método `from_metrics_normal_load_is_active` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/StreamStatusTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `StreamStatusTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# StreamStatusTest::invalid_raw_status_defaults_to_stopped

## Objetivo
Validar el comportamiento descrito por el método `invalid_raw_status_defaults_to_stopped` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Dashboard/StreamStatusTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `StreamStatusTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Dashboard**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PackSubscriptionCatalogMergerTest::merges_rows_and_deduplicates_module_listener_pairs

## Objetivo
Validar el comportamiento descrito por el método `merges_rows_and_deduplicates_module_listener_pairs` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/EventBus/PackSubscriptionCatalogMergerTest.php`, alineado al bounded context **EventBus** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
EventBus

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PackSubscriptionCatalogMergerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **EventBus**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PackSubscriptionCatalogMergerTest::skips_duplicate_from_second_registrar

## Objetivo
Validar el comportamiento descrito por el método `skips_duplicate_from_second_registrar` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/EventBus/PackSubscriptionCatalogMergerTest.php`, alineado al bounded context **EventBus** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
EventBus

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PackSubscriptionCatalogMergerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **EventBus**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PackSubscriptionCatalogMergerTest::skips_missing_class_and_bad_interface

## Objetivo
Validar el comportamiento descrito por el método `skips_missing_class_and_bad_interface` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/EventBus/PackSubscriptionCatalogMergerTest.php`, alineado al bounded context **EventBus** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
EventBus

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PackSubscriptionCatalogMergerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **EventBus**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PackSubscriptionCatalogMergerTest::skips_throw_and_malformed_catalog_without_wiping_base

## Objetivo
Validar el comportamiento descrito por el método `skips_throw_and_malformed_catalog_without_wiping_base` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/EventBus/PackSubscriptionCatalogMergerTest.php`, alineado al bounded context **EventBus** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
EventBus

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PackSubscriptionCatalogMergerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **EventBus**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TenantSuspendedResponsePresenterTest::api_request_returns_problem_details_payload

## Objetivo
Validar el comportamiento descrito por el método `api_request_returns_problem_details_payload` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Http/Presenters/TenantSuspendedResponsePresenterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TenantSuspendedResponsePresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SecurityHeadersServicesTest::csp_builder_returns_empty_when_not_configured

## Objetivo
Validar el comportamiento descrito por el método `csp_builder_returns_empty_when_not_configured` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Http/Security/SecurityHeadersServicesTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SecurityHeadersServicesTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SecurityHeadersServicesTest::csp_builder_expands_vite_origins_in_local_environment

## Objetivo
Validar el comportamiento descrito por el método `csp_builder_expands_vite_origins_in_local_environment` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Http/Security/SecurityHeadersServicesTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SecurityHeadersServicesTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SecurityHeadersServicesTest::csp_builder_keeps_production_csp_without_vite_origins

## Objetivo
Validar el comportamiento descrito por el método `csp_builder_keeps_production_csp_without_vite_origins` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Http/Security/SecurityHeadersServicesTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SecurityHeadersServicesTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SecurityHeadersServicesTest::applicator_applies_configured_headers_to_response

## Objetivo
Validar el comportamiento descrito por el método `applicator_applies_configured_headers_to_response` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Http/Security/SecurityHeadersServicesTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SecurityHeadersServicesTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# AdapterPipelineTest::json_validate_and_field_map_adapters_transform_payload

## Objetivo
Validar el comportamiento descrito por el método `json_validate_and_field_map_adapters_transform_payload` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/AdapterPipelineTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `AdapterPipelineTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# IntegrationHttpPresenterTest::list_envelope_preserves_count_and_data

## Objetivo
Validar el comportamiento descrito por el método `list_envelope_preserves_count_and_data` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/Presenters/IntegrationHttpPresenterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `IntegrationHttpPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# IntegrationHttpPresenterTest::created_and_updated_responses_match_admin_api_contract

## Objetivo
Validar el comportamiento descrito por el método `created_and_updated_responses_match_admin_api_contract` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/Presenters/IntegrationHttpPresenterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `IntegrationHttpPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# IntegrationHttpPresenterTest::not_found_returns_404_error_envelope

## Objetivo
Validar el comportamiento descrito por el método `not_found_returns_404_error_envelope` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/Presenters/IntegrationHttpPresenterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `IntegrationHttpPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# WebhookIngressHttpPresenterTest::accepted_response_matches_webhook_ingress_contract

## Objetivo
Validar el comportamiento descrito por el método `accepted_response_matches_webhook_ingress_contract` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/Presenters/WebhookIngressHttpPresenterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `WebhookIngressHttpPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# WebhookIngressHttpPresenterTest::error_response_preserves_status_code

## Objetivo
Validar el comportamiento descrito por el método `error_response_preserves_status_code` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/Presenters/WebhookIngressHttpPresenterTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `WebhookIngressHttpPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# WebhookIngressProcessorTest::process_publishes_built_envelope_and_returns_ids

## Objetivo
Validar el comportamiento descrito por el método `process_publishes_built_envelope_and_returns_ids` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/Services/WebhookIngressProcessorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `WebhookIngressProcessorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# IntegrationInputValidatorTest::validate_store_requires_code_name_and_direction

## Objetivo
Validar el comportamiento descrito por el método `validate_store_requires_code_name_and_direction` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/Support/IntegrationInputValidatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `IntegrationInputValidatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# IntegrationInputValidatorTest::validate_store_rejects_invalid_direction

## Objetivo
Validar el comportamiento descrito por el método `validate_store_rejects_invalid_direction` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/Support/IntegrationInputValidatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `IntegrationInputValidatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# WebhookEventEnvelopeBuilderTest::build_generates_event_id_when_missing

## Objetivo
Validar el comportamiento descrito por el método `build_generates_event_id_when_missing` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/WebhookEventEnvelopeBuilderTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `WebhookEventEnvelopeBuilderTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# WebhookEventEnvelopeBuilderTest::build_preserves_valid_event_id_from_payload

## Objetivo
Validar el comportamiento descrito por el método `build_preserves_valid_event_id_from_payload` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/WebhookEventEnvelopeBuilderTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `WebhookEventEnvelopeBuilderTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# WebhookEventEnvelopeBuilderTest::build_rejects_payload_without_event_type

## Objetivo
Validar el comportamiento descrito por el método `build_rejects_payload_without_event_type` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/WebhookEventEnvelopeBuilderTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `WebhookEventEnvelopeBuilderTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# WebhookSignatureVerifierTest::verify_accepts_valid_hmac_sha256

## Objetivo
Validar el comportamiento descrito por el método `verify_accepts_valid_hmac_sha256` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/WebhookSignatureVerifierTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `WebhookSignatureVerifierTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# WebhookSignatureVerifierTest::verify_rejects_invalid_signature

## Objetivo
Validar el comportamiento descrito por el método `verify_rejects_invalid_signature` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/WebhookSignatureVerifierTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `WebhookSignatureVerifierTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# WebhookSignatureVerifierTest::verify_rejects_missing_signature

## Objetivo
Validar el comportamiento descrito por el método `verify_rejects_missing_signature` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Integration/WebhookSignatureVerifierTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `WebhookSignatureVerifierTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PlatformStructuredLoggerTest::logger_hashes_payload_and_redacts_secrets

## Objetivo
Validar el comportamiento descrito por el método `logger_hashes_payload_and_redacts_secrets` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Logging/PlatformStructuredLoggerTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PlatformStructuredLoggerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# BusStatusEvaluateTest::idle_healthy_bus_is_active_not_stopped

## Objetivo
Validar el comportamiento descrito por el método `idle_healthy_bus_is_active_not_stopped` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/BusStatusEvaluateTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BusStatusEvaluateTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# BusStatusEvaluateTest::high_error_rate_is_degraded

## Objetivo
Validar el comportamiento descrito por el método `high_error_rate_is_degraded` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/BusStatusEvaluateTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BusStatusEvaluateTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# BusStatusEvaluateTest::high_throughput_is_hi_load

## Objetivo
Validar el comportamiento descrito por el método `high_throughput_is_hi_load` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/BusStatusEvaluateTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BusStatusEvaluateTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ConsumerListTest::trims_and_filters_empty_strings

## Objetivo
Validar el comportamiento descrito por el método `trims_and_filters_empty_strings` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/ConsumerListTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ConsumerListTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ConsumerListTest::empty_factory_has_no_consumers

## Objetivo
Validar el comportamiento descrito por el método `empty_factory_has_no_consumers` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/ConsumerListTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ConsumerListTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ConsumerListTest::contains_detects_module

## Objetivo
Validar el comportamiento descrito por el método `contains_detects_module` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/ConsumerListTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ConsumerListTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventIdTest::rejects_empty_identifier

## Objetivo
Validar el comportamiento descrito por el método `rejects_empty_identifier` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/EventIdTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventIdTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventIdTest::equals_matches_by_value

## Objetivo
Validar el comportamiento descrito por el método `equals_matches_by_value` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/EventIdTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventIdTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventStatusTest::factories_match_predicate_helpers

## Objetivo
Validar el comportamiento descrito por el método `factories_match_predicate_helpers` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/EventStatusTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventStatusTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventStatusTest::unknown_raw_defaults_to_pending

## Objetivo
Validar el comportamiento descrito por el método `unknown_raw_defaults_to_pending` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/EventStatusTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventStatusTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventTypeTest::rejects_empty_type

## Objetivo
Validar el comportamiento descrito por el método `rejects_empty_type` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/EventTypeTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventTypeTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventTypeTest::known_types_follow_merged_subscription_config

## Objetivo
Validar el comportamiento descrito por el método `known_types_follow_merged_subscription_config` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/EventTypeTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventTypeTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventTypeTest::arbitrary_string_can_exist_outside_catalog

## Objetivo
Validar el comportamiento descrito por el método `arbitrary_string_can_exist_outside_catalog` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/EventTypeTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventTypeTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LatencyMsAndThroughputAndErrorRateTest::latency_ms_guardrails

## Objetivo
Validar el comportamiento descrito por el método `latency_ms_guardrails` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/LatencyMsAndThroughputAndErrorRateTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LatencyMsAndThroughputAndErrorRateTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LatencyMsAndThroughputAndErrorRateTest::latency_acceptance_buckets

## Objetivo
Validar el comportamiento descrito por el método `latency_acceptance_buckets` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/LatencyMsAndThroughputAndErrorRateTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LatencyMsAndThroughputAndErrorRateTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LatencyMsAndThroughputAndErrorRateTest::throughput_eps_high_load

## Objetivo
Validar el comportamiento descrito por el método `throughput_eps_high_load` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/LatencyMsAndThroughputAndErrorRateTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LatencyMsAndThroughputAndErrorRateTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LatencyMsAndThroughputAndErrorRateTest::throughput_eps_idle_vs_high

## Objetivo
Validar el comportamiento descrito por el método `throughput_eps_idle_vs_high` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/LatencyMsAndThroughputAndErrorRateTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LatencyMsAndThroughputAndErrorRateTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LatencyMsAndThroughputAndErrorRateTest::error_rate_compute_handles_zero_total

## Objetivo
Validar el comportamiento descrito por el método `error_rate_compute_handles_zero_total` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/LatencyMsAndThroughputAndErrorRateTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LatencyMsAndThroughputAndErrorRateTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LatencyMsAndThroughputAndErrorRateTest::error_rate_health_buckets

## Objetivo
Validar el comportamiento descrito por el método `error_rate_health_buckets` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/LatencyMsAndThroughputAndErrorRateTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LatencyMsAndThroughputAndErrorRateTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# MiddlewareDomainTest::correlation_context_merges_body_and_headers

## Objetivo
Validar el comportamiento descrito por el método `correlation_context_merges_body_and_headers` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/MiddlewareDomainTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareDomainTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# MiddlewareDomainTest::schema_registry_resolves_config_entry

## Objetivo
Validar el comportamiento descrito por el método `schema_registry_resolves_config_entry` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/MiddlewareDomainTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareDomainTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# MiddlewareEventOriginInferTest::infers_from_standard_channel_hints

## Objetivo
Validar el comportamiento descrito por el método `infers_from_standard_channel_hints` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/MiddlewareEventOriginInferTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareEventOriginInferTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# MiddlewareEventOriginInferTest::infers_alias_for_unknown_uppercase_channel

## Objetivo
Validar el comportamiento descrito por el método `infers_alias_for_unknown_uppercase_channel` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/MiddlewareEventOriginInferTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareEventOriginInferTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# MiddlewareEventOriginInferTest::falls_back_to_unknown

## Objetivo
Validar el comportamiento descrito por el método `falls_back_to_unknown` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/MiddlewareEventOriginInferTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareEventOriginInferTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# DeadLetterHttpPresenterTest::list_envelope_preserves_count_and_data

## Objetivo
Validar el comportamiento descrito por el método `list_envelope_preserves_count_and_data` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/Presenters/DeadLetterHttpPresenterTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DeadLetterHttpPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# DeadLetterHttpPresenterTest::resolve_and_requeue_messages_match_api_contract

## Objetivo
Validar el comportamiento descrito por el método `resolve_and_requeue_messages_match_api_contract` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/Presenters/DeadLetterHttpPresenterTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DeadLetterHttpPresenterTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PublishEnvelopeValidatorTest::validate_structure_accepts_minimal_valid_envelope

## Objetivo
Validar el comportamiento descrito por el método `validate_structure_accepts_minimal_valid_envelope` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/PublishEnvelopeValidatorTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PublishEnvelopeValidatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PublishEnvelopeValidatorTest::validate_structure_rejects_non_array_payload

## Objetivo
Validar el comportamiento descrito por el método `validate_structure_rejects_non_array_payload` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/PublishEnvelopeValidatorTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PublishEnvelopeValidatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PublishPayloadSchemaValidatorTest::skips_validation_when_disabled

## Objetivo
Validar el comportamiento descrito por el método `skips_validation_when_disabled` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/PublishPayloadSchemaValidatorTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PublishPayloadSchemaValidatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PublishPayloadSchemaValidatorTest::validates_payload_against_configured_schema

## Objetivo
Validar el comportamiento descrito por el método `validates_payload_against_configured_schema` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/PublishPayloadSchemaValidatorTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PublishPayloadSchemaValidatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PublishPayloadSchemaValidatorTest::throws_when_schema_validation_fails

## Objetivo
Validar el comportamiento descrito por el método `throws_when_schema_validation_fails` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/PublishPayloadSchemaValidatorTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PublishPayloadSchemaValidatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# QueueEntryProcessingRulesTest::mark_processed_updates_timing_fields

## Objetivo
Validar el comportamiento descrito por el método `mark_processed_updates_timing_fields` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/QueueEntryProcessingRulesTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `QueueEntryProcessingRulesTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# QueueEntryProcessingRulesTest::mark_failed_increments_attempts

## Objetivo
Validar el comportamiento descrito por el método `mark_failed_increments_attempts` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/QueueEntryProcessingRulesTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `QueueEntryProcessingRulesTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# RetryPolicyTest::from_config_reads_max_attempts_and_backoff

## Objetivo
Validar el comportamiento descrito por el método `from_config_reads_max_attempts_and_backoff` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/RetryPolicyTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `RetryPolicyTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationPublishDeferTest::publish_leaves_pending_until_simulation_scope_ends

## Objetivo
Validar el comportamiento descrito por el método `publish_leaves_pending_until_simulation_scope_ends` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/SimulationPublishDeferTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationPublishDeferTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationPulseServiceTest::snapshot_clears_stale_pulse_and_reports_inactive

## Objetivo
Validar el comportamiento descrito por el método `snapshot_clears_stale_pulse_and_reports_inactive` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/SimulationPulseServiceTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationPulseServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationPulseServiceTest::snapshot_keeps_recent_pulse_active

## Objetivo
Validar el comportamiento descrito por el método `snapshot_keeps_recent_pulse_active` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/SimulationPulseServiceTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationPulseServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TopologyRegistryConfigMapperTest::map_producers_preserves_label_and_event_list

## Objetivo
Validar el comportamiento descrito por el método `map_producers_preserves_label_and_event_list` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/TopologyRegistryConfigMapperTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TopologyRegistryConfigMapperTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TopologyRegistryConfigMapperTest::map_consumers_groups_subscriptions_by_module

## Objetivo
Validar el comportamiento descrito por el método `map_consumers_groups_subscriptions_by_module` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/TopologyRegistryConfigMapperTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TopologyRegistryConfigMapperTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TopologySnapshotMergerTest::merge_producers_unions_events_and_prefers_observed_label

## Objetivo
Validar el comportamiento descrito por el método `merge_producers_unions_events_and_prefers_observed_label` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/TopologySnapshotMergerTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TopologySnapshotMergerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TopologySnapshotMergerTest::merge_consumers_unions_subscriptions_and_skips_empty_ids

## Objetivo
Validar el comportamiento descrito por el método `merge_consumers_unions_subscriptions_and_skips_empty_ids` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Middleware/TopologySnapshotMergerTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TopologySnapshotMergerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Middleware**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# BusMetricsAlertEvaluatorTest::evaluate_returns_no_alerts_when_metrics_are_healthy

## Objetivo
Validar el comportamiento descrito por el método `evaluate_returns_no_alerts_when_metrics_are_healthy` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/BusMetricsAlertEvaluatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BusMetricsAlertEvaluatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# BusMetricsAlertEvaluatorTest::evaluate_fires_high_error_rate_as_p1

## Objetivo
Validar el comportamiento descrito por el método `evaluate_fires_high_error_rate_as_p1` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/BusMetricsAlertEvaluatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BusMetricsAlertEvaluatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# BusMetricsAlertEvaluatorTest::evaluate_fires_latency_and_dlq_alerts_as_p2

## Objetivo
Validar el comportamiento descrito por el método `evaluate_fires_latency_and_dlq_alerts_as_p2` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/BusMetricsAlertEvaluatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BusMetricsAlertEvaluatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# BusStoppedAlertEvaluatorTest::evaluate_clears_cache_when_bus_is_not_stopped

## Objetivo
Validar el comportamiento descrito por el método `evaluate_clears_cache_when_bus_is_not_stopped` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/BusStoppedAlertEvaluatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BusStoppedAlertEvaluatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# BusStoppedAlertEvaluatorTest::evaluate_waits_before_firing_bus_stopped_alert

## Objetivo
Validar el comportamiento descrito por el método `evaluate_waits_before_firing_bus_stopped_alert` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/BusStoppedAlertEvaluatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BusStoppedAlertEvaluatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# CanaryProbeEnvelopeFactoryTest::build_produces_valid_canary_envelope

## Objetivo
Validar el comportamiento descrito por el método `build_produces_valid_canary_envelope` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/CanaryProbeEnvelopeFactoryTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `CanaryProbeEnvelopeFactoryTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# CanaryQueueCompletionVerifierTest::is_completed_returns_true_for_processed_status

## Objetivo
Validar el comportamiento descrito por el método `is_completed_returns_true_for_processed_status` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/CanaryQueueCompletionVerifierTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `CanaryQueueCompletionVerifierTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# CanaryQueueCompletionVerifierTest::is_completed_returns_false_for_pending_status

## Objetivo
Validar el comportamiento descrito por el método `is_completed_returns_false_for_pending_status` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/CanaryQueueCompletionVerifierTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `CanaryQueueCompletionVerifierTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# InfrastructureAlertEvaluatorTest::evaluate_fires_queue_backlog_when_jobs_exceed_threshold

## Objetivo
Validar el comportamiento descrito por el método `evaluate_fires_queue_backlog_when_jobs_exceed_threshold` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/InfrastructureAlertEvaluatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `InfrastructureAlertEvaluatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# InfrastructureAlertEvaluatorTest::evaluate_returns_empty_when_infrastructure_is_healthy

## Objetivo
Validar el comportamiento descrito por el método `evaluate_returns_empty_when_infrastructure_is_healthy` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/InfrastructureAlertEvaluatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `InfrastructureAlertEvaluatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# MonitoringAlertThresholdsTest::from_config_reads_platform_monitoring_alert_defaults

## Objetivo
Validar el comportamiento descrito por el método `from_config_reads_platform_monitoring_alert_defaults` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/MonitoringAlertThresholdsTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MonitoringAlertThresholdsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# MonitoringAlertThresholdsTest::defaults_apply_when_config_keys_are_missing

## Objetivo
Validar el comportamiento descrito por el método `defaults_apply_when_config_keys_are_missing` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Monitoring/MonitoringAlertThresholdsTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MonitoringAlertThresholdsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# FeedProjectionLagCalculatorTest::average_lag_ms_returns_zero_when_table_is_empty

## Objetivo
Validar el comportamiento descrito por el método `average_lag_ms_returns_zero_when_table_is_empty` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Observability/FeedProjectionLagCalculatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `FeedProjectionLagCalculatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# FeedProjectionLagCalculatorTest::average_lag_ms_computes_received_minus_occurred

## Objetivo
Validar el comportamiento descrito por el método `average_lag_ms_computes_received_minus_occurred` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Observability/FeedProjectionLagCalculatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `FeedProjectionLagCalculatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PrometheusGaugeCollectorTest::collect_returns_snapshot_with_non_negative_gauges

## Objetivo
Validar el comportamiento descrito por el método `collect_returns_snapshot_with_non_negative_gauges` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Observability/PrometheusGaugeCollectorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PrometheusGaugeCollectorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PrometheusTextRendererTest::render_outputs_all_prometheus_metric_lines

## Objetivo
Validar el comportamiento descrito por el método `render_outputs_all_prometheus_metric_lines` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Observability/PrometheusTextRendererTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PrometheusTextRendererTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PrometheusTextRendererTest::render_escapes_special_characters_in_client_label

## Objetivo
Validar el comportamiento descrito por el método `render_escapes_special_characters_in_client_label` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Observability/PrometheusTextRendererTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PrometheusTextRendererTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SliMetricsRecorderTest::record_persists_sli_metric_row

## Objetivo
Validar el comportamiento descrito por el método `record_persists_sli_metric_row` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Observability/SliMetricsRecorderTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SliMetricsRecorderTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# StreamConnectionTrackerTest::tracks_active_sse_connections

## Objetivo
Validar el comportamiento descrito por el método `tracks_active_sse_connections` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Observability/StreamConnectionTrackerTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `StreamConnectionTrackerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TraceContextTest::start_uses_correlation_id_as_trace_id_when_valid_uuid

## Objetivo
Validar el comportamiento descrito por el método `start_uses_correlation_id_as_trace_id_when_valid_uuid` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Observability/TraceContextTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TraceContextTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TraceContextTest::start_generates_trace_id_when_correlation_is_missing

## Objetivo
Validar el comportamiento descrito por el método `start_generates_trace_id_when_correlation_is_missing` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Observability/TraceContextTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TraceContextTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientSimulationDeadlineTest::resolve_publish_plan_caps_total_by_duration

## Objetivo
Validar el comportamiento descrito por el método `resolve_publish_plan_caps_total_by_duration` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/ClientSimulationDeadlineTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientSimulationDeadlineTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientSimulationFixedCountTest::resolve_publish_plan_fixes_total_for_rate_times_duration

## Objetivo
Validar el comportamiento descrito por el método `resolve_publish_plan_fixes_total_for_rate_times_duration` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/ClientSimulationFixedCountTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientSimulationFixedCountTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientSimulationPublishPlanTest::burst_mode_uses_events_count_only

## Objetivo
Validar el comportamiento descrito por el método `burst_mode_uses_events_count_only` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/ClientSimulationPublishPlanTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientSimulationPublishPlanTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientSimulationPublishPlanTest::per_minute_with_duration_computes_total_and_interval

## Objetivo
Validar el comportamiento descrito por el método `per_minute_with_duration_computes_total_and_interval` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/ClientSimulationPublishPlanTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientSimulationPublishPlanTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ClientSimulationPublishPlanTest::per_minute_without_duration_defaults_to_one_minute_of_events

## Objetivo
Validar el comportamiento descrito por el método `per_minute_without_duration_defaults_to_one_minute_of_events` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/ClientSimulationPublishPlanTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ClientSimulationPublishPlanTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# InstanceDeploymentServiceTest::tenant_not_bound_when_slug_differs_from_instance

## Objetivo
Validar el comportamiento descrito por el método `tenant_not_bound_when_slug_differs_from_instance` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/InstanceDeploymentServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `InstanceDeploymentServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# InstanceDeploymentServiceTest::cross_tenant_portal_allowed_when_multi_tenant_flag_enabled

## Objetivo
Validar el comportamiento descrito por el método `cross_tenant_portal_allowed_when_multi_tenant_flag_enabled` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/InstanceDeploymentServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `InstanceDeploymentServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# InstanceTenantContextTest::exposes_client_slug_and_name_from_config

## Objetivo
Validar el comportamiento descrito por el método `exposes_client_slug_and_name_from_config` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/InstanceTenantContextTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `InstanceTenantContextTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# InstanceTenantContextTest::log_context_includes_slug_and_deployment_mode

## Objetivo
Validar el comportamiento descrito por el método `log_context_includes_slug_and_deployment_mode` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/InstanceTenantContextTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `InstanceTenantContextTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LocalFleetRegistryTest::it_allocates_next_port_and_upserts_by_slug

## Objetivo
Validar el comportamiento descrito por el método `it_allocates_next_port_and_upserts_by_slug` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/LocalFleet/LocalFleetRegistryTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LocalFleetRegistryTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LocalInstanceEnvironmentLoaderTest::it_loads_database_path_from_instance_env_file

## Objetivo
Validar el comportamiento descrito por el método `it_loads_database_path_from_instance_env_file` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/LocalInstanceEnvironmentLoaderTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LocalInstanceEnvironmentLoaderTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ModulesConfigPathTest::resolves_relative_modules_config_path_from_env

## Objetivo
Validar el comportamiento descrito por el método `resolves_relative_modules_config_path_from_env` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/ModulesConfigPathTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ModulesConfigPathTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ModulesConfigPathTest::falls_back_to_default_when_env_missing

## Objetivo
Validar el comportamiento descrito por el método `falls_back_to_default_when_env_missing` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/ModulesConfigPathTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ModulesConfigPathTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PlatformDatabaseReadinessTest::can_query_schema_is_false_when_sqlite_file_missing

## Objetivo
Validar el comportamiento descrito por el método `can_query_schema_is_false_when_sqlite_file_missing` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/PlatformDatabaseReadinessTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PlatformDatabaseReadinessTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PlatformDatabaseReadinessTest::can_query_schema_is_true_when_sqlite_file_exists

## Objetivo
Validar el comportamiento descrito por el método `can_query_schema_is_true_when_sqlite_file_exists` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/PlatformDatabaseReadinessTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PlatformDatabaseReadinessTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SessionXsrfCookieConfigTest::xsrf_cookie_name_is_derived_from_session_cookie_for_local_fleet

## Objetivo
Validar el comportamiento descrito por el método `xsrf_cookie_name_is_derived_from_session_cookie_for_local_fleet` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/SessionXsrfCookieConfigTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SessionXsrfCookieConfigTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TenantCatalogSampleEventBuilderTest::builds_templates_from_producer_event_types

## Objetivo
Validar el comportamiento descrito por el método `builds_templates_from_producer_event_types` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/TenantCatalogSampleEventBuilderTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TenantCatalogSampleEventBuilderTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ValidatePlatformCatalogTest::passes_when_both_catalogs_are_empty

## Objetivo
Validar el comportamiento descrito por el método `passes_when_both_catalogs_are_empty` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/ValidatePlatformCatalogTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ValidatePlatformCatalogTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ValidatePlatformCatalogTest::fails_when_producer_declared_in_json_but_missing_in_eventbus

## Objetivo
Validar el comportamiento descrito por el método `fails_when_producer_declared_in_json_but_missing_in_eventbus` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/ValidatePlatformCatalogTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ValidatePlatformCatalogTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ValidatePlatformCatalogTest::fails_when_subscriber_declared_in_json_but_not_subscribed_in_eventbus

## Objetivo
Validar el comportamiento descrito por el método `fails_when_subscriber_declared_in_json_but_not_subscribed_in_eventbus` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/ValidatePlatformCatalogTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ValidatePlatformCatalogTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ValidatePlatformCatalogTest::passes_when_declarative_and_eventbus_are_aligned

## Objetivo
Validar el comportamiento descrito por el método `passes_when_declarative_and_eventbus_are_aligned` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Platform/ValidatePlatformCatalogTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ValidatePlatformCatalogTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ApplicationSupplementalRouteRegistrarTest::registers_health_ready_and_simulation_internal_routes

## Objetivo
Validar el comportamiento descrito por el método `registers_health_ready_and_simulation_internal_routes` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Providers/ApplicationSupplementalRouteRegistrarTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ApplicationSupplementalRouteRegistrarTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# BoundedContextProviderRegistrarTest::provider_classes_include_core_bcs

## Objetivo
Validar el comportamiento descrito por el método `provider_classes_include_core_bcs` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Providers/BoundedContextProviderRegistrarTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BoundedContextProviderRegistrarTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# EventBusPackSubscriptionBootstrapperTest::bootstrap_noops_when_no_consumer_registrars

## Objetivo
Validar el comportamiento descrito por el método `bootstrap_noops_when_no_consumer_registrars` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Providers/EventBusPackSubscriptionBootstrapperTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventBusPackSubscriptionBootstrapperTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# PlatformGateRegistrarTest::gates_allow_all_when_api_auth_is_disabled

## Objetivo
Validar el comportamiento descrito por el método `gates_allow_all_when_api_auth_is_disabled` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Providers/PlatformGateRegistrarTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PlatformGateRegistrarTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ProviderBootManifestTest::providers_lists_composition_root_in_boot_order

## Objetivo
Validar el comportamiento descrito por el método `providers_lists_composition_root_in_boot_order` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Providers/ProviderBootManifestTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ProviderBootManifestTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationServiceBindingsRegistrarTest::singleton_classes_cover_simulation_pipeline

## Objetivo
Validar el comportamiento descrito por el método `singleton_classes_cover_simulation_pipeline` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Providers/SimulationServiceBindingsRegistrarTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationServiceBindingsRegistrarTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationServiceBindingsRegistrarTest::register_resolves_key_simulation_services

## Objetivo
Validar el comportamiento descrito por el método `register_resolves_key_simulation_services` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Providers/SimulationServiceBindingsRegistrarTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationServiceBindingsRegistrarTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SqliteConcurrencyConfiguratorTest::configure_runs_without_exception_on_sqlite_memory

## Objetivo
Validar el comportamiento descrito por el método `configure_runs_without_exception_on_sqlite_memory` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Providers/SqliteConcurrencyConfiguratorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SqliteConcurrencyConfiguratorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ApplicationCoverageCalculatorTest::calculate_counts_only_application_layer_prefixes

## Objetivo
Validar el comportamiento descrito por el método `calculate_counts_only_application_layer_prefixes` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Quality/ApplicationCoverageCalculatorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ApplicationCoverageCalculatorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ApplicationCoverageGateServiceTest::evaluate_passes_when_coverage_meets_threshold

## Objetivo
Validar el comportamiento descrito por el método `evaluate_passes_when_coverage_meets_threshold` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Quality/ApplicationCoverageGateServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ApplicationCoverageGateServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# ApplicationCoverageGateServiceTest::evaluate_fails_when_coverage_below_threshold

## Objetivo
Validar el comportamiento descrito por el método `evaluate_fails_when_coverage_below_threshold` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Quality/ApplicationCoverageGateServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ApplicationCoverageGateServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# QualitySettingsTest::from_config_reads_platform_quality_defaults

## Objetivo
Validar el comportamiento descrito por el método `from_config_reads_platform_quality_defaults` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Quality/QualitySettingsTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `QualitySettingsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# QualitySettingsTest::defaults_apply_when_config_keys_are_missing

## Objetivo
Validar el comportamiento descrito por el método `defaults_apply_when_config_keys_are_missing` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Quality/QualitySettingsTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `QualitySettingsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LocalFleetAdminCredentialsResolverTest::resolve_prefers_explicit_admin_payload

## Objetivo
Validar el comportamiento descrito por el método `resolve_prefers_explicit_admin_payload` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Shared/Platform/LocalFleetAdminCredentialsResolverTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LocalFleetAdminCredentialsResolverTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LocalFleetAdminCredentialsResolverTest::resolve_uses_platform_admin_operator_when_present

## Objetivo
Validar el comportamiento descrito por el método `resolve_uses_platform_admin_operator_when_present` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Shared/Platform/LocalFleetAdminCredentialsResolverTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LocalFleetAdminCredentialsResolverTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LocalFleetAppKeyResolverTest::reuses_existing_app_key_from_env_file

## Objetivo
Validar el comportamiento descrito por el método `reuses_existing_app_key_from_env_file` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Shared/Platform/LocalFleetAppKeyResolverTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LocalFleetAppKeyResolverTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# LocalFleetAppKeyResolverTest::generates_base64_key_when_env_file_missing

## Objetivo
Validar el comportamiento descrito por el método `generates_base64_key_when_env_file_missing` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Shared/Platform/LocalFleetAppKeyResolverTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `LocalFleetAppKeyResolverTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TenantCatalogRuntimeConfiguratorTest::apply_merges_catalog_into_modules_and_eventbus_config

## Objetivo
Validar el comportamiento descrito por el método `apply_merges_catalog_into_modules_and_eventbus_config` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Shared/Platform/TenantCatalogRuntimeConfiguratorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TenantCatalogRuntimeConfiguratorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# TenantCatalogRuntimeConfiguratorTest::normalizer_skips_invalid_producer_rows

## Objetivo
Validar el comportamiento descrito por el método `normalizer_skips_invalid_producer_rows` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Shared/Platform/TenantCatalogRuntimeConfiguratorTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TenantCatalogRuntimeConfiguratorTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationRunHandoffPayloadMapperTest::it_builds_dispatch_payload_with_deadline_and_progress_defaults

## Objetivo
Validar el comportamiento descrito por el método `it_builds_dispatch_payload_with_deadline_and_progress_defaults` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Simulation/Handoff/SimulationRunHandoffPayloadMapperTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationRunHandoffPayloadMapperTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationRunHandoffPayloadMapperTest::it_applies_progress_percent_without_exceeding_one_hundred

## Objetivo
Validar el comportamiento descrito por el método `it_applies_progress_percent_without_exceeding_one_hundred` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Simulation/Handoff/SimulationRunHandoffPayloadMapperTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationRunHandoffPayloadMapperTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationRunReportSummaryMapperTest::it_maps_summary_with_human_duration_and_publish_rate

## Objetivo
Validar el comportamiento descrito por el método `it_maps_summary_with_human_duration_and_publish_rate` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Simulation/Metrics/SimulationRunReportSummaryMapperTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationRunReportSummaryMapperTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationTenantEligibilityCheckerTest::it_blocks_tenant_without_explicit_modules_catalog

## Objetivo
Validar el comportamiento descrito por el método `it_blocks_tenant_without_explicit_modules_catalog` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Simulation/SimulationTenantEligibilityCheckerTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationTenantEligibilityCheckerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationTenantEligibilityCheckerTest::it_blocks_tenant_with_only_middleware_catalog

## Objetivo
Validar el comportamiento descrito por el método `it_blocks_tenant_with_only_middleware_catalog` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Simulation/SimulationTenantEligibilityCheckerTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationTenantEligibilityCheckerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationTenantEligibilityCheckerTest::it_blocks_tenant_with_producer_without_event_types

## Objetivo
Validar el comportamiento descrito por el método `it_blocks_tenant_with_producer_without_event_types` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Simulation/SimulationTenantEligibilityCheckerTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationTenantEligibilityCheckerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationTenantEligibilityCheckerTest::it_allows_tenant_with_explicit_catalog_and_event_types

## Objetivo
Validar el comportamiento descrito por el método `it_allows_tenant_with_explicit_catalog_and_event_types` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Simulation/SimulationTenantEligibilityCheckerTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationTenantEligibilityCheckerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
---

# SimulationTenantEligibilityCheckerTest::it_does_not_treat_default_fixture_as_simulation_source

## Objetivo
Validar el comportamiento descrito por el método `it_does_not_treat_default_fixture_as_simulation_source` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Unit/Simulation/SimulationTenantEligibilityCheckerTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SimulationTenantEligibilityCheckerTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

## Datos de entrada
Ver implementación: construcción de fixtures (`RefreshDatabase`, facades, payloads de evento o cuerpos HTTP) dentro del método de prueba.

## Flujo de ejecución
1. Arrange: preparar datos, mocks o facades según el caso.
2. Act: invocar caso de uso, despachar evento o llamar endpoint HTTP.
3. Assert: verificar estado en BD, respuesta JSON o efectos en cola/outbox.

## Resultado esperado
Las aserciones del método deben pasar; el comportamiento debe mantenerse ante refactors que preserven contratos públicos.

## Validación arquitectónica
Capa **Unknown** en el módulo **Transversal**. Consultar **matrix_validacion_middleware.md** para idempotencia, desacoplamiento productor/consumidor y trazabilidad.

## Resultado real (opcional)
Ejecutar `php vendor/bin/phpunit` desde la raíz del proyecto (ver README en esta carpeta).

## Observaciones
Ficha base generada por `docs/testing/tools/generate_test_catalogs.php`; el detalle de asserts vive en el archivo PHP del test.
