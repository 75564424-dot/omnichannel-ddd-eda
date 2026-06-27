# Catálogo — Integration

## 1. Objetivo de la prueba
Documentar y rastrear la suite **Integration** del proyecto: cada ficha siguiente describe un método de prueba y su lectura arquitectónica respecto al **middleware** (transporte/enrutado sin negocio de dominio).

## 2. Alcance
Capa **Integration**: servicios de aplicación, bus, persistencia de trazas y límites entre capas. No sustituye la lectura del código fuente de los asserts.

## 3. Flujo probado (capa)
Ejecución PHPUnit con entorno `phpunit.xml` (SQLite en memoria, cola `sync`). Arranque de aplicación Laravel cuando aplica.

## 4. Datos de entrada
Por método: ver implementación (fixtures, `config()->set`, cuerpos HTTP, UUIDs de evento).

## 5. Resultado esperado
Todos los tests de la capa en verde; contratos públicos estables ante refactors que preserven el middleware como integración desacoplada.

## 6. Resultado obtenido (si aplica)
Regenerar tras cambios: `php docs/testing/tools/generate_test_catalogs.php`. Ejecutar `php vendor/bin/phpunit --testsuite Integration`.

## 7. Relación con el middleware
Valida propagación/nombre de eventos, trazabilidad en cola, registro declarativo, o coherencia config ↔ ejecución ↔ vistas API según la capa — alineado a `docs/Modulos/Modulo_Control_Middleware.md` y planes de servicio.

---

Este archivo lista las pruebas de la capa **Integration** con la plantilla estándar del proyecto.

> Generado por `docs/testing/tools/generate_test_catalogs.php`. Regenerar tras añadir o renombrar tests.

---

# DashboardFeedListenersDependencyBoundaryTest::listener_constructors_exclude_inappropriate_application_layers

## Objetivo
Validar el comportamiento descrito por el método `listener_constructors_exclude_inappropriate_application_layers` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Dashboard/DashboardFeedListenersDependencyBoundaryTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `DashboardFeedListenersDependencyBoundaryTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# GetGlobalMetricsUsesDashboardRepositoriesTest::constructor_uses_only_dashboard_read_repositories

## Objetivo
Validar el comportamiento descrito por el método `constructor_uses_only_dashboard_read_repositories` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Dashboard/GetGlobalMetricsUsesDashboardRepositoriesTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `GetGlobalMetricsUsesDashboardRepositoriesTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# PlatformPingObservedByDashboardIntegrationTest::platform_ping_is_recorded_in_event_feed

## Objetivo
Validar el comportamiento descrito por el método `platform_ping_is_recorded_in_event_feed` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Dashboard/PlatformPingObservedByDashboardIntegrationTest.php`, alineado al bounded context **Dashboard** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Dashboard

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `PlatformPingObservedByDashboardIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# EventAndAuditLogServiceTest::event_log_service_writes_received_and_processed_rows

## Objetivo
Validar el comportamiento descrito por el método `event_log_service_writes_received_and_processed_rows` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Logging/EventAndAuditLogServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventAndAuditLogServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# EventAndAuditLogServiceTest::audit_log_service_writes_audit_row

## Objetivo
Validar el comportamiento descrito por el método `audit_log_service_writes_audit_row` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Logging/EventAndAuditLogServiceTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventAndAuditLogServiceTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# BusTrackingDirectDispatchIntegrationTest::skips_recording_when_payload_has_no_event_id

## Objetivo
Validar el comportamiento descrito por el método `skips_recording_when_payload_has_no_event_id` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/BusTrackingDirectDispatchIntegrationTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BusTrackingDirectDispatchIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# BusTrackingDirectDispatchIntegrationTest::dispatches_create_single_queue_row_and_second_dispatch_is_idempotent

## Objetivo
Validar el comportamiento descrito por el método `dispatches_create_single_queue_row_and_second_dispatch_is_idempotent` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/BusTrackingDirectDispatchIntegrationTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BusTrackingDirectDispatchIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# BusTrackingListenerDependencyBoundaryTest::constructor_parameters_exclude_foreign_bounded_context_application_layers

