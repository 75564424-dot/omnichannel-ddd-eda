# Guia de Puntuacion Global

## 1. Proposito

Este documento define como convertir evaluaciones por criterio en un indice global del proyecto.

La puntuacion global debe reflejar:

- peso por dominio;
- criticidad;
- dependencias;
- impacto;
- madurez;
- riesgo residual.

## 2. Principio general

No basta con promediar.

Un criterio de baja puntuacion en seguridad o middleware puede tener mas peso que varios criterios de valor medio en otras areas.

## 3. Componentes de la puntuacion

### 3.1 Puntaje por criterio

Cada criterio recibe un valor entre `0` y `5`.

### 3.2 Peso por criterio

Cada criterio tiene un peso especifico en la matriz de dominio.

### 3.3 Puntaje por dominio

```text
puntaje_dominio = suma(puntaje_criterio * peso_criterio) / suma(peso_criterio)
```

### 3.4 Peso por dominio

El dominio no pesa igual que los demas.
El peso del dominio depende de su criticidad para la operacion y la arquitectura.

## 4. Propuesta de pesos de dominio

Los pesos siguientes son recomendados para este proyecto, porque la documentacion existente trata esos dominios como estructurales.

| Dominio | Peso sugerido | Motivo |
|---|---|---|
| Seguridad | 1.5 | Protege toda la superficie de acceso y control |
| Middleware | 1.4 | Es el nucleo operativo del sistema |
| Observabilidad | 1.3 | Permite diagnostico y operacion |
| Operacion | 1.3 | Afecta despliegue, continuidad y recuperación |
| Arquitectura | 1.2 | Sostiene desacoplamiento y evolucion |
| Integracion | 1.1 | Habilita el valor omnicanal |
| Calidad | 1.1 | Sostiene cambios y contratos |
| IA | 0.8 | Aporta valor metodologico, pero no debe dominar la decision final |

Si el equipo cambia el contexto de negocio, estos pesos pueden ajustarse, pero deben quedar documentados.

## 5. Factores de ajuste

### 5.1 Criticidad

Si un dominio tiene riesgo critico abierto, su puntaje ajustado debe bajar.

Ejemplo:

- seguridad con endpoints publicos;
- middleware sin trazabilidad;
- operacion sin capacidad de recovery.

### 5.2 Dependencias

Un dominio puede verse penalizado si depende de una capacidad que aun no existe.

### 5.3 Riesgo

Si una brecha produce un riesgo alto sin mitigacion, el dominio no debe considerarse maduro aunque el promedio sea aceptable.

### 5.4 Madurez

La madurez refleja consistencia y estabilidad, no solo presencia de documentos.

## 6. Formula recomendada del indice global

```text
indice_global = normalizar( suma(puntaje_dominio * peso_dominio * factor_criticidad * factor_madurez - penalizacion_riesgo) )
```

Donde:

- `puntaje_dominio` esta en escala `0-5`;
- `peso_dominio` esta en escala relativa;
- `factor_criticidad` ajusta dominios sensibles;
- `factor_madurez` ajusta dominios estables o inmaduros;
- `penalizacion_riesgo` reduce el indice si hay riesgos abiertos;
- `normalizar` convierte el resultado final a `0-100`.

## 7. Metodo practico simplificado

Para operar de forma simple dentro del framework:

1. calcular el promedio ponderado por criterio;
2. calcular el promedio ponderado por dominio;
3. aplicar peso de dominio;
4. aplicar penalizacion por criticidad y riesgo;
5. normalizar a `0-100`.

## 8. Umbrales del indice global

- `0-20` = Critico
- `20-40` = Muy deficiente
- `40-60` = Aceptable
- `60-75` = Bueno
- `75-90` = Muy bueno
- `90-100` = Excelente

## 9. Subindices obligatorios

El framework debe producir al menos estos indices secundarios:

### 9.1 Indice de Arquitectura

Usa principalmente:

- `C01`
- `C02`
- `C03`
- `C04`
- `C27`

### 9.2 Indice de Calidad

Usa principalmente:

- `C24`
- `C25`
- `C26`

### 9.3 Indice de Seguridad

Usa principalmente:

- `C11`
- `C12`
- `C16`

### 9.4 Indice de Observabilidad

Usa principalmente:

- `C13`
- `C14`
- `C15`

### 9.5 Indice de IA

Usa principalmente:

- `C21`
- `C22`
- `C23`

### 9.6 Indice de Evolucion

Usa:

- `C03`
- `C04`
- `C27`
- `C28`
- `11_Matriz_Evolucion.csv`

### 9.7 Indice de Mantenibilidad

Usa principalmente:

- `C04`
- `C24`
- `C25`
- `C26`
- `C28`

## 10. Regla de bloqueo critico

Aunque el indice global sea aceptable, no debe declararse aceptacion plena si existe alguna de estas situaciones:

- seguridad con brechas criticas abiertas;
- observabilidad sin trazabilidad basica;
- middleware sin persistencia o resiliencia minima;
- operacion sin capacidad de despliegue o recovery.

## 11. Lectura del resultado

### 11.1 Critico

El sistema no debe avanzar sin remediacion.

### 11.2 Muy deficiente

Existen capacidades parciales, pero el sistema aun no es confiable.

### 11.3 Aceptable

El sistema cumple un minimo util, pero necesita consolidacion.

### 11.4 Bueno

El sistema ya puede sostener la operacion y la evolucion con control.

### 11.5 Muy bueno

La plataforma muestra madurez y estabilidad altas.

### 11.6 Excelente

El proyecto demuestra consistencia, trazabilidad y capacidad de evolucion.

## 12. Ejemplo conceptual

Si seguridad y observabilidad estan altas, pero middleware esta baja:

- el indice global baja de forma importante;
- la aceptacion final no debe ser plena;
- la prioridad de mejora va a middleware y trazabilidad.

## 13. Relacion con la documentacion existente

La documentacion actual ya define:

- ponderacion por criterio;
- puntaje de `0` a `5`;
- umbrales de madurez;
- decision ejecutiva por dominio.

Este documento no lo contradice.
Lo organiza en un indice completo y operativo.

## 14. Referencias

- [docs/evaluation/09_Matriz_Madurez_Global.csv](09_Matriz_Madurez_Global.csv)
- [docs/evaluation/10_Matriz_Aceptacion_Final.csv](10_Matriz_Aceptacion_Final.csv)
- [docs/evaluation/Middleware_Acceptance_Evaluation_Framework.md](Middleware_Acceptance_Evaluation_Framework.md)
- [docs/production/Plan_Seguridad.md](../production/Plan_Seguridad.md)
- [docs/production/Plan_Observabilidad.md](../production/Plan_Observabilidad.md)
- [docs/production/Plan_Middleware.md](../production/Plan_Middleware.md)
- [docs/production/Plan_Resiliencia.md](../production/Plan_Resiliencia.md)
- [docs/production/Plan_Cloud.md](../production/Plan_Cloud.md)

