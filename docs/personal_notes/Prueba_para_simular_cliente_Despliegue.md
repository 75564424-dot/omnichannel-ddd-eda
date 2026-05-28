# Validación integral — ¿Listo para simular un cliente real sin intervención técnica?

**Rol:** arquitectura + QA Lead (evaluación previa a simulación “como cliente real”).  
**Restricción:** solo evaluación; sin refactor ni implementación en este documento.  
**Contexto del sistema:** Fase A (runbook), B.2 (sync ampliado), C (registro dinámico), D (instancia por cliente), E (pruebas y evidencia QA).

---

## Pregunta directa

**¿El sistema está listo para simular un cliente real *sin* intervención técnica?**

**Respuesta:** **No.** Simular un cliente de forma creíble **sí** es viable con **perfil técnico** (dev/ops/integrador); **no** es usable hoy como producto donde un usuario de negocio o un “cliente final” lo haga solo sin tocar archivos, consola ni conceptos de bus/config.

Por tanto el **estado global** respecto al objetivo literal (“sin intervención técnica”) es **no listo**.  
Respecto a **simulación asistida por equipo técnico**, el estado es **parcialmente listo** (ver §1).

---

## 1. Estado del sistema

| Dimensión | Valoración |
|-----------|------------|
| **Respecto a “sin intervención técnica”** | **No listo** |
| **Respecto a simulación con integrador/dev (laboratorio o staging)** | **Parcialmente listo** |
| **Respecto al núcleo técnico (bus, sync, UI, tests)** | **Listo** en el sentido de que la suite automatizada y el runbook permiten un recorrido reproducible con evidencia (Fase E). |

---

## 2. Flujo completo — análisis

| Etapa | ¿Funciona en el diseño actual? | ¿Sin intervención técnica? |
|-------|--------------------------------|---------------------------|
| **Configuración de módulos** | Sí, vía `modules_config.json` (+ coherencia con `eventbus.php` para enrutamiento) | **No** — requiere editor, JSON válido, entender productores/suscriptores |
| **sync-config** | Sí — API `POST …/registry/sync-config` (B.2 + eventbus) | **No** — requiere llamar API o usar UI botón en `/middleware`, conocer orden con `config:clear` si hay cache |
| **Emisión de eventos** | Sí — HTTP publish / comandos artisan | **No** — construcción de payload, UUID, `event_type` alineado a suscripciones |
| **Consumo** | Sí — suscripciones en config + packs (C); listeners reales según lo desplegado | **No** — definición de `consumer_registrars` o filas en `eventbus.php` es tarea técnica |
| **Visualización** | Sí — `/middleware`, `/dashboard`, APIs | **Parcial** — un usuario puede *ver* si alguien ya configuró y generó tráfico; no puede completar el ciclo solo |

**Conclusión de flujo:** el **recorrido extremo a extremo es válido** con documentación (runbook, simulación productiva); **no** es autónomo para un perfil no técnico.

---

## 3. Coherencia: configurado = ejecutado = visualizado

| Relación | Grado de cumplimiento | Fricción |
|----------|----------------------|----------|
| **Declarado (JSON) = registry (tras sync)** | **Alto** si se ejecuta sync tras cambios | Olvidar sync o editar solo un archivo |
| **Declarado = consumidores en cola al publicar** | **Solo si** `eventbus.subscriptions` está alineado con los `event_type` | JSON sin espejo en eventbus → “configurado en UI” ≠ “ejecutado” |
| **Ejecutado = feed/KPIs dashboard** | **Medio** — depende de `dashboard_config.json`, `event_id` en payload, reglas de ingestión | Fallos “silenciosos” en feed vía skips en log |
| **Global** | **Coherencia fuerte** cuando un **mismo integrador** gobierna JSON + eventbus + packs | **Coherencia débil** si distintas personas editan solo una fuente |

👉 “Lo configurado = lo ejecutado = lo visualizado” **no es automático**; es **obligación operativa** alinear fuentes y orden de pasos.

---

## 4. Usabilidad operativa

| Pregunta | Evaluación |
|----------|------------|
| **¿Requiere tocar código?** | **No es obligatorio** para el escenario estándar (solo config + sync + APIs/comandos). **Sí** si el cliente necesita nuevos listeners → packs PHP y `consumer_registrars` o equivalente. |
| **¿Pasos confusos?** | **Sí** para quien no conozca la matriz de fuentes de verdad (JSON vs `eventbus.php` vs C). El runbook mitiga; no elimina curva de aprendizaje. |
| **¿Producto “llave en mano” para no técnicos?** | **No.** |

