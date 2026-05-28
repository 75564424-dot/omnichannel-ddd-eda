# ADR-009: OpenTelemetry y trazabilidad distribuida

**Estado:** Aceptado (implementación incremental)  
**Fecha:** 2026-05-21  
**Plan:** Plan_Observabilidad.md Fase 3

---

## Contexto

El middleware omnicanal requiere correlación cross-service para diagnóstico en producción. El plan define OpenTelemetry como objetivo de Fase 3, pero añadir el SDK PHP completo incrementa dependencias, superficie de configuración y overhead en runtime PHP-FPM.

---

## Decisión

1. **Fase 3 actual:** trazabilidad ligera con `trace_logs`, `correlation_id` y spans en publish/track/project.
2. **OpenTelemetry SDK PHP:** **diferido** a despliegue cloud con sidecar/collector (Datadog Agent, AWS ADOT, Grafana Alloy).
3. **Compatibilidad:** IDs (`trace_id`, `span_id`, `parent_span_id`) siguen formato UUID compatible con W3C `traceparent` mapping manual.
4. **Export:** `/metrics` Prometheus cubre SLIs; traces se consultan vía SQL/API interna hasta integrar OTLP.

---

## Consecuencias

### Positivas

- Sin dependencia OTel en composer en esta fase
- Menor riesgo de regresión en PHP 8.2 + Laravel 11
- Patrón sidecar alineado con Kubernetes/ECS enterprise

### Negativas

- No hay auto-instrumentación HTTP/DB
- Integración con Jaeger/Tempo requiere exporter OTLP futuro o ETL desde `trace_logs`

---

## Migración futura

1. Desplegar OpenTelemetry Collector como sidecar
2. Instrumentar outbound HTTP (integraciones) con propagación `traceparent`
3. Opcional: package `open-telemetry/opentelemetry` solo en workers si el overhead es aceptable
4. Retirar consultas SQL directas a `trace_logs` cuando OTLP esté estable

---

## Referencias

- [Plan_Observabilidad.md](Plan_Observabilidad.md)
- [Observabilidad.md](Observabilidad.md)
- W3C Trace Context: https://www.w3.org/TR/trace-context/
