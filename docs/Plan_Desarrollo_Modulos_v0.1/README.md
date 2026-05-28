# Plan de desarrollo — Módulos v0.1 (Middleware + Dashboard)

**Ámbito:** este folder documenta únicamente los dos bounded contexts que forman la **plataforma core** del repositorio: **Middleware (Event Bus)** y **Dashboard (observabilidad)**.  
**Referencia de producto:** la plataforma evoluciona de software exclusivo de un tenant concreto hacia un **servicio de integración y observabilidad de eventos** reutilizable.

---

## 1. Modelo de producto

| Rol | Descripción |
|-----|-------------|
| **Middleware (primario)** | Núcleo del servicio: ingestión, registro, distribución y trazabilidad técnica de eventos. Expone APIs de control y operación del bus. **Es la pieza que puede comercializarse como capacidad principal** (integración EDA). |
| **Dashboard (complemento)** | Capa de observabilidad: métricas, feed, topología y estado coherente con los **read models** y datos que produce el Middleware (y el tráfico observado). **No sustituye al Middleware** y no tiene sentido comercial como producto aislado en esta línea. |

### Regla de empaquetado

- **Oferta estándar del servicio:** Middleware **+** Dashboard en la misma entrega (mismo despliegue/host o mismo contrato de suscripción), porque el Dashboard **consume** APIs y datos derivados del flujo del Middleware.
- **Modalidad headless / API-only:** un integrador puede usar **solo** las APIs de publicación y consulta del Middleware (sin exponer la UI del Dashboard a usuarios finales). Técnicamente el Dashboard sigue siendo parte del **producto versionado** y puede desplegarse en modo restringido o solo para operaciones internas; no se desarrolla como “producto separado” desacoplado del core.

### Principios que el plan refuerza

1. El Middleware **no** contiene reglas de negocio de verticales (retail, OMS, etc.); esos comportamientos viven en **paquetes de integración** externos que publican/consumen eventos.
2. El Dashboard **no** acopla pantallas a módulos de negocio; las KPIs y vistas se **configuran** (JSON) y los orígenes de datos son tablas de observabilidad compartidas con el Middleware.

---

## 2. Documentos en esta carpeta

| Documento | Contenido |
|-----------|-----------|
| [README.md](./README.md) | Modelo de producto, empaquetado, índice (este archivo). |
| [Plan_Modulo_Control_Middleware.md](./Plan_Modulo_Control_Middleware.md) | Plan técnico del **Middleware**: responsabilidades, componentes, flujos, riesgos — alineado a **servicio** y catálogo de eventos externo. |
| [Plan_Modulo_Dashboard_General.md](./Plan_Modulo_Dashboard_General.md) | Plan técnico del **Dashboard**: observabilidad, dependencia del Middleware, configuración, listeners genéricos. |

### Relación con otra documentación en `docs/`

- **`docs/Modulos/`:** fichas resumidas de módulos (pueden quedar como referencia histórica; la línea oficial de producto core es esta carpeta **Plan_Desarrollo_Modulos_v0.1**).
- **`docs/Plan_Desarrollo_Servicio_v0.1/`:** fases y matrices de control a nivel **servicio** (refactor, naming, capas); complementa estos planes sin sustituirlos.
- **`docs/DC_Mockups_obsoletos(NOusar)/`:** mockups legacy; no deben gobernar el alcance del core actual.

---

## 3. Dependencias entre módulos (vista simplificada)

```text
[ Productores / consumidores externos ]
           │ eventos (contrato genérico)
           ▼
    ┌──────────────────┐
    │    Middleware    │◄──── APIs / persistencia bus / registro
    │   (servicio)     │
    └────────┬─────────┘
             │ read models compartidos (feed, cola, métricas bus)
             ▼
    ┌──────────────────┐
    │    Dashboard     │
    │  (complemento)   │
    └──────────────────┘
```

---

## 4. Versionado

- **v0.1** — Plan alineado a repositorio **platform/event-bus-core**: core sin módulos de negocio embebidos; integración vía configuración y extensiones.

---

*Última actualización del índice: 2026-05 (reorientación Middleware como servicio + Dashboard como complemento incluido).*
