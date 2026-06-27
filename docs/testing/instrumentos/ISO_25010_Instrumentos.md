# Instrumento — ISO/IEC 25010 (calidad del producto software)

**Versión:** 1.0  
**Fecha:** 2026-06-27  
**Norma de referencia:** ISO/IEC 25010:2011 (modelo SQuaRE — calidad en uso del producto)  
**Alineación evaluation:** [docs/evaluation/08_Matriz_Calidad.csv](../../evaluation/08_Matriz_Calidad.csv), [docs/evaluation/04_Guia_Instrumentos_Medicion.md](../../evaluation/04_Guia_Instrumentos_Medicion.md)

## 1. Propósito

Mapear las **características y subcaracterísticas** de ISO 25010 a las suites de prueba del proyecto y a los instrumentos de medición del framework de evaluación.

## 2. Resumen por característica (2026-06-27)

| Característica ISO 25010 | Subcaracterísticas cubiertas | Métodos test | Cobertura estimada |
|--------------------------|------------------------------|--------------|-------------------|
| Adecuación funcional | Completitud, corrección, pertinencia | Unit+Feature+E2E | Alta (middleware core) |
| Eficiencia de rendimiento | Comportamiento temporal | Unit (métricas) + k6 (manual) | Media |
| Compatibilidad | Interoperabilidad | OpenAPI, V1 mirror | Alta |
| Usabilidad | Reconocibilidad | Feature web (parcial) | Baja (sin UI E2E) |
| Fiabilidad | Recuperabilidad, tolerancia fallos | ResilienceApiTest, RetryPolicyTest | Media-Alta |
| Seguridad | Confidencialidad, autenticidad | Security+Identity Feature | Alta (1 fallo) |
| Mantenibilidad | Modularidad, analizabilidad | Unit (200 métodos) | Alta |
| Portabilidad | Adaptabilidad | Config por instancia, E2E fixtures | Media |

## 3. Suite PHPUnit por capa

| Suite | Métodos | Rol ISO 25010 |
|-------|---------|---------------|
| Unit | 200 | Mantenibilidad, corrección local |
| Integration | 21 | Fiabilidad, interoperabilidad interna |
| Feature | 139 | Adecuación funcional, seguridad |
| E2E | 2 | Aceptación / completitud sistema |

## 4. Brechas ISO → evaluation

- **Eficiencia:** k6 no gate CI → C14 observabilidad.
- **Usabilidad:** sin Playwright → Plan_Calidad gap.
- **Seguridad:** REQ-SEC-03 headers pendiente validación HTTP.

## 5. CSV

Detalle subcaracterística ↔ tests: [ISO_25010_Instrumentos.csv](./ISO_25010_Instrumentos.csv).

## 6. Referencias

- [docs/testing/matrix_validacion_middleware.md](../matrix_validacion_middleware.md)
- [docs/evaluation/05_Guia_Puntuacion_Global.md](../../evaluation/05_Guia_Puntuacion_Global.md)
