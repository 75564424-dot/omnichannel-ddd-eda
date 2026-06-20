# REGISTRO DE SOFTWARE INDECOPI — GUÍA COMPLETADA PARA ESTE PROYECTO

> **Fuente:** `Registro_Software_INDECOPI.docx.md` (plantilla intacta) + metadatos del repositorio.  
> **Software:** Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta)

---

## 1. ¿Qué se puede registrar?

Esta obra de software registrable ante INDECOPI comprende:

| Elemento | Evidencia en el proyecto |
|----------|--------------------------|
| Programa de computador (código fuente) | `app/` (311 PHP), `resources/js/` (Vue), `config/`, `database/` |
| Aplicación web | Rutas `/dashboard`, `/middleware`, `/control`, APIs REST |
| Documentación técnica | `docs/` (158 archivos), ADRs, runbooks, OpenAPI |
| Manual técnico | `Documento 2 - Ejemplar del software_COMPLETADO.md`, runbooks |

**No se protege:** la idea abstracta de "middleware de eventos" — sí la **implementación concreta** en Laravel/Vue de este repositorio.

---

## 2. Requisitos básicos — estado de preparación

| Requisito INDECOPI | Documento del paquete | Estado |
|--------------------|----------------------|--------|
| Formulario solicitud | [COMPLETAR_MANUALMENTE] portal appDR | Pendiente trámite |
| Datos autores y titular | Doc 3, 4, 5 `_COMPLETADO` | Parcial — faltan DNI/RUC |
| Ejemplar software | Doc 2, Plantilla Anexo 1 | Parcial — falta ZIP y PDF código |
| Descripción técnica | Doc 1, Plantilla Anexo 2 | **Completado** (texto) |
| Comprobante pago S/ 58.48 | [COMPLETAR_MANUALMENTE] | Pendiente — verificar tarifa vigente |

---

## 3. Paquete documental generado

| # | Archivo original (intacto) | Versión completada |
|---|---------------------------|-------------------|
| 1 | `Documento 1 - Ficha técnica de software.docx.md` | `Documento 1 - Ficha técnica de software_COMPLETADO.md` |
| 2 | `Documento 2 - Ejemplar del software.docx.md` | `Documento 2 - Ejemplar del software_COMPLETADO.md` |
| 3 | `Documento 3 - Declaración jurada de auditoría.docx.md` | `Documento 3 - Declaración jurada de auditoría_COMPLETADO.md` |
| 4 | `Documento 4 - Lista de autores y roles.docx.md` | `Documento 4 - Lista de autores y roles_COMPLETADO.md` |
| 5 | `Documento 5 - Representación legal de la empresa.docx.md` | `Documento 5 - Representación legal de la empresa_COMPLETADO.md` |
| 6 | `Documento 6 - Anexos técnicos.docx.md` | `Documento 6 - Anexos técnicos_COMPLETADO.md` |
| 7 | `Plantilla_Registro_General_Software.docx.md` | `Plantilla_Registro_General_Software_COMPLETADO.md` |
| 8 | `Registro_Software_INDECOPI.docx.md` | Este archivo |
| 9 | — | `Patente_Resumen_Evidencias.md` |

---

## 4. Datos del software para el formulario

| Campo formulario | Valor sugerido |
|------------------|----------------|
| Título | Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta) |
| Tipo | Aplicación web / plataforma de software |
| Lenguajes | PHP, JavaScript, Vue, SQL |
| Fecha creación | 2026-05-27 (primer commit) |
| Versión | v1.7-beta |
| Descripción breve | Plataforma EDA: middleware event bus + dashboard observabilidad + portal SaaS |

---

## 5. Modalidades de presentación

**En línea:** https://servicios.indecopi.gob.pe/appDR/

**Archivos a adjuntar (digital):**
1. Ficha técnica completada (PDF)
2. Ejemplar: ZIP representativo + PDF 10–15 páginas código
3. Declaraciones juradas firmadas (PDF por autor)
4. Lista autores (PDF)
5. Representación legal (PDF + vigencia poder)
6. Anexos técnicos (PDF + imágenes)

---

## 6. Plazo y resultado

- Plazo referencial INDECOPI: 30–45 días hábiles.
- Resultado esperado: Certificado de Registro de Obra con número de asiento.

---

## 7. Derechos

| Tipo | Titular |
|------|---------|
| Derechos morales | Autores (Brayan Estif Guillen Sanabria; Guillen-Sanabria — confirmar) |
| Derechos patrimoniales | [COMPLETAR_MANUALMENTE — empresa/universidad] |
| Duración patrimonial | Según DL 822 y contrato interno |
| Licencia código | MIT (`composer.json`) — compatible con registro; verificar política titular |

---

## 8. Checklist pre-presentación

- [ ] Completar DNI, domicilio, firmas (Doc 3)
- [ ] Confirmar si autores Git son una o dos personas (Doc 4)
- [ ] Completar razón social, RUC, representante legal (Doc 5)
- [ ] Generar ZIP ejemplar sin `.env` ni secretos
- [ ] Imprimir 10–15 páginas código representativo
- [ ] Tomar capturas dashboard, middleware, control
- [ ] Ejecutar `composer ci` y adjuntar resultados QA
- [ ] Exportar diagrama arquitectura
- [ ] Pagar derecho de trámite
- [ ] Presentar en appDR o MAC presencial

---

*Guía generada automáticamente para el proyecto omnichannel-ddd-eda.*
