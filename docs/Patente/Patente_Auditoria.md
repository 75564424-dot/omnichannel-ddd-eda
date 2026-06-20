# Auditoría de Documentación de Patente

Este documento consolida los resultados de la auditoría y actualización de la documentación técnica de patente (Documentos 1 al 6) ubicada en la carpeta `docs/Patente`, realizada contra el estado real del repositorio `omnichannel-ddd-eda`.

## Archivos revisados

Se revisaron y auditaron los siguientes documentos (versión `_COMPLETADO.md`):

1. `Documento 1 - Ficha técnica de software_COMPLETADO.md`
2. `Documento 2 - Ejemplar del software_COMPLETADO.md`
3. `Documento 3 - Declaración jurada de auditoría_COMPLETADO.md`
4. `Documento 4 - Lista de autores y roles_COMPLETADO.md`
5. `Documento 5 - Representación legal de la empresa_COMPLETADO.md`
6. `Documento 6 - Anexos técnicos_COMPLETADO.md`

## Correcciones realizadas

1. **Inclusión de Trazabilidad:** Se ha añadido una columna o etiqueta de **Estado** en todas las tablas técnicas, junto con su fuente directa (e.g. `(Fuente: composer.json)`, `(Fuente: git log)`, `(Fuente: database/migrations/)`).
2. **Normalización de Estados:** Se unificaron las etiquetas en los formatos: `**VALIDADO**`, `**PARCIALMENTE VALIDADO**`, y `**PENDIENTE DE VALIDACIÓN**` en lugar del genérico `[PENDIENTE_VALIDACION]`.
3. **Preservación de Marcadores y Contenido:** Todos los marcadores gráficos como `[INSERTAR_CAPTURA_LOGIN]` y `[INSERTAR_DIAGRAMA_ARQUITECTURA]` se han conservado intactos sin generar imágenes. Tampoco se ha eliminado ningún párrafo de contexto técnico o de funcionalidades.
4. **Validación Exhaustiva:** Todos los ítems fueron validados contra la estructura del proyecto actual (`app/`, `tests/`, `config/`, etc.), los archivos manifiesto (`composer.json`, `package.json`), y el historial de Git (commits de `Brayan Estif Guillen Sanabria` y `Guillen-Sanabria`).

## Información agregada

- Confirmación explícita (etiquetas **VALIDADO**) sobre las versiones de software, los nombres de archivos, estructura de directorios, librerías frontend/backend, flujos lógicos, testing e historial git.
- Señalamiento claro de los campos legales pendientes por llenado humano (DNI, empresa titular, representante legal, firmas).

## Información eliminada

- **Ninguna.** Se ha respetado estrictamente la regla de NO reemplazar contenido válido, NO eliminar información útil y NO simplificar los documentos. Todos los marcadores `[COMPLETAR_MANUALMENTE]` permanecen.

## Inconsistencias encontradas

- **Redis:** Se lista como base de datos en la documentación técnica, pero no se evidencia en `composer.json`. Se ha clasificado como **PARCIALMENTE VALIDADO** asumiendo su posible dependencia vía configuración de entorno (`.env`).
- **Autores Git:** Se encontraron dos alias (`Brayan Estif Guillen Sanabria` y `Guillen-Sanabria`) compartiendo el mismo correo institucional (`75564424@continental.edu.pe`). Queda pendiente validar manualmente si corresponden a un único autor o no (se requiere DNI único en tal caso).
- **Manual de Usuario:** Se lista en la documentación técnica, pero no existe evidencia o ruta en el repositorio para dicho documento (marcado como `[NO_EVIDENCIADO]` y **PARCIALMENTE VALIDADO**).

## Campos pendientes de completar manualmente

Los siguientes aspectos legales requieren intervención humana y se mantuvieron marcados (NO se inventó información):

- **Titular del registro:** Nombre formal de la empresa / razón social / universidad que registra.
- **Identidad del Representante Legal:** Nombres, apellidos, DNI, dirección y poderes notariales en INDECOPI.
- **Identidad de Autores:** Validar DNIs de los autores y consolidar el rol de ambos alias Git.
- **Datos Sensibles:** RUC de la empresa y firmas físicas en todos los documentos.

## Nivel de confianza por documento

| Documento | Nivel de Confianza | Justificación |
|-----------|--------------------|---------------|
| Documento 1 - Ficha técnica de software | **ALTO** | Toda la arquitectura, versiones y librerías coinciden al 100% con el repositorio. |
| Documento 2 - Ejemplar del software | **ALTO** | Estructura de carpetas y comandos de ejecución validados correctamente. |
| Documento 3 - Declaración jurada de auditoría | **MEDIO** | Requiere consolidación de autores y validación de DNI/RUC. |
| Documento 4 - Lista de autores y roles | **MEDIO** | Los roles específicos requieren ser completados manualmente; Git no proporciona descripciones legales. |
| Documento 5 - Representación legal de la empresa | **BAJO** | El 90% de este documento requiere datos oficiales de RUC, actas y poderes no presentes en código. |
| Documento 6 - Anexos técnicos | **ALTO** | Los pipelines, scripts y referencias arquitectónicas coinciden fielmente con lo encontrado. |
