# 5.2 Arquitectura Orientada a Eventos (EDA)

## 1. Descripción General

La arquitectura está basada en **Event-Driven Architecture (EDA)**, donde los sistemas se comunican mediante eventos desde su generación hasta su consumo por servicios de dominio.

El flujo permite:
- Procesamiento asíncrono
- Desacoplamiento entre sistemas
- Escalabilidad y resiliencia

---

## 2. Fuentes de Eventos (Productores)

Los eventos son generados por:

### 2.1 Punto de Venta (POS)
- Registra ventas
- Procesa devoluciones
- Consulta inventario

### 2.2 E-commerce
- Genera pedidos online
- Maneja cancelaciones
- Procesa pagos

### 2.3 ERP
- Gestiona datos maestros
- Maneja compras
- Controla transferencias

### 2.4 Aplicaciones Móviles
- Consultas de usuario
- Reservas
- Actualización de estados

---

## 3. Generación de Eventos

Cada acción relevante genera un evento de dominio.

### Ejemplo de evento

```json
{
  "evento": "PedidoCreado",
  "idPedido": "P-000123",
  "fecha": "2025-05-28T10:15:00Z",
  "canal": "E-COMMERCE",
  "clienteId": "C-456",
  "total": 129.90
}