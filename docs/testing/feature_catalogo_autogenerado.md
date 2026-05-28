# Catálogo — Feature

## 1. Objetivo de la prueba
Documentar y rastrear la suite **Feature** del proyecto: cada ficha siguiente describe un método de prueba y su lectura arquitectónica respecto al **middleware** (transporte/enrutado sin negocio de dominio).

## 2. Alcance
Capa **Feature**: contratos HTTP del control de middleware y rutas observable desde operación. No sustituye la lectura del código fuente de los asserts.

## 3. Flujo probado (capa)
Ejecución PHPUnit con entorno `phpunit.xml` (SQLite en memoria, cola `sync`). Arranque de aplicación Laravel cuando aplica.

## 4. Datos de entrada
Por método: ver implementación (fixtures, `config()->set`, cuerpos HTTP, UUIDs de evento).

## 5. Resultado esperado
Todos los tests de la capa en verde; contratos públicos estables ante refactors que preserven el middleware como integración desacoplada.

## 6. Resultado obtenido (si aplica)
Regenerar tras cambios: `php docs/testing/tools/generate_test_catalogs.php`. Ejecutar `php vendor/bin/phpunit --testsuite Feature`.

## 7. Relación con el middleware
Valida propagación/nombre de eventos, trazabilidad en cola, registro declarativo, o coherencia config ↔ ejecución ↔ vistas API según la capa — alineado a `docs/Modulos/Modulo_Control_Middleware.md` y planes de servicio.

---

Este archivo lista las pruebas de la capa **Feature** con la plantilla estándar del proyecto.

> Generado por `docs/testing/tools/generate_test_catalogs.php`. Regenerar tras añadir o renombrar tests.

---

# DashboardEndpointsTest::idle_dashboard_feed_reconciles_syncing_middleware_snapshot_online

## Objetivo
Validar el comportamiento descrito por el método `idle_dashboard_feed_reconciles_syncing_middleware_snapshot_online` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::recent_dashboard_feed_entries_skip_syncing_reconciliation

## Objetivo
Validar el comportamiento descrito por el método `recent_dashboard_feed_entries_skip_syncing_reconciliation` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::get_dashboard_metrics_returns_global_counters_shape

## Objetivo
Validar el comportamiento descrito por el método `get_dashboard_metrics_returns_global_counters_shape` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::get_dashboard_metrics_catalog_returns_metrics_and_event_envelope

## Objetivo
Validar el comportamiento descrito por el método `get_dashboard_metrics_catalog_returns_metrics_and_event_envelope` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::get_dashboard_metric_series_returns_chart_payload

## Objetivo
Validar el comportamiento descrito por el método `get_dashboard_metric_series_returns_chart_payload` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::get_dashboard_metric_series_returns_404_for_unknown_metric

## Objetivo
Validar el comportamiento descrito por el método `get_dashboard_metric_series_returns_404_for_unknown_metric` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::get_dashboard_modules_catalog_returns_normalized_topology_payload

## Objetivo
Validar el comportamiento descrito por el método `get_dashboard_modules_catalog_returns_normalized_topology_payload` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::get_dashboard_events_feed_returns_list_wrapper

## Objetivo
Validar el comportamiento descrito por el método `get_dashboard_events_feed_returns_list_wrapper` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::get_dashboard_snapshot_returns_aggregate_payload

## Objetivo
Validar el comportamiento descrito por el método `get_dashboard_snapshot_returns_aggregate_payload` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::get_dashboard_metrics_flow_returns_diagram_payload

## Objetivo
Validar el comportamiento descrito por el método `get_dashboard_metrics_flow_returns_diagram_payload` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::get_dashboard_daily_series_respects_days_cap

## Objetivo
Validar el comportamiento descrito por el método `get_dashboard_daily_series_respects_days_cap` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::get_dashboard_nodes_returns_nested_payload

## Objetivo
Validar el comportamiento descrito por el método `get_dashboard_nodes_returns_nested_payload` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::refresh_node_returns_updated_status_snapshot

## Objetivo
Validar el comportamiento descrito por el método `refresh_node_returns_updated_status_snapshot` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::patch_middleware_events_updates_flag_and_restores_default

## Objetivo
Validar el comportamiento descrito por el método `patch_middleware_events_updates_flag_and_restores_default` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::patch_middleware_events_validates_boolean

## Objetivo
Validar el comportamiento descrito por el método `patch_middleware_events_validates_boolean` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::patch_middleware_events_rejects_unknown_node

