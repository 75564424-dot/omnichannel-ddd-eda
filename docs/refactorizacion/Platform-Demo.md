# Auditoría — Platform-Demo

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Platform/Demo/` |
| **Namespace** | `App\Platform\Demo\` |
| **Tipo** | Hooks demo / sample pack |
| **Archivos PHP** | 2 |
| **LOC aprox.** | 42 |
| **Tests** | 0 |

## ¿Qué hace?

Registra **consumidores de eventos de ejemplo** para packs demo: `DemoPackListener` y `DemoPackEventConsumers` reaccionan a eventos del catálogo demo para poblar dashboards y validar el bus.

## ¿Para qué sirve?

- Demostraciones comerciales y entornos de prueba sin integraciones reales.
- Validar wiring productor → bus → consumidor con payloads de fixture.

## Métricas de deuda

| Indicador | Valor | Detalle |
|-----------|-------|---------|
| **% código sucio** | **25%** | Sin tests; mezclado con Platform real en Shared |
| **% código espagueti** | **10%** | Mínimo acoplamiento |
| **Archivos >150 LOC** | 0 | — |

## Cosas sueltas / inconsistentes

1. **Carpeta `app/Platform/Demo`** vs **`app/Shared/Platform`** — naming confuso ("Platform" en dos sitios).
2. **Sin feature flag claro** — demo consumers pueden activarse en instancias que no deberían.
3. **Sin tests** — regressions silentes en demos.

## Recomendaciones de refactor (futuro)

| Prioridad | Acción |
|-----------|--------|
| P4 | Renombrar a `app/Demo/` o mover bajo `config/modules/demo/`. |
| P4 | Tests smoke: evento demo → consumer registrado. |
| P4 | Flag `PLATFORM_DEMO_PACK_ENABLED` documentado. |

## Veredicto

**Trivial.** Renombrar/aislar cuando se ordene Shared/Platform.
