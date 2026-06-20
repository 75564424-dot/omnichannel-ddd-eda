# Patente — Resumen de Evidencias

**Fecha de generación:** 2026-06-10  
**Proyecto:** `omnichannel-ddd-eda` / `platform/event-bus-core`  
**Carpeta salida:** `docs/Patente/`  
**Metodología:** Solo evidencia del repositorio; sin datos inventados.

---

## 1. Archivos analizados

### Código fuente

| Categoría | Cantidad | Rutas principales |
|-----------|----------|-------------------|
| PHP aplicación | 311 | `app/` (14 bounded contexts) |
| PHP tests | 81 | `tests/Unit`, `Integration`, `Feature`, `E2E` |
| Migraciones BD | 31 | `database/migrations/` |
| Frontend Vue | 14 páginas | `resources/js/Pages/` |
| Rutas | 4 + BC routes | `routes/`, `app/*/Interfaces/Routes/` |
| Comandos Artisan | 16 | `app/Console/Commands/`, Monitoring commands |
| Scripts | 14 | `scripts/ci/`, `scripts/ops/`, `scripts/*.mjs` |
| Configuración | 22+ | `config/*.php`, `config/*.json` |

### Manifiestos

| Archivo | Uso |
|---------|-----|
| `composer.json` | Nombre paquete, dependencias PHP, scripts CI |
| `package.json` | Dependencias frontend, scripts build/dev |
| `phpunit.xml` | Suites de prueba |

### Documentación

| Categoría | Cantidad | Ubicación |
|-----------|----------|-----------|
| Total docs | 158 | `docs/` |
| ADRs | 9 | `docs/production/ADR_*.md` |
| Planes producción | 16 | `docs/production/Plan_*.md` |
| Runbooks | 8 | `docs/production/`, `docs/monitoring/`, `docs/personal_notes/` |
| Arquitectura | 4 | `docs/architecture/` |
| Testing docs | 13+ | `docs/testing/` |
| Matriz trazabilidad | 13 | `docs/matriz_generada/` |
| Mockups HTML | 12+ | `docs/Mokcups_v1.0/`, `v2.0/` |

### Git

| Dato | Valor |
|------|-------|
| Commits totales | 37 |
| Primer commit | 2026-05-27 (`b175b8d`) |
| Último commit | 2026-06-18 (`2c552e0`) |
| Tags | 0 |
| Ramas locales | main, feature/v1.5–v1.7, refactorizacion |

---

## 2. Documentos utilizados (fuentes primarias)

| Documento | Uso en patente |
|-----------|----------------|
| `composer.json` | Nombre técnico, descripción, stack PHP |
| `package.json` | Stack frontend |
| `config/platform.php` | Modelo instancia por cliente |
| `config/eventbus.php` | Event bus |
| `config/saas_catalog.php` | Módulos comerciales PMV |
| `config/platform_roles.php` | RBAC |
| `docs/Plan_Desarrollo_Modulos_v0.1/README.md` | Modelo producto Middleware+Dashboard |
| `docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_*.md` | Capabilities C1–C5, O1–O5 |
| `docs/Plan_Desarrollo_Servicio_v0.1/Flujo_Middleware.md` | Flujo 5 etapas documental |
| `docs/production/ADR_001` a `ADR_009` | Decisiones arquitectónicas |
| `docs/production/Plan_de_implementacion.md` | Readiness y limitaciones |
| `docs/production/Reporte_Implementacion.md` | Estado implementación |
| `docs/architecture/*` | ER, diccionario BD |
| `docs/matriz_generada/reporte_generacion.md` | Trazabilidad cruzada |
| `git log`, `git shortlog` | Autores, fechas, versiones |

---

## 3. Documentos generados (salida)

| Original (intacto) | Completado |
|--------------------|------------|
| `Documento 1 - Ficha técnica de software.docx.md` | `Documento 1 - Ficha técnica de software_COMPLETADO.md` |
| `Documento 2 - Ejemplar del software.docx.md` | `Documento 2 - Ejemplar del software_COMPLETADO.md` |
| `Documento 3 - Declaración jurada de auditoría.docx.md` | `Documento 3 - Declaración jurada de auditoría_COMPLETADO.md` |
| `Documento 4 - Lista de autores y roles.docx.md` | `Documento 4 - Lista de autores y roles_COMPLETADO.md` |
| `Documento 5 - Representación legal de la empresa.docx.md` | `Documento 5 - Representación legal de la empresa_COMPLETADO.md` |
| `Documento 6 - Anexos técnicos.docx.md` | `Documento 6 - Anexos técnicos_COMPLETADO.md` |
| `Plantilla_Registro_General_Software.docx.md` | `Plantilla_Registro_General_Software_COMPLETADO.md` |
| `Registro_Software_INDECOPI.docx.md` | `Registro_Software_INDECOPI_COMPLETADO.md` |
| — | `Patente_Resumen_Evidencias.md` (este archivo) |

**Total:** 8 plantillas originales intactas + 9 archivos de salida.

---

## 4. Datos completados automáticamente

