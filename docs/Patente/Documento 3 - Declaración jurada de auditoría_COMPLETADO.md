**Documento 3**  
**DECLARACIÓN JURADA DE AUTORÍA — VERSIÓN COMPLETADA**

> **Fuente de generación:** análisis del repositorio `omnichannel-ddd-eda` (2026-06-10).  
> **Plantilla base:** `Documento 3 - Declaración jurada de auditoría.docx.md` (intacta).

---

## 1. Encabezado Formal

**DECLARACIÓN JURADA DE AUTORÍA**

---

## 2. Identificación del Declarante

Yo, **[COMPLETAR_MANUALMENTE — Nombre completo]**, identificado con Documento Nacional de Identidad (DNI) N.º **[COMPLETAR_MANUALMENTE]**, con domicilio en **[COMPLETAR_MANUALMENTE — Dirección completa]**, en calidad de autor del software denominado **"Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta)"**, declaro bajo juramento lo siguiente:

| Campo | Valor | Estado |
|-------|-------|--------|
| Título del software (registro) | Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta) | **VALIDADO** |
| Nombre técnico paquete | `platform/event-bus-core` | **VALIDADO** (Fuente: `composer.json`) |
| Fuente título | `composer.json`, `git log` (commits beta y v1.7) | **VALIDADO** (Fuente: `git log`) |

---

## 3. Reconocimiento de Autoría

Declaro ser autor del software mencionado, desarrollado principalmente en **PHP 8.2**, **JavaScript**, **Vue 3** y **SQL**, con framework **Laravel 11** e **Inertia.js**, cuya funcionalidad principal consiste en:

> Proporcionar una **plataforma de integración por eventos (Event Bus / Middleware)** con **observabilidad operativa (Dashboard)**, **portal de administración SaaS (Control Plane)**, **gestión de instancias por cliente**, **integraciones vía webhooks**, **simulación de clientes** y **monitoreo/alertas**, sin embebimiento de reglas de negocio vertical en el núcleo.

**Fuente funcionalidad:** `composer.json` description; `docs/Plan_Desarrollo_Modulos_v0.1/README.md`; capabilities C1–C5 y objetivos O1–O5. **VALIDADO**

Mi participación se centró en el rol de **[COMPLETAR_MANUALMENTE — rol específico del declarante]**, contribuyendo al diseño, programación y/o pruebas del sistema.

**Roles inferibles del proyecto (no asignar sin confirmación del declarante):**

| Área técnica | Evidencia en repositorio | Estado |
|--------------|--------------------------|--------|
| Backend / Middleware | `app/Middleware/`, commits aislamiento y v1.7 | **VALIDADO** |
| Frontend Vue/Inertia | `resources/js/Pages/`, 14 vistas | **VALIDADO** |
| Control Plane / SaaS | `app/Control/`, `routes/control.php` | **VALIDADO** |
| Arquitectura / ADRs | `docs/production/ADR_*.md` | **VALIDADO** |
| Testing / QA | `tests/` (81 archivos PHP), Playwright | **VALIDADO** |
| Integraciones | `app/Integration/` | **VALIDADO** |
| Documentación técnica | `docs/` (158 archivos) | **VALIDADO** |

---

## 4. Reconocimiento de Coautores

El software fue creado en colaboración con los siguientes contribuyentes identificados en el historial Git del repositorio (excluyendo bots automatizados):

| # | Nombre (Git) | Correo evidenciado | Commits | Rol inferido | DNI | Periodo participación | Estado |
|---|--------------|-------------------|---------|--------------|-----|----------------------|--------|
| 1 | Brayan Estif Guillen Sanabria | 75564424@continental.edu.pe | 16 | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | 2026-05-27 a 2026-06-18 | **VALIDADO** (Fuente: `git log`) |
| 2 | Guillen-Sanabria | 75564424@continental.edu.pe | 9 | [COMPLETAR_MANUALMENTE] | [COMPLETAR_MANUALMENTE] | 2026-05-28 a 2026-06-16 | **VALIDADO** (Fuente: `git log`) |