---

## 5. Riesgos

| Riesgo | Impacto |
|--------|---------|
| **Error humano** — editar solo `modules_config.json` | Cola/registry desalineados respecto a expectativas de “consumidores” |
| **Config inconsistente** — tipos distintos entre JSON y eventbus | Visualización “rica” en catálogo y cola pobre o vacía en consumers |
| **Fallos silenciosos** — JSON inválido (fallback a defaults en `config/modules.php`) | Cree que cargó módulos y la app sigue con catálogo vac/default |
| **`config:cache`** | Cambios no visibles hasta `config:clear` |
| **SQLite `:memory:`** en pruezas manuales cruzadas | Datos “desaparecen” entre terminal y navegador (ya documentado en runbook) |

---

## 6. Necesidad de refactorización (evaluación, sin ejecutar)

| Problema | ¿Crítico? | ¿Bloquea simulación? | ¿Puede esperar? |
|----------|-----------|----------------------|-----------------|
| Ausencia de UI/API de gobierno unificado para no técnicos | No para laboratorio técnico | **Bloquea** simulación *sin* intervención técnica | Sí como evolución de producto |
| Dos fuentes (`modules_config.json` + `eventbus`) | No si hay disciplina | No si el integrador alinea | Comando `validate-catalog` / mirror automático puede esperar priorización |
| Logs de skip en feed no siempre visibles al usuario final | Bajo–medio | No para demo técnica | Mejoras de UX/alertas |
| Multi-tenant en una app | N/A modelo D | N/A instancia por cliente | Decisión explícita futura |

**No se identifica en esta evaluación un defecto de refactor “crítico bloqueante”** para que un **equipo técnico** simule un cliente en staging; sí hay **huecos de producto** frente al objetivo “cero intervención técnica”.

---

## 7. Problemas detectados (resumen)

1. **Dependencia de perfiles técnicos** para configurar, publicar y validar de punta a punta.  
2. **Duplicidad conceptual** de configuración (JSON declarativo vs eventbus operativo) — gestionable, no eliminada por magia.  
3. **JSON inválido** puede pasar inadvertido.  
4. **Documentación del plan maestro** (tabla 1.1) puede estar desincronizada respecto a B.2/C implementados — riesgo de decisión errónea si no se actualiza.  
5. **E2E UI** no como gate obligatorio en CI — riesgo de regresión visual.

---

## 8. Acciones recomendadas

### Inmediatas (antes de decir “simulamos cliente real”)

1. Ejecutar en **staging** el runbook + **`Simulacion_escenario_productivo.md`** con evidencia (capturas o log firmado).  
2. Asegurar **BD archivo** o instancia estable; evitar `:memory:` para demos cruzadas.  
3. Actualizar **`Plan_de_implementacion.md`** §1.1 para reflejar sync con JSON y Fase C viva.  
4. **Formar** al operador que hará de “cliente”: lista corta de pasos y qué archivo tocar.

### Futuras (no bloquean simulación técnica actual)

1. `platform:validate-catalog` (B.3) en CI.  
2. Asistente o plantilla que genere **par** JSON/eventbus alineado.  
3. E2E UI automatizado.  
4. Panel de gobierno (solo si el producto apunta a no técnicos).

---

## 9. Conclusión

**¿Se puede simular un cliente real *ahora*?**

- **Sí**, si “cliente real” significa **escenario end-to-end ejecutado por integradores/dev/ops** con runbook, staging y posiblemente Fase D (instancia dedicada).  
- **No**, si “cliente real” implica **autonomía sin intervención técnica** (solo negocio o usuario final): el sistema **aún no es ese producto**.

**Veredicto único para el título de este documento:**  
**Parcialmente listo** para simulación **realista con soporte técnico**; **no listo** como producto usable **sin** esa intervención.

---

## Referencias (evidencia y procedimiento)

- `Runbook_cliente_simulado.md`  
- `Simulacion_escenario_productivo.md`  
- `Fase_E_QA.md` / `Release_decision_QA.md`  
- `Fase_D_arquitectura_cliente.md`  
- `Observabilidad_pruebas_produccion_local.md`  

---

*Validación integral — despliegue / simulación de cliente. Solo evaluación; sin refactor.*
