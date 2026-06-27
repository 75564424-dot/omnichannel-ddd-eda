# Instrumento — ISO/IEC 29119 (proceso de pruebas de software)

**Versión:** 1.0  
**Fecha:** 2026-06-27  
**Norma de referencia:** ISO/IEC 29119 (procesos y documentación de testing)  
**Implementación en repo:** `phpunit.xml` — suites Unit, Integration, Feature, E2E

## 1. Propósito

Documentar los **niveles de prueba** del proyecto según ISO 29119-4 (técnicas de diseño de pruebas) y mapearlos a carpetas, objetivos, criterios de entrada/salida y artefactos.

## 2. Niveles definidos en el proyecto

| Nivel ISO 29119 | Suite Laravel | Carpeta | Métodos | Objetivo |
|-----------------|---------------|---------|---------|----------|
| Prueba de componente (unit) | Unit | `tests/Unit` | 200 | Validar unidades aisladas (validators, presenters, policies) |
| Prueba de integración | Integration | `tests/Integration` | 21 | Validar colaboración entre componentes (bus, trace, tenant seed) |
| Prueba de sistema | Feature | `tests/Feature` | 139 | Validar API/HTTP y flujos completos vía aplicación |
| Prueba de aceptación | E2E + ops | `tests/E2E` + scripts | 2 + smoke | Certificar instancia tipo cliente y pre-GO staging |

## 3. Criterios de entrada/salida por nivel

### Unit
- **Entrada:** código compilable, `composer install`
- **Salida:** 0 failures en 200 métodos
- **Artefacto:** `unit_catalogo_autogenerado.md`

### Integration
- **Entrada:** SQLite :memory:, `QUEUE_CONNECTION=sync`
- **Salida:** 0 failures (actual: 1 — InstanceTenantSeedingIntegrationTest)
- **Artefacto:** `integration_catalogo_autogenerado.md`

### Feature (sistema)
- **Entrada:** migraciones RefreshDatabase, config security test
- **Salida:** 0 failures (actual: 1 — OperatorLoginTest)
- **Artefacto:** `feature_catalogo_autogenerado.md`

### Aceptación
- **Entrada:** catálogo alineado, fixture cliente
- **Salida:** E2E verde + `simulate-client-smoke.sh` OK
- **Artefacto:** `e2e_catalogo_autogenerado.md`, Checklist_PreDespliegue

## 4. Trazabilidad evaluation

| Nivel | Matrices evaluation |
|-------|---------------------|
| Unit/Integration | 02_Middleware, 08_Calidad |
| Feature | 03_Integracion, 05_Seguridad, 04_Observabilidad |
| E2E | 10_Matriz_Aceptacion_Final (A02, A08) |

## 5. CSV

[ISO_29119_Instrumentos.csv](./ISO_29119_Instrumentos.csv)

## 6. Comandos

```bash
php vendor/bin/phpunit --testsuite Unit
php vendor/bin/phpunit --testsuite Integration
php vendor/bin/phpunit --testsuite Feature
php vendor/bin/phpunit --testsuite E2E
composer test   # suite completa
```
