# PROC-015 — Gestión incidentes soporte cliente

**ID:** PROC-015  
**Versión documento:** 1.0  
**Fecha:** 2026-06-27  
**Estado:** Implementado  
**Tipo:** Negocio — Apoyo / Administrativo  
**Macroproceso:** MP-01 Gestión Plataforma SaaS

---

## Descripción

Proceso de reporte de incidentes desde el portal instancia cliente y gestión/resolución desde el control plane SaaS. El operador tenant envía reportes vía `/support/reports`; el soporte SaaS gestiona en `/control/incidents`. Persistencia en tabla `client_incident_reports` con diagnósticos automáticos, severidad normalizada y flujo de estados open → acknowledged → resolved.

---

## Objetivo

Canalizar problemas operativos del cliente hacia soporte SaaS con contexto diagnóstico, trazabilidad tenant y ciclo de respuesta documentado, integrando portal cliente (PROC-019) y control plane (PROC-007).

---

## Alcance

**Incluye:**

- Creación reporte cliente: `POST /support/reports`.
- Consulta cliente: `GET /support/reports/{report}`.
- Bandeja CP: `/control/incidents`.
- Respuesta admin: `respondReport`, `updateReport`.
- `ClientIncidentReportService` — create, respond, find.
- Diagnósticos automáticos (`IncidentDiagnosticCollector`).
- Normalización severidad (`ClientIncidentReportSeverityNormalizer`).
- Resolución tenant instancia (`ClientIncidentReportTenantResolver`).
- Auto-creación incidente en fallo simulación (`SimulationRunFailureHandler`).

**Excluye:**

- Monitoreo automático alertas (PROC-013).
- Ticketing externo (Jira/ServiceNow) — no evidenciado.
- SLA contractual automatizado.

---

## Actores

| Actor | Rol |
|-------|-----|
| Operador tenant / cliente | Reporta incidente desde portal |
| Soporte SaaS / Admin CP | Gestiona y responde en `/control/incidents` |
| `ClientIncidentReportService` | Lógica dominio soporte |
| `IncidentsController` | UI/API control plane |
| `SupportReportWebController` | Creación desde portal |
| `SimulationRunFailureHandler` | Auto-reporte fallo simulación |

---

## Entradas

| Entrada | Origen |
|---------|--------|
| Formulario reporte | Descripción, severidad, subject, page_url |
| Contexto cliente | user_agent, IP, clientContext JSON |
| Usuario autenticado | PROC-005 sesión web |
| Tenant instancia | `ClientIncidentReportTenantResolver` |
| Acción admin | Mensaje respuesta, cambio estado |

---

## Salidas

| Salida | Descripción |
|--------|-------------|
| Fila `client_incident_reports` | Incidente registrado |
| `diagnostic_log` | Snapshot diagnóstico automático |
| Notificación cliente | Respuesta admin visible portal |
| Estado actualizado | open / acknowledged / resolved |
| Redirect/JSON | Confirmación operación |

---

## Reglas de negocio

| ID | Regla | Evidencia |
|----|-------|-----------|
| RN-015-01 | Reporte requiere usuario autenticado portal | `routes/web.php` |
| RN-015-02 | Severidad normalizada antes persistir | `ClientIncidentReportSeverityNormalizer` |
| RN-015-03 | Subject default si vacío | `ClientIncidentReportService::createFromClient` |
| RN-015-04 | Respuesta admin → status acknowledged si open | `respond()` |
| RN-015-05 | Tenant resuelto desde contexto instancia | ADR-001 instance_per_client |
| RN-015-06 | UUID id reporte | `Ramsey\Uuid` |

---

## Precondiciones

1. Operador cliente autenticado (PROC-005).
2. Portal instancia accesible (PROC-019).
3. Tabla `client_incident_reports` migrada.
4. Tenant context disponible (PROC-010).

---

## Postcondiciones

1. Incidente registrado con diagnóstico.
2. Soporte CP puede ver y responder.
3. Cliente recibe respuesta en portal.
4. Estado coherente con ciclo vida incidente.

---

## Flujo principal (paso a paso)

| Paso | Actividad | Descripción |
|------|-----------|-------------|
| 1 | Evento inicio | Cliente `POST /support/reports` |
| 2 | Autenticación | Sesión web operador tenant |
| 3 | Resolver tenant | `ClientIncidentReportTenantResolver` |
| 4 | Recolectar diagnóstico | `IncidentDiagnosticCollector::collect` |
| 5 | Crear reporte | `ClientIncidentReportService::createFromClient` |
| 6 | Persistir | `ClientIncidentReportModel` status open |
| 7 | Admin CP revisa | `GET /control/incidents` |
| 8 | Responder | `respondReport` — mensaje + acknowledged |
| 9 | Cliente lee | `GET /support/reports/{report}` |
| 10 | **Fin** | Incidente acknowledged/resolved |

---

## Flujos alternativos

### FA-01 — Auto-reporte fallo simulación

- **Condición:** `SimulationRunFailureHandler` detecta fallo PROC-020.
- **Acción:** Crea reporte si no existe duplicado reciente.