## Objetivo
Validar el comportamiento descrito por el método `constructor_parameters_exclude_foreign_bounded_context_application_layers` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/BusTrackingListenerDependencyBoundaryTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `BusTrackingListenerDependencyBoundaryTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# EventPublisherServiceIntegrationTest::publish_inserts_pending_then_listener_marks_processed_after_sync_dispatch

## Objetivo
Validar el comportamiento descrito por el método `publish_inserts_pending_then_listener_marks_processed_after_sync_dispatch` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/EventPublisherServiceIntegrationTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventPublisherServiceIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# EventPublisherServiceIntegrationTest::second_publish_with_same_event_id_is_idempotent

## Objetivo
Validar el comportamiento descrito por el método `second_publish_with_same_event_id_is_idempotent` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/EventPublisherServiceIntegrationTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventPublisherServiceIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# EventStoreIdempotencyIntegrationTest::duplicate_publish_creates_single_event_store_row

## Objetivo
Validar el comportamiento descrito por el método `duplicate_publish_creates_single_event_store_row` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/EventStoreIdempotencyIntegrationTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `EventStoreIdempotencyIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# ModuleRegistryObservationIntegrationTest::dispatch_records_producer_and_topology_lists_connections_for_subscribed_consumer

## Objetivo
Validar el comportamiento descrito por el método `dispatch_records_producer_and_topology_lists_connections_for_subscribed_consumer` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/ModuleRegistryObservationIntegrationTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ModuleRegistryObservationIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# ModuleRegistryObservationIntegrationTest::topology_observed_sections_are_empty_without_traffic

## Objetivo
Validar el comportamiento descrito por el método `topology_observed_sections_are_empty_without_traffic` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/ModuleRegistryObservationIntegrationTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `ModuleRegistryObservationIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# OutboxRelayIntegrationTest::outbox_mode_relays_to_bus_and_marks_published

## Objetivo
Validar el comportamiento descrito por el método `outbox_mode_relays_to_bus_and_marks_published` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/OutboxRelayIntegrationTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `OutboxRelayIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# SubscriptionRegistryAndBusRegistrationIntegrationTest::core_catalog_is_empty_so_external_packs_define_types_and_consumers

## Objetivo
Validar el comportamiento descrito por el método `core_catalog_is_empty_so_external_packs_define_types_and_consumers` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/SubscriptionRegistryAndBusRegistrationIntegrationTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SubscriptionRegistryAndBusRegistrationIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# SubscriptionRegistryAndBusRegistrationIntegrationTest::wildcard_platform_listeners_are_registered

## Objetivo
Validar el comportamiento descrito por el método `wildcard_platform_listeners_are_registered` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/SubscriptionRegistryAndBusRegistrationIntegrationTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `SubscriptionRegistryAndBusRegistrationIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# WorkflowEngineIntegrationTest::active_workflow_creates_processing_job_on_publish

## Objetivo
Validar el comportamiento descrito por el método `active_workflow_creates_processing_job_on_publish` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Middleware/WorkflowEngineIntegrationTest.php`, alineado al bounded context **Middleware** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Middleware

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `WorkflowEngineIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# TraceLogsPipelineIntegrationTest::publish_track_and_project_write_trace_spans_and_feed_correlation

## Objetivo
Validar el comportamiento descrito por el método `publish_track_and_project_write_trace_spans_and_feed_correlation` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Observability/TraceLogsPipelineIntegrationTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TraceLogsPipelineIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# InstanceTenantSeedingIntegrationTest::seeder_creates_tenant_row_for_instance_slug

## Objetivo
Validar el comportamiento descrito por el método `seeder_creates_tenant_row_for_instance_slug` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Platform/InstanceTenantSeedingIntegrationTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `InstanceTenantSeedingIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# InstanceTenantSeedingIntegrationTest::message_queue_persists_tenant_id_after_seed

## Objetivo
Validar el comportamiento descrito por el método `message_queue_persists_tenant_id_after_seed` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Platform/InstanceTenantSeedingIntegrationTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `InstanceTenantSeedingIntegrationTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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

# TenantLifecycleIntegrationFlowTest::test_integration_flow_start_suspend_restore_affects_portal_and_api

## Objetivo
Validar el comportamiento descrito por el método `test_integration_flow_start_suspend_restore_affects_portal_and_api` en `C:/Proyectos/cursor/omnichannel-ddd-eda/tests/Integration/Platform/TenantLifecycleIntegrationFlowTest.php`, alineado al bounded context **Transversal** y a la capa **Unknown**.

## Tipo de prueba
Unknown

## Módulo
Transversal

## Estado
Existente

## Descripción
Ejecución PHPUnit sobre la clase `TenantLifecycleIntegrationFlowTest`. Las aserciones concretas están en el código fuente del test; esta ficha documenta el propósito y la lectura arquitectónica.

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
