**Documento 4**  
**LISTA DE AUTORES Y ROLES — VERSIÓN COMPLETADA**

> **Fuente de generación:** historial Git y estructura del repositorio (2026-06-10).  
> **Plantilla base:** `Documento 4 - Lista de autores y roles.docx.md` (intacta).

---

## 1. Encabezado Formal

**LISTA DE AUTORES Y ROLES DEL SOFTWARE**

---

## 2. Identificación del Software

El presente documento corresponde al software denominado **Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta)**, paquete técnico `platform/event-bus-core`, desarrollado en el repositorio `omnichannel-ddd-eda`.

| Campo | Valor | Evidencia | Estado |
|-------|-------|-----------|--------|
| Empresa o titular | [COMPLETAR_MANUALMENTE] | No evidenciado en repositorio | **PENDIENTE DE VALIDACIÓN** |
| Periodo desarrollo Git | 2026-05-27 a 2026-06-18 | `git log --all` | **VALIDADO** (Fuente: `git log`) |
| Total commits | 37 | `git rev-list --all --count` | **VALIDADO** (Fuente: `git log`) |
| Commits humanos (aprox.) | 25 | Excluyendo dependabot y merges | **VALIDADO** (Fuente: `git log`) |

---

## 3. Tabla de Autores y Roles

### 3.1 Autores identificados en Git (evidencia directa)

| Nombre completo (Git) | DNI | Rol en el proyecto | Módulo / Componente | Periodo participación | Descripción de aportes (evidencia commit) | Estado |
| --------------------- | --- | ------------------ | ------------------- | --------------------- | ------------------------------------------- |--------|
| Brayan Estif Guillen Sanabria | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | 2026-05-27 – 2026-06-18 | 16 commits: commit inicial beta; formalización versiones; Fase 6 certificación UI; v1.7 rutas/tenants; refactorización; merges; notificaciones tiempo real (`git log`) | **VALIDADO** |
| Guillen-Sanabria | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | 2026-05-28 – 2026-06-16 | 9 commits: fase aislamiento instancias; simulación empresas; refactorización código; corrección bugs; eventos en panel cliente (`git log`) | **VALIDADO** |

**Correo compartido:** `75564424@continental.edu.pe` — **PENDIENTE DE VALIDACIÓN** si son la misma persona.

### 3.2 Tabla preparada para coautores adicionales (sin evidencia Git)

| Nombre completo | DNI | Rol en el proyecto | Módulo / Componente | Periodo | Descripción de aportes |
| --------------- | --- | ------------------ | ------------------- | ------- | -------------------- |
| [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] |
| [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] |

### 3.3 Mapeo sugerido módulo ↔ bounded context (para asignación manual de roles)

| Módulo / componente | Ruta código | Documentación | Estado |
| ------------------- | ----------- | ------------- |--------|
| Middleware Event Bus | `app/Middleware/` | `docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Control_Middleware.md` | **VALIDADO** |
| Dashboard observabilidad | `app/Dashboard/` | `docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Dashboard_General.md` | **VALIDADO** |
| Control Plane SaaS | `app/Control/`, `routes/control.php` | `config/saas_catalog.php` | **VALIDADO** |
| Integraciones | `app/Integration/` | `docs/production/Plan_Integraciones.md` | **VALIDADO** |
| Plataforma / multi-instancia | `app/Shared/Platform/` | `docs/production/ADR_001_instancia_por_cliente.md` | **VALIDADO** |
| Frontend Vue/Inertia | `resources/js/Pages/` | 14 páginas Vue | **VALIDADO** |
| Testing | `tests/` | `docs/testing/` | **VALIDADO** |
| Documentación / ADRs | `docs/` | 158 archivos | **VALIDADO** |

**Instrucción:** El representante legal o líder técnico debe asignar roles concretos (backend, frontend, arquitectura, QA, documentación) a cada autor confirmado. **No se inventan roles individuales** sin evidencia adicional.

---

## 4. Comentarios sobre Roles

Cada autor identificado en Git participó en el desarrollo del software según los mensajes de commit registrados. Los roles técnicos del proyecto incluyen, como mínimo, las siguientes áreas evidenciadas en el código:

| Rol técnico (área) | Responsabilidad evidenciada | Estado |
| ------------------ | --------------------------- |--------|
| Desarrollo backend Laravel | Bounded contexts, APIs, comandos Artisan, migraciones | **VALIDADO** |
| Desarrollo frontend Vue | Portal instancia y control plane vía Inertia | **VALIDADO** |
| Arquitectura de software | DDD, EDA, ADRs, instancia por cliente | **VALIDADO** |
| Integración / DevOps | Scripts CI, smoke tests, runbooks despliegue | **VALIDADO** |
| QA / Testing | Unit, Integration, Feature, E2E, Playwright | **VALIDADO** |

La división exacta por persona requiere **[COMPLETAR_MANUALMENTE]** según conocimiento interno del equipo.

---

## 5. Validación por la Empresa

La empresa **[COMPLETAR_MANUALMENTE — Razón social]**, en calidad de titular de los derechos patrimoniales del software, valida la presente lista de autores y roles, reconociendo los derechos morales de cada integrante confirmado.

Este documento se adjunta como parte del paquete de registro ante Indecopi.

| Campo | Valor |
|-------|-------|
| Representante legal | [COMPLETAR_MANUALMENTE] |
| Sello institucional | [COMPLETAR_MANUALMENTE] |

---

## 6. Firma y Fecha

| Campo | Valor |
|-------|-------|
| Firma del representante legal | _________________________________ |
| Nombre completo | [COMPLETAR_MANUALMENTE] |
| Cargo | [COMPLETAR_MANUALMENTE] |
| Fecha | [COMPLETAR_MANUALMENTE] |

---

## 7. Observaciones Finales

- **No se han inventado autores.** Solo se listan nombres presentes en `git log` con commits humanos.
- **dependabot[bot]** no se incluye como autor.
- Si Brayan Estif Guillen Sanabria y Guillen-Sanabria son la misma persona, consolidar en una sola fila con DNI único.
- Fuente primaria: `git shortlog -sn --all`, `git log --format="%an|%ae|%ai|%s"`.

---

*Documento generado automáticamente. Requiere validación, roles manuales y firma antes de trámite INDECOPI.*