| Campo | Valor completado | Fuente |
|-------|------------------|--------|
| Nombre técnico | `platform/event-bus-core` | composer.json |
| Título registro sugerido | Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta) | composer + git |
| Descripción funcional | Middleware EDA + Dashboard + Control + Integraciones | código + planes |
| Stack tecnológico | PHP 8.2, Laravel 11, Vue 3, Inertia, Vite, Sanctum, SQL | manifiestos |
| Arquitectura | DDD, EDA, bounded contexts, instancia por cliente | ADRs + app/ |
| Bounded contexts | 14 carpetas app/ | filesystem |
| Funcionalidades | Publish, sync, dashboard, control plane, simulación, webhooks | rutas + servicios |
| APIs principales | /api/middleware, /api/dashboard, /api/integrations | MiddlewareApiRoutes |
| Fecha primer commit | 2026-05-27 | git log |
| Versión referenciada | v1.7 + beta | commits 6500034, b175b8d |
| Autores Git | Brayan Estif Guillen Sanabria (16); Guillen-Sanabria (9) | git shortlog |
| Email autores | 75564424@continental.edu.pe | git log |
| Tests | 81 PHP + Playwright; suites Unit/Integration/Feature/E2E | phpunit.xml |
| Migraciones | 31 | database/migrations |
| ADRs | 9 con estado | docs/production |
| Módulos PMV | 7 en saas_catalog | config/saas_catalog.php |
| Licencia | MIT | composer.json |

---

## 5. Campos pendientes de llenado manual

### Datos personales y legales

| Campo | Documento(s) | Marcador |
|-------|--------------|----------|
| DNI declarante | Doc 3 | [COMPLETAR_MANUALMENTE] |
| Domicilio | Doc 3 | [COMPLETAR_MANUALMENTE] |
| Firma autores | Doc 3, 4 | [COMPLETAR_MANUALMENTE] |
| Rol específico por autor | Doc 3, 4 | [COMPLETAR_MANUALMENTE] |
| Razón social | Doc 5 | [COMPLETAR_MANUALMENTE] |
| RUC | Doc 5 | [COMPLETAR_MANUALMENTE] |
| Domicilio legal empresa | Doc 5 | [COMPLETAR_MANUALMENTE] |
| Representante legal | Doc 5 | [COMPLETAR_MANUALMENTE] |
| Vigencia de poder SUNARP | Doc 5 | [COMPLETAR_MANUALMENTE] |
| Sello empresa | Doc 5 | [COMPLETAR_MANUALMENTE] |
| Titular derechos patrimoniales | Doc 3, 5 | [COMPLETAR_MANUALMENTE] |

### Validaciones pendientes

| Campo | Motivo |
|-------|--------|
| ¿Mismo autor Brayan / Guillen-Sanabria? | Mismo email Git — [PENDIENTE_VALIDACION] |
| Nombre comercial producto | No en repositorio — [PENDIENTE_VALIDACION] |
| Fecha exacta versión funcional cerrada | Sin tag release — [PENDIENTE_VALIDACION] |
| Redis en producción | No en composer.json — [PENDIENTE_VALIDACION] |
| SSE O5 implementado en UI | Plan dice opcional — [PENDIENTE_VALIDACION] |
| Resultados QA actuales | No ejecutado en sesión — [PENDIENTE_VALIDACION] |

### Artefactos a producir

| Artefacto | Marcador |
|-----------|----------|
| ZIP ejemplar código | [ANEXO_CODIGO_REPRESENTATIVO_PENDIENTE] |
| PDF 10–15 páginas código | [FRAGMENTO_CODIGO_PENDIENTE] |
| Capturas pantalla (10) | [INSERTAR_CAPTURA_*] |
| Diagrama arquitectura gráfico | [INSERTAR_DIAGRAMA_ARQUITECTURA] |
| Diagrama BPMN formal | [INSERTAR_BPMN] |
| Reporte ejecución tests | [INSERTAR_RESULTADOS_QA] |
| Comprobante pago INDECOPI | [COMPLETAR_MANUALMENTE] |
| Formulario appDR | [COMPLETAR_MANUALMENTE] |

---

## 6. Nivel de confianza por documento

| Documento completado | Nivel | Justificación |
|---------------------|-------|---------------|
| Doc 1 — Ficha técnica | **Alto** | Stack, arquitectura y funcionalidades verificados en código |
| Doc 2 — Ejemplar | **Alto** | Estructura repo y manual técnico basados en evidencia real |
| Doc 3 — Declaración jurada | **Medio** | Texto técnico completo; faltan DNI, firma, titular; autores de Git |
| Doc 4 — Autores y roles | **Medio** | Nombres de Git verificados; roles y DNI requieren manual |
| Doc 5 — Representación legal | **Bajo** | Solo preparado; cero datos legales en repositorio |
| Doc 6 — Anexos técnicos | **Alto** | Flujos, arquitectura, QA documentados; faltan imágenes |
| Plantilla general | **Alto** | Anexos 1 y 2 completados con datos proyecto |
| Registro INDECOPI guía | **Alto** | Checklist y mapeo documentos |
| Resumen evidencias | **Alto** | Meta-documento de control |

**Confianza global del paquete técnico:** **Medio-Alto**  
**Confianza global del paquete legal:** **Bajo** (hasta completar manual)

---

## 7. Riesgos identificados para el trámite

1. **Autores Git ambiguos** — dos nombres, un email; consolidar antes de firmar.
2. **Sin nombre comercial registrado** — definir título final único para todos los documentos.
3. **README incompleto** — manual instalación depende de runbooks dispersos.
4. **Sin capturas ni PDF código** — requisito INDECOPI no cumplido aún en artefactos visuales.
5. **Licencia MIT** — verificar compatibilidad con política de titular patrimonial.
6. **Documentación legacy retail** — aclarar en declaración que no forma parte del core registrado.

---

## 8. Próximos pasos recomendados

1. Definir **título único** y **titular legal** (empresa o universidad).
2. Confirmar **lista definitiva de autores** y asignar roles.
3. Completar y **firmar** declaraciones juradas (una por autor).
4. Generar **ZIP** y **PDF código** (15 archivos listados en Doc 2).
5. Ejecutar `composer ci` y guardar reporte para anexo QA.
6. Tomar **capturas** de las 10 pantallas listadas en Doc 6.
7. Presentar en https://servicios.indecopi.gob.pe/appDR/

---

*Generado automáticamente como control de calidad del paquete de registro de software.*
