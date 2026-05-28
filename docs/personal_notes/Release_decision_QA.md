# Decisión de salida a producción — plataforma bus / middleware / dashboard

**Rol:** QA Lead / responsable de release (evaluación en fecha de verificación técnica del repositorio).  
**Evidencia automática:** `php vendor/bin/phpunit` — **OK (83 tests, 274 assertions)**.  
**Modelo de despliegue asumido:** **Fase D — instancia por cliente** (silo, BD dedicada), no multi-tenant lógico en una sola app.

---

## 1. Estado

### **GO con riesgos** (condicionado)

| Ámbito | Veredicto |
|--------|------------|
| **Núcleo técnico** (publicación, cola, tracking, sync B.2, registro C, APIs middleware/dashboard documentadas) | **GO** para producción **por instancia**, tras completar checklist operativo en **staging** (no solo local). |
| **Producto “el cliente configura todo en runtime sin deploy”** | **NO-GO** — sigue sin haber panel/API de gobierno unificado; gobierno por archivos + CI/CD + packs. |
| **Parque grande de clientes sin automatización de ops** | **GO con riesgos alto** — riesgo operativo, no necesariamente de código. |

En una sola frase: **se puede etiquetar una release como producible para el core de plataforma en modelo instancia-por-cliente, siempre que staging haya ejecutado el runbook y la simulación productiva y no se vendan capacidades aún no implementadas.**

---

## 2. Justificación

**Pruebas**  
- Suite PHPUnit **verde** con cobertura relevante en: API de middleware (publish, sync, cola, topología, eventos), merger de packs (C), flujo integrado API (`MiddlewarePipelineEndToEndTest`), integración de publisher y tracking.  
- No constituyen por sí solas prueba de carga, caos ni seguridad completa.

**Estabilidad**  
- Arquitectura acotada (middleware + dashboard + bus); cambios recientes B.2/C integrados sin fallos detectados en CI local.  
- **Estabilidad operativa** depende de buen uso de `eventbus` + JSON + `consumer_registrars` y de BD/archivos por instancia (Fase D).

**Errores**  
- No se reportan en esta evaluación **defectos bloqueantes** abiertos en código verificado por la suite.  
- Riesgos residuales son sobre todo **de producto/operación** (expectativas vs capacidades, drift de config, ausencia de E2E UI obligatorio en CI).

**Desalineación documental**  
- `Plan_de_implementacion.md` (v1.0) contiene líneas que **quedaron desactualizadas** respecto al código actual (p. ej. sync que ya fusiona JSON; fusión C implementada). Eso **no bloquea** el GO técnico, pero debe corregirse para evitar decisiones basadas en texto viejo.

---

## 3. Riesgos

| Riesgo | Severidad | Nota |
|--------|-----------|------|
| **Expectativa de configuración 100 % en runtime** sin redeploy | Alta (negocio) | Plan original marca “no cumple”; comunicar en release notes. |
| **E2E UI no obligatorio en CI** | Media | Regresión visual solo manual o herramienta externa hasta que se automatice. |
| **Divergencia JSON vs `eventbus`** por operador | Media | Mitigar con runbook, simulación productiva y (recomendado) `platform:validate-catalog`. |
| **`config:cache` + JSON/packs** mal gestionado | Media | Procedimiento claro en despliegue. |
| **JSON inválido — fallback silencioso** en `modules.php` | Media | Lint/CI de JSON o revisión en PR. |
| **Escalado N instancias** sin fleet/inventario | Media | Fase D plan; riesgo de ops, no de commit único. |
| **Carga / seguridad / DR** no cubiertas por esta suite | Variable | Gate adicional según SLA del cliente. |

---

## 4. Recomendaciones

1. **Antes del primer GO real:** ejecutar en **staging** el contenido de `Simulacion_escenario_productivo.md` + smoke post-deploy (sync → publish → cola → UI).  
2. **Actualizar** `Plan_de_implementacion.md` sección 1.1 para reflejar B.2/C reales y enlazar documentos de Fase D y estrategia de pruebas.  
3. **Formalizar** criterios de release en checklist (copiar de `Estrategia_pruebas_pre_produccion.md`).  
4. **No** prometer multi-tenant ni “self-service config” hasta diseño explícito.  
5. Tras primer cliente en prod: **retrospectiva** de incidentes de config y tamaño de lista `consumer_registrars`.

---

## Tabla resumen ejecutiva

| Pregunta | Respuesta |
|----------|-----------|
| ¿La suite automatizada da luz verde al merge? | **Sí** (evidencia actual). |
| ¿Listo para producción sin condiciones? | **No** — condicionado a staging, alcance contratado y gobierno operativo. |
| ¿Listo como núcleo de plataforma por instancia? | **Sí, con riesgos gestionados** según §3 y §4. |

---

*Evaluación objetiva; revisar en cada release tag y tras cambios en bus, sync o seguridad.*
