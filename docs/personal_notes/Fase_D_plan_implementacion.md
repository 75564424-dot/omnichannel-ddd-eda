# Plan de implementación progresiva — Fase D (instancia por cliente)

**Referencia:** `docs/personal_notes/Fase_D_blueprint_instancia_por_cliente.md`  
**Principio:** incrementos controlados; **sin** multi-tenant en una sola app hasta decisión explícita futura.  
**Base técnica existente:** B.2 (`sync-config` + JSON), C (`consumer_registrars` + interface); **no** son prerequisitos de código nuevos para iniciar D.

---

## Visión por etapas

| Fase | Objetivo | Resultado observable |
|------|----------|----------------------|
| **1 — Preparación** | Alinear proceso, documentación y criterios de “un cliente = un despliegue” | Equipo sabe qué tocar y en qué orden; riesgos explícitos |
| **2 — Adaptación mínima** | Primer ciclo real: config por instancia + smoke repetible | Al menos **una** instancia no local con el flujo runbook + sync |
| **3 — Soporte completo** | Repetible para N clientes con gobierno y automatización ligera | Alta de cliente sin improvisación; menos retrabajo |
| **4 — Optimización** | Reducir coste operativo y mejorar observabilidad / resiliencia | Mejor TCO y claridad en incidentes |

---

## Fase 1 — Preparación

### Tareas

| ID | Tarea | Detalle |
|----|--------|---------|
| D1.1 | Formalizar modelo operativo | Incorporar en `Plan_de_implementacion.md` (o anexo) la decisión: **instancia = cliente**, BD dedicada, sin `tenant_id` obligatorio en app. |
| D1.2 | Actualizar / enlazar runbook | `Runbook_cliente_simulado.md` (o nota): sección **“Despliegue por cliente”**: orden migrate → `config:clear` si aplica → sync-config → publish prueba → `/middleware` + `/dashboard`. |
| D1.3 | Inventario de artefactos por cliente | Lista mínima: `.env`, `modules_config.json`, fragmentos `eventbus.php` / `consumer_registrars`, versión de imagen/commit. |
| D1.4 | Definir convención de naming | Documento corto: `event_type`, `origin`, slug en logs (`PLATFORM_CLIENT_SLUG` opcional). |
| D1.5 | Matriz RACI ligera | Quién posee GitOps, secretos, primer soporte post-deploy. |
| D1.6 | Criterios de “listo para Fase 2” | Checklist firmada: backups por instancia, accesos a repos/secrets, entorno staging disponible o acordado. |

### Dependencias

- Ninguna nueva dependencia de código.
- Alineación con legales/comercial si aplica (datos en región, etc.).

### Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Equipo asume multi-tenant en una sola URL | Comunicación explícita + revisión del blueprint |
| Runbook desactualizado respecto a B.2/C | Revisar sync + `consumer_registrars` en el mismo paso operativo |

### Entregables

- Texto aprobado en docs (plan + runbook extendido o enlazado).
- Plantilla de “ficha cliente” (markdown/Notion/hoja) con artefactos listados.

---

## Fase 2 — Adaptación mínima

### Tareas

| ID | Tarea | Detalle |
|----|--------|---------|
| D2.1 | Pipeline parametrizado | Un job reutilizable que inyecte **variables por cliente** (env + ruta/config de `modules_config` embebido o adjunto **solo** a ese deploy). |
| D2.2 | Primera instancia piloto | Staging o cliente piloto: misma imagen que prod futura; BD **dedicada**; sin datos de otro cliente. |
| D2.3 | Post-deploy smoke | Script/curl: health → `POST …/registry/sync-config` (200, `success`) → `POST …/events/publish` mínimo → GET cola/topología según criterio del equipo. |
| D2.4 | Variables de trazabilidad | Añadir opcional `PLATFORM_CLIENT_SLUG` / `APP_NAME` cliente en `.env` del piloto; verificar que aparezca en logs sin tocar lógica de negocio. |
| D2.5 | Procedimiento restore | “Restore solo con backup **etiquetado** al cliente X”; evitar mezcla de dumps. |
| D2.6 | Retro piloto | Lista de fricciones (tiempo, pasos olvidados, errores de config). |

### Dependencias

- **Fase 1** cerrada (documentación + convenciones).
- Acceso a infraestructura del piloto.

### Riesgos

| Riesgo | Mitigación |
|--------|------------|
| JSON o `consumer_registrars` equivocados en el piloto | Revisión en PR + diff contra plantilla |
| `config:cache` oculta cambios | Procedimiento documentado; rebuild tras cambio de config |
| SQLite o BD compartida por error | Checklist infra: string de conexión **única** por instancia |

### Entregables

