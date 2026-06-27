# Feature — Seguridad e Identidad

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [feature_seguridad_identidad.csv](./feature_seguridad_identidad.csv)  
**Fuente IDs:** [matriz_maestra_casos.csv](./matriz_maestra_casos.csv)

---

## 1. Objetivo

Documentar pruebas de **autenticación web** (operadores), **autorización por rol**, **autenticación API** (integradores M2M) y headers de seguridad HTTP.

## 2. Alcance BPMN

| Proceso | Documento BPMN | Enfoque |
|---------|----------------|---------|
| PROC-005 | [14_Proceso_Autenticacion_Operadores_Web.md](../Diagrama_BPMN/14_Proceso_Autenticacion_Operadores_Web.md) | Sesión web, login, portal |
| PROC-006 | [15_Proceso_Autenticacion_API_Integradores.md](../Diagrama_BPMN/15_Proceso_Autenticacion_API_Integradores.md) | API key, Sanctum, abilities |

Macroproceso: [04_Macroproceso_Seguridad_Acceso.md](../Diagrama_BPMN/04_Macroproceso_Seguridad_Acceso.md).

## 3. Carpetas de tests

| Capa | Ruta |
|------|------|
| Feature Identity | `tests/Feature/Identity/` |
| Feature Security | `tests/Feature/Security/` |
| Unit Security | `tests/Unit/Http/Security/` |

## 4. Clases y casos

### PlatformApiAuthenticationTest (PROC-006)

| ID | Método | Validación |
|----|--------|------------|
| TC-0133 | `middleware_status_returns_401_without_credentials` | 401 sin credenciales |
| TC-0134 | `middleware_status_accepts_static_api_key` | API key estática |
| TC-0135 | `publish_requires_events_publish_ability` | Ability `events:publish` |
| TC-0136 | `sync_config_writes_audit_log_when_enabled` | Audit log sync |
| TC-0137 | `sanctum_token_grants_access_with_abilities` | Token Sanctum |

### OperatorLoginTest (PROC-005)

| ID | Método | Resultado |
|----|--------|-----------|
| TC-0067 | `dashboard_redirects_guest_to_login` | PASÓ |
| TC-0068 | `operator_can_login_and_access_dashboard` | PASÓ |
| TC-0069 | `operator_of_another_tenant_can_login_when_multi_tenant_portal_enabled` | PASÓ |
| TC-0070 | `operator_of_another_tenant_is_rejected_when_multi_tenant_portal_disabled` | PASÓ |
| TC-0071 | `saas_admin_cannot_login_on_client_silo` | PASÓ |
| TC-0072 | `control_routes_return_not_found_on_client_silo` | PASÓ |
| TC-0073 | `platform_operator_seeder_creates_admin_user` | PASÓ |

**Corrección TC-0070 (2026-06-24):** `PlatformDatabaseReadiness` bloqueaba resolución de tenant con SQLite `:memory:`; corregido en `app/Shared/Platform/Support/PlatformDatabaseReadiness.php`. Incidencia INC-613e3b cerrada.

### RoleBasedAuthorizationTest (PROC-005)

| ID | Método | Rol validado |
|----|--------|--------------|
| TC-0076 | `dashboard_viewer_cannot_sync_registry` | viewer → 403 sync |
| TC-0077 | `dashboard_viewer_can_read_middleware_status` | viewer → lectura OK |
| TC-0078 | `bus_operator_can_sync_registry` | bus_operator → sync OK |
| TC-0079 | `saas_admin_can_access_control_user_management` | saas_admin → CP users |
| TC-0080 | `platform_admin_cannot_access_control_user_management` | platform_admin → 403 |
| TC-0081 | `bus_operator_cannot_access_control_companies` | bus_operator → 403 CP |
| TC-0082 | `sync_config_audit_includes_actor_label_with_role` | Audit con rol |

### OperatorSessionApiAccessTest

| ID | Método | Validación |
|----|--------|------------|
| TC-0074 | `authenticated_operator_session_can_call_middleware_api_without_bearer_token` | Sesión → API |
| TC-0075 | `authenticated_operator_can_access_dashboard_api_via_session` | Sesión → dashboard API |

### SecurityHeadersServicesTest (Unit, PROC-006)

IDs TC-0233–TC-0236 — CSP y headers configurables.

## 5. Resultado obtenido (2026-06-24)

| Métrica | Valor |
|---------|-------|
| Casos en CSV | 25 |
| PASÓ | 25 |
| FALLÓ | 0 |

## 6. Criterios de aceptación

- Integrador sin token → 401 Problem Details (PROC-006).
- Operador con rol insuficiente → 403 en rutas restringidas.
- Operador tenant A no accede a silo tenant B cuando portal multi-tenant disabled → verificado (TC-0070).

## 7. Ejecución

```bash
php vendor/bin/phpunit tests/Feature/Identity/OperatorLoginTest.php
php vendor/bin/phpunit tests/Feature/Security/PlatformApiAuthenticationTest.php
php vendor/bin/phpunit tests/Feature/Identity/RoleBasedAuthorizationTest.php
```

## 8. Trazabilidad

CU-SEC-01…CU-SEC-03 en [Matriz_Trazabilidad_Pruebas.csv](./Matriz_Trazabilidad_Pruebas.csv).  
Riesgo RSK-F01 cerrado en `instrumentos/Matriz_Riesgos_Testing.csv`.