## Objetivo
Validar el comportamiento descrito por el método `patch_middleware_events_rejects_unknown_node` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# DashboardEndpointsTest::get_dashboard_nodes_and_bus_endpoints_respond

## Objetivo
Validar el comportamiento descrito por el método `get_dashboard_nodes_and_bus_endpoints_respond` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Dashboard/DashboardEndpointsTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardEndpointsTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewareControlApiTest::get_queue_returns_success_payload

## Objetivo
Validar el comportamiento descrito por el método `get_queue_returns_success_payload` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewareControlApiTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareControlApiTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewareControlApiTest::get_topology_includes_observed_registry_payload

## Objetivo
Validar el comportamiento descrito por el método `get_topology_includes_observed_registry_payload` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewareControlApiTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareControlApiTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewareControlApiTest::post_registry_sync_config_persists_catalog_modules

## Objetivo
Validar el comportamiento descrito por el método `post_registry_sync_config_persists_catalog_modules` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewareControlApiTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareControlApiTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewareControlApiTest::post_registry_sync_config_includes_declarative_modules_catalog_when_eventbus_empty

## Objetivo
Validar el comportamiento descrito por el método `post_registry_sync_config_includes_declarative_modules_catalog_when_eventbus_empty` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewareControlApiTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareControlApiTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewareControlApiTest::post_registry_sync_config_dedupes_identical_binding_from_eventbus_and_modules_catalog

## Objetivo
Validar el comportamiento descrito por el método `post_registry_sync_config_dedupes_identical_binding_from_eventbus_and_modules_catalog` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewareControlApiTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareControlApiTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewareControlApiTest::get_status_returns_bus_status_string

## Objetivo
Validar el comportamiento descrito por el método `get_status_returns_bus_status_string` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewareControlApiTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareControlApiTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewareControlApiTest::get_metrics_and_refresh_return_snapshots

## Objetivo
Validar el comportamiento descrito por el método `get_metrics_and_refresh_return_snapshots` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewareControlApiTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareControlApiTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewareControlApiTest::post_publish_validation_error_when_envelope_incomplete

## Objetivo
Validar el comportamiento descrito por el método `post_publish_validation_error_when_envelope_incomplete` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewareControlApiTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareControlApiTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewareControlApiTest::post_publish_then_get_event_by_id_returns_tracking_row

## Objetivo
Validar el comportamiento descrito por el método `post_publish_then_get_event_by_id_returns_tracking_row` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewareControlApiTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareControlApiTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewareControlApiTest::get_unknown_event_id_returns_404

## Objetivo
Validar el comportamiento descrito por el método `get_unknown_event_id_returns_404` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewareControlApiTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareControlApiTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewareControlApiTest::get_dead_letters_returns_list_envelope

## Objetivo
Validar el comportamiento descrito por el método `get_dead_letters_returns_list_envelope` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewareControlApiTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewareControlApiTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewarePipelineEndToEndTest::post_registry_sync_config_is_idempotent_for_persisted_module_rows

## Objetivo
Validar el comportamiento descrito por el método `post_registry_sync_config_is_idempotent_for_persisted_module_rows` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewarePipelineEndToEndTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewarePipelineEndToEndTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewarePipelineEndToEndTest::post_registry_sync_config_from_declarative_catalog_only_remains_stable_on_second_sync

## Objetivo
Validar el comportamiento descrito por el método `post_registry_sync_config_from_declarative_catalog_only_remains_stable_on_second_sync` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewarePipelineEndToEndTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewarePipelineEndToEndTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewarePipelineEndToEndTest::post_publish_invokes_subscribed_string_listener

## Objetivo
Validar el comportamiento descrito por el método `post_publish_invokes_subscribed_string_listener` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewarePipelineEndToEndTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewarePipelineEndToEndTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# MiddlewarePipelineEndToEndTest::full_flow_modules_config_sync_publish_exposed_in_queue_topology_and_dashboard_catalog

## Objetivo
Validar el comportamiento descrito por el método `full_flow_modules_config_sync_publish_exposed_in_queue_topology_and_dashboard_catalog` en `D:/DemoApp/omnichannel-ddd-eda/tests/Feature/Middleware/MiddlewarePipelineEndToEndTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `MiddlewarePipelineEndToEndTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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