**Nota importante:** Ambos autores Git comparten el **mismo correo electrónico** (`75564424@continental.edu.pe`). **PENDIENTE DE VALIDACIÓN** si corresponden a la misma persona con distinta configuración Git o a coautores distintos. No se debe asumir sin confirmación manual.

**Contribuciones evidenciadas por mensajes de commit (sin atribución definitiva de rol):**

| Autor Git | Hitos en commits | Estado |
|-----------|------------------|--------|
| Brayan Estif Guillen Sanabria | Versión beta inicial; v1.7; Fase 6; refactorización; merges; notificaciones tiempo real | **VALIDADO** (Fuente: `git log`) |
| Guillen-Sanabria | Fase aislamiento multi-instancia; simulación empresas; refactorización código; corrección bugs | **VALIDADO** (Fuente: `git log`) |

**dependabot[bot]:** 12 commits de actualización de dependencias — **no constituye coautoría humana**.

Cada coautor humano debe suscribir su propia declaración jurada con DNI y firma.

---

## 5. Declaración de Originalidad

Declaro que la obra es original, producto de trabajo creativo de programación, y que **no se ha verificado automáticamente** infracción de derechos de terceros más allá del uso declarado de librerías open source listadas en `composer.json` y `package.json` (licencias MIT y equivalentes de dependencias).

El núcleo del software **no embebe dominios de negocio retail** (ventas, inventario, pedidos) como requisito del producto base, según documentación oficial v0.1 (`docs/Plan_Desarrollo_Modulos_v0.1/README.md`). Documentación legacy en `docs/Modulos/` y `docs/DC_Mockups_obsoletos(NOusar)/` está marcada como referencia histórica, no como alcance del core.

El software constituye una implementación independiente de plataforma EDA/DDD desarrollada por el equipo identificado en Git, desplegada como servicio de integración y observabilidad.

**[COMPLETAR_MANUALMENTE]** — El declarante debe confirmar personalmente que no existe copia no autorizada de código de terceros.

---

## 6. Autorización a la Empresa o Titular

Autorizo a **[COMPLETAR_MANUALMENTE — Razón social o Universidad]**, identificada con RUC N.º **[COMPLETAR_MANUALMENTE]**, a gestionar el registro del software ante Indecopi, reconociendo que:

- Los **derechos patrimoniales** corresponden al titular indicado (empresa, universidad o persona natural según contrato interno).
- Los **derechos morales** de autoría permanecen en mi calidad de creador y coautores.

**Titular sugerido a confirmar:** [COMPLETAR_MANUALMENTE]

**Alternativa académica (si aplica):** Universidad **[COMPLETAR_MANUALMENTE — Nombre]**, según convenio de titularidad de obras derivadas de proyecto de tesis o curso.

---

## 7. Cláusula de Juramento

Declaro bajo juramento la veracidad de lo expuesto en el presente documento, comprometiéndome a responder legalmente en caso de falsedad.

---

## 8. Firma y Fecha

| Campo | Valor |
|-------|-------|
| Firma | _________________________________ |
| Nombre completo | [COMPLETAR_MANUALMENTE] |
| DNI | [COMPLETAR_MANUALMENTE] |
| Fecha | [COMPLETAR_MANUALMENTE] |

---

## 9. Observaciones Finales

- Esta declaración corresponde al software **Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta)**.
- Debe existir **una declaración firmada por cada autor humano** listado en la sección 4.
- Los campos marcados [COMPLETAR_MANUALMENTE] son obligatorios para validez legal.
- **Fuente de autores:** `git shortlog -sn --all`, `git log --format="%an|%ae"`.

---

*Documento preparado automáticamente. Requiere completado manual, firma y validación legal antes de presentación ante INDECOPI.*