### FA-02 — Marcar leído cliente

- **Acción:** `SupportNotificationsWebController::markRead`.

### FA-03 — Escalado desde alerta

- **Condición:** PROC-013 alerta crítica.
- **Acción:** Ops crea reporte manual o correlaciona existente — PENDIENTE_VALIDACION automatización.

### FA-04 — Actualización estado admin

- **Acción:** `PATCH /control/incidents/reports/{report}` — resolved.

---

## Excepciones

| Escenario | Causa | Tratamiento |
|-----------|-------|-------------|
| EX-015-01 | Usuario no autenticado | Redirect login |
| EX-015-02 | Tenant no resuelto | Reporte con tenant null |
| EX-015-03 | Reporte no encontrado | HTTP 404 |

---

## Eventos

| Evento BPMN | Tipo | Descripción |
|-------------|------|-------------|
| POST support report | Evento inicio | Cliente reporta |
| Incidente creado | Intermedio | BD persistida |
| Respuesta admin | Intermedio | acknowledged |
| Fin gestión | Evento fin | resolved |

---

## Dependencias

| Dependencia | Tipo | Proceso |
|-------------|------|---------|
| PROC-005 | Previo | Auth web |
| PROC-019 | Previo | Portal acceso |
| PROC-010 | Contexto | Tenant instancia |
| PROC-013 | Origen opcional | Alertas |
| PROC-020 | Origen opcional | Fallo simulación |

---

## Riesgos

| ID | Riesgo | Mitigación |
|----|--------|------------|
| R1 | Diagnóstico insuficiente | Collector extensible |
| R2 | Sin integración ticketing externo | CP inbox nativo |
| R3 | Datos PII en diagnostic_log | Política retención PROC-014 |

---

## Indicadores

| Indicador | Fuente |
|-----------|--------|
| Reportes abiertos / resueltos | Tabla client_incident_reports |
| Tiempo respuesta | responded_at - created_at |
| C20 | `docs/evaluation/06_Matriz_Operacion.csv` |

---

## Relación con otros procesos

| Proceso | Relación |
|---------|----------|
| PROC-007 | Mismo bounded context Control |
| PROC-013 | Alertas pueden derivar incidentes |
| PROC-020 | Auto-reporte fallos simulación |

---

## Componentes involucrados

| Capa | Componente |
|------|------------|
| Aplicación | `ClientIncidentReportService`, `ControlIncidentsService`, `ClientSupportWebService` |
| HTTP | `IncidentsController`, `SupportReportWebController`, `SupportNotificationsWebController` |
| Infra | `ClientIncidentReportModel`, migration 2026_05_27 |
| Presenters | `ClientIncidentReportPresenter` |

---

## Documentación relacionada

- `database/migrations/2026_05_27_120000_create_client_incident_reports_table.php`
- `tests/Feature/Control/ClientSupportReportTest.php`
- `routes/control.php`, `routes/web.php`

---

## Trazabilidad

| Elemento | Evidencia |
|----------|-----------|
| PROC-015 | `docs/Patente/matriz_generada/procesos.csv` |
| Servicio | `app/Control/Application/Services/ClientIncidentReportService.php` |
| Rutas | `routes/web.php` L39–41; `routes/control.php` L51–54 |
| Migration | `database/migrations/2026_05_27_120000_create_client_incident_reports_table.php` |
| PMV-008 | Control plane incluye incidentes — `pmv.csv` |

---

## Diagrama Mermaid

```mermaid
flowchart TD
    START([POST /support/reports]) --> AUTH{¿Sesión portal?}
    AUTH -->|No| LOGIN[Redirect login]
    AUTH -->|Sí| CREATE[ClientIncidentReportService createFromClient]
    CREATE --> DIAG[IncidentDiagnosticCollector]
    DIAG --> DB[(client_incident_reports status=open)]
    DB --> CP[Admin /control/incidents]
    CP --> RESP[respondReport]
    RESP --> ACK[status acknowledged + admin_response]
    ACK --> CLIENT[Cliente GET /support/reports/{id}]
    CLIENT --> RES{¿Resuelto?}
    RES -->|Sí| END([Fin resolved])
    RES -->|No| CP
```

---

## BPMN Mapping

| Elemento BPMN | Identificador / descripción |
|---------------|----------------------------|
| **Evento Inicio** | POST /support/reports |
| **Eventos Intermedios** | Incidente creado; respuesta admin |
| **Evento Final** | Estado resolved |
| **Actividades** | Crear reporte; diagnosticar; responder; actualizar estado |
| **Gateways** | GW-AUTH: sesión válida; GW-STATUS: ciclo open→ack→resolved |
| **Pools** | Pool Cliente Portal; Pool Control Plane Soporte |
| **Lanes** | Lane Portal Web; Lane CP Incidents |
| **Objetos de datos** | ClientIncidentReport; diagnostic_log |
| **Almacenes** | Tabla client_incident_reports |

---

*Fin del documento PROC-015*