- Instancia piloto **verificada** con evidencia (capturas o log de smoke).
- Pipeline v1 documentado (parámetros mínimos).
- Plantilla `.env.example` por cliente (sin secretos).

---

## Fase 3 — Soporte completo

### Tareas

| ID | Tarea | Detalle |
|----|--------|---------|
| D3.1 | GitOps / IaC por cliente | Repo o carpeta por cliente **o** mismo repo con **values**/overlays separados (Helm/Kustomize/terraform module); trazabilidad de quién desplegó qué. |
| D3.2 | Automatizar sync en deploy | Opcional: step post-rollout que ejecute `sync-config` (job K8s, hook, o `artisan` en init si es idempotente y seguro). |
| D3.3 | Inventario “fleet” | Mínimo viable: tabla (Cliente | URL | Versión | Último deploy | Owner) mantenida manualmente o export desde CI. |
| D3.4 | Comando `platform:validate-catalog` (si no existe) | Cierra hueco plan B.3: falla si declarativo vs `eventbus`/packs incoherente según reglas acordadas. |
| D3.5 | Formación corta | 30–60 min para ops: orden migrate/sync/publish, dónde está el JSON, qué es `consumer_registrars`. |
| D3.6 | Contrato de soporte L1/L2 | Qué toca el cliente vs proveedor (cambios en JSON, packs). |

### Dependencias

- **Fase 2** completada con retro incorporada.

### Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Drift de versiones entre clientes | Política de ventana de actualización + inventario |
| Fatiga operativa con N clientes | D3.1 + D3.3 obligatorios antes de escalar N>3 |
| Automatización sync demasiado agresiva | Sync solo tras validar migraciones y health; rollback documentado |

### Entregables

- Procedimiento **estándar de alta de cliente** (checklist de X pasos).
- Fleet inventory vivo + pipeline estable multi-cliente.
- `platform:validate-catalog` en CI o pre-deploy (si se implementa en esta fase).

---

## Fase 4 — Optimización

### Tareas

| ID | Tarea | Detalle |
|----|--------|---------|
| D4.1 | Config dinámica de JSON (opcional) | `MODULES_CONFIG_PATH` o volumen montado sin rebuild de imagen — **solo** si el negocio lo exige. |
| D4.2 | Observabilidad unificada | Tags `client_slug` / `deployment_id` en APM/logs; dashboards por cliente o facetas. |
| D4.3 | Playbooks DR | RTO/RPO por tier; prueba anual de restore **por instancia**. |
| D4.4 | Coste y densidad | Revisión: ¿algún segmento justifica multi-tenant real? Decisión documentada **antes** de invertir en refactor. |
| D4.5 | Hardening | Rotación de secretos, rate limits en API pública, backups cifrados — según perfil de amenaza. |

### Dependencias

- **Fase 3** estable con ≥2 clientes reales o carga representativa en staging.

### Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Optimización prematura (D4.1 sin necesidad) | Gate: “>X cambios/mes de JSON sin rebuild” |
| Observabilidad sin PII policy | Acordar campos en logs con compliance |

### Entregables

- DR test documentado.
- Informe de optimización (coste, observabilidad, decisión multi-tenant diferida o no).

---

## Dependencias globales (diagrama lógico)

```text
Fase 1 (docs + convenciones)
    │
    ▼
Fase 2 (piloto + pipeline mínimo + smoke)
    │
    ▼
Fase 3 (repetible + fleet + validación opcional)
    │
    ▼
Fase 4 (opcional según escala y dolor medido)
```

**Paralelo seguro:** B.3 (`validate-catalog`) puede iniciarse en Fase 3 si hay capacidad de desarrollo; no bloquea Fase 2 operativa.

---

## Control de “no romper producción”

| Regla | Motivo |
|-------|--------|
| Cambios de producto **desacoplados** de D hasta Fase 3–4 | D es sobre todo **cómo** se despliega, no refactor del bus |
| Piloto **fuera** de tráfico real crítico hasta checklist verde | Reduce blast radius |
| Sync y migraciones **siempre** en orden documentado | Evita registry/cola incoherentes |
| Un cliente nunca comparte BD con otro | Aislamiento acordado en blueprint |

---

## Resumen ejecutivo

| Fase | Esfuerzo típico | Valor principal |
|------|-----------------|----------------|
| 1 | Bajo | Consenso y documentación |
| 2 | Medio | Primera prueba de fuego repetible |
| 3 | Medio–alto | Escala controlada a N clientes |
| 4 | Variable | Eficiencia y madurez operativa |

---

*Plan ejecutable — Fase D (instancia por cliente). Ajustar fechas y owners en la herramienta de gestión del equipo.*
