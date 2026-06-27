# Instrumento — Seguridad (REQ-SEC-*) vs pruebas

**Versión:** 1.0  
**Fecha:** 2026-06-27  
**Fuente requisitos:** [requerimientos.csv](../../Patente/matriz_generada/requerimientos.csv) (REQ-SEC-01–03)  
**Matriz evaluation:** [docs/evaluation/05_Matriz_Seguridad.csv](../../evaluation/05_Matriz_Seguridad.csv) (C11, C12, C16)

## 1. Propósito

Instrumentar la verificación de requisitos **no funcionales de seguridad** mediante tests automatizados, configuración versionada y checklists manuales.

## 2. Resumen REQ-SEC

| ID | Descripción | Estado impl. | Cobertura test | Brecha |
|----|-------------|--------------|----------------|--------|
| REQ-SEC-01 | Auth en todas rutas API | Implementado | Alta — PlatformApiAuthenticationTest + Identity | 1 fallo portal multi-tenant |
| REQ-SEC-02 | Rate limiting APIs | Implementado | Baja — solo config | Sin Feature 429 |
| REQ-SEC-03 | Headers seguridad y CORS | PENDIENTE_VALIDACION | Media — Unit CSP | Sin Feature HTTP headers |

## 3. Procesos BPMN relacionados

- **PROC-005** Autenticación operadores web
- **PROC-006** Autenticación API integradores
- **PROC-015** Gestión incidentes (audit parcial)

## 4. Instrumentos de medición (evaluation §3.5)

| Instrumento | REQ | Test ancla |
|-------------|-----|------------|
| % endpoints protegidos | SEC-01 | PlatformApiAuthenticationTest |
| % casos RBAC | SEC-01 | RoleBasedAuthorizationTest |
| Headers/CORS/rate limit | SEC-02/03 | SecurityHeadersServicesTest + gap |
| Audit writer | C16 | EventAndAuditLogServiceTest |

## 5. CSV

[Instrumentos_Seguridad.csv](./Instrumentos_Seguridad.csv)

## 6. Ejecución

```bash
php vendor/bin/phpunit tests/Feature/Security tests/Feature/Identity tests/Unit/Http/Security
```
