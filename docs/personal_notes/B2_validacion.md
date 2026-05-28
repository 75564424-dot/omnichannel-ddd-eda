# Fase B.2 — Validación del sync ampliado

**Tipo:** evidencia de calidad (QA) previa al avance de fase.  
**Alcance:** comportamiento de `POST /api/middleware/registry/sync-config` tras incorporar `config('modules.catalog')` (origen `modules_config.json`) junto a `config/eventbus.php`.  
**Fecha de verificación automatizada:** suite PHPUnit del repositorio (estado al momento de redactar este documento).

---

## 1. Resumen de validación

Se comprobó que la implementación B.2 **no rompa** el comportamiento previo basado solo en `eventbus.php` y que el sync:

- siga persistiendo productores y consumidores en `middleware_registered_modules`;
- integre correctamente el catálogo declarativo del JSON cuando está presente y bien formado;
- evite duplicar vínculos equivalentes entre eventbus y JSON dentro del mismo `execute()`;
- tolere filas incompletas en JSON, catálogo ausente o vacío, y JSON inválido en carga de `config/modules.php` (comportamiento heredado del loader).

Complementariamente se documentó qué **no** cubre la suite automatizada (E2E de navegador en `/middleware` y `/dashboard`) y la **limitación de diseño** ya conocida: la cola y el enrutamiento al publicar dependen de `eventbus.subscriptions`, no solo del JSON declarativo.

---

## 2. Resultados

| Área | Resultado | Evidencia / nota |
|------|------------|------------------|
| Compatibilidad con `eventbus.php` (productores / suscripciones) | **OK** | Orden de proceso inalterado para la parte eventbus; tests de registro y publicación siguen pasando. |
| Ejecución de `sync-config` (idempotencia, sin duplicados lógicos en un request, integración JSON) | **OK** | Tests dedicados: persistencia clásica, solo catálogo declarativo con eventbus vacío, deduplicación eventbus + JSON. |
| Flujo completo: JSON → sync → eventos → visualización UI | **WARNING** | Cubierto por tests de API y persistencia; **no** hay evidencia en este paquete de pruebas de smoke E2E obligatorio en navegador. |
| Consistencia “configurado = observado” (UI vs ejecución del bus) | **WARNING** | Registry puede reflejar JSON + eventbus; **consumers en cola** siguen alineados con `eventbus.subscriptions`. Sin alinear ambas fuentes puede haber discrepancia aparente (esperada por diseño). |
| Casos de error: JSON incompleto, duplicados, ausencia de configuración | **OK** / **WARNING** | Filas inválidas omitidas; duplicados tratados; ausencia compatible. **WARNING:** JSON **inválido** en disco hace fallback silencioso a defaults en `config/modules.php` — riesgo operativo, no regresión funcional del endpoint. |

**Prueba automática agregada:** `php vendor/bin/phpunit` — **OK (75 tests, 223 assertions)** en el estado verificado del proyecto.

---

## 3. Problemas encontrados

**Defectos de regresión (FAIL):** ninguno detectado en la ejecución de la suite ni en revisión estática acotada al alcance B.2.

**Observaciones:**

1. Fallback silencioso ante `JsonException` al leer `modules_config.json` — el operador podría ignorar que el catálogo declarativo no se cargó.
2. Posibles filas duplicadas en registry si dos fuentes usan **identificadores distintos** para el mismo módulo conceptual — no es fallo de deduplicación B.2; es convención de datos.
3. La validación visual del flujo en `/middleware` y `/dashboard` queda como **complemento manual** recomendado, no sustituida íntegramente por CI en este informe.

---

## 4. Riesgos

| Riesgo | Impacto |
|--------|---------|
| Expectativa de que “solo JSON” configure consumidores en cola | Medio — confusión entre registry y enrutamiento real del bus. |
| JSON mal formado sin alerta visible | Medio/bajo — sync y app pueden proseguir con catálogo declarativo vacío desde archivo. |
| Pruebas sin BD persistente (p. ej. SQLite `:memory:` por request en escenarios locales) | Bajo — ya mitigado en documentación operativa; puede afectar pruebas manuales cruzadas. |
| Siguiente fase (multi-tenant, UI que edite config, etc.) no cubierta por B.2 | Depende del roadmap — debe tratarse como trabajo explícito. |

---

## 5. Estado final del sistema

**Parcialmente estable** en el sentido estricto de QA integral: **estable** respecto a **regresión automatizada y contrato del sync**, con **advertencias** en cobertura E2E UI y en la semántica “una sola fuente de verdad” para el **comportamiento del bus en publicación**.

Para operación con JSON + `eventbus.php` alineados y BD coherente, el comportamiento se considera **estable** para uso en laboratorio según runbook del proyecto.

---

## 6. Recomendación

**Sí — se puede continuar con la siguiente fase**, con estas condiciones de gobierno:

1. Tratar la alineación **declarativa (JSON) ↔ operativa (`eventbus.php`)** como requisito cuando la métrica de éxito incluya cola y consumers al publicar.
2. Añadir, cuando corresponda al plan de la siguiente fase, smoke manual o E2E mínimo sobre `/middleware` y `/dashboard` si el riesgo de regresión visual aumenta.
3. Opcional: endurecer observabilidad ante JSON inválido (lint, CI, o logging) para reducir el riesgo silencioso.

---

*Documento de evidencia — validación Fase B.2; complementa `B2_sync_ampliado.md` (diseño) y el runbook operativo del repositorio.*
