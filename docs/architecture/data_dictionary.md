# Data Dictionary (Legacy — Retail OLTP)

> **Deprecado:** Este modelo describe un sistema de retail monolítico y **no refleja** la persistencia actual del middleware omnicanal.  
> Consulte la documentación vigente (v2.0, 2026-06-24):
> - [`er_diagram.md`](er_diagram.md) — diagramas entidad-relación (38 tablas)
> - [`middleware_database_dictionary.md`](middleware_database_dictionary.md) — diccionario completo
> - [`middleware_database_architecture.md`](middleware_database_architecture.md) — arquitectura DDD/EDA

---

## Table: PRODUCT
Represents items sold in the retail system, typically high-rotation fashion/accessory products.

| Field Name   | Data Type | Description                              | Constraints     | Example        |
|--------------|----------|------------------------------------------|-----------------|----------------|
| product_id   | integer  | Unique identifier of the product         | PK, NOT NULL    | 101            |
| name         | string   | Product name                             | NOT NULL        | Leather Bag    |
| category     | string   | Product category or type                 | NOT NULL        | Accessories    |
| collection   | string   | Collection or seasonal grouping          | NULL            | Summer 2025    |
| price        | decimal  | Base selling price                       | NOT NULL        | 59.90          |

---

## Table: CHANNEL
Represents sales channels (e.g., physical store, ecommerce, social media).

| Field Name | Data Type | Description                          | Constraints  | Example      |
|------------|----------|--------------------------------------|--------------|--------------|
| channel_id | integer  | Unique identifier of the channel     | PK, NOT NULL | 1            |
| name       | string   | Channel name                         | NOT NULL     | E-commerce   |
| type       | string   | Type of channel                      | NOT NULL     | Digital      |

---

## Table: STORE
Represents physical store locations.

| Field Name | Data Type | Description                    | Constraints  | Example       |
|------------|----------|--------------------------------|--------------|---------------|
| store_id   | integer  | Unique identifier of the store | PK, NOT NULL | 10            |
| name       | string   | Store name                     | NOT NULL     | Sifrah Mall   |
| location   | string   | Store location                 | NOT NULL     | Huancayo      |

---

## Table: INVENTORY
Stores stock levels per product across stores and channels.

| Field Name         | Data Type | Description                                      | Constraints               | Example     |
|-------------------|----------|--------------------------------------------------|---------------------------|-------------|
| inventory_id      | integer  | Unique identifier of inventory record            | PK, NOT NULL              | 5001        |
| product_id        | integer  | Associated product                               | FK → PRODUCT, NOT NULL    | 101         |
| store_id          | integer  | Associated store (if applicable)                 | FK → STORE, NULL          | 10          |
| channel_id        | integer  | Associated channel                               | FK → CHANNEL, NOT NULL    | 1           |
| stock_quantity    | integer  | Available stock quantity                         | NOT NULL                  | 25          |
| reserved_quantity | integer  | Quantity reserved for pending orders             | NOT NULL                  | 5           |
| last_update       | datetime | Last update timestamp of the inventory           | NOT NULL                  | 2026-04-30  |

---

## Table: CUSTOMER
Represents customers placing orders.

| Field Name   | Data Type | Description                        | Constraints     | Example          |
|--------------|----------|------------------------------------|-----------------|------------------|
| customer_id  | integer  | Unique identifier of the customer  | PK, NOT NULL    | 9001             |
| name         | string   | Full name of the customer          | NOT NULL        | Juan Perez       |
| contact_info | string   | Contact details (email/phone)      | NOT NULL        | juan@email.com   |

---

## Table: ORDER
Represents customer purchase orders.

| Field Name  | Data Type | Description                          | Constraints              | Example      |
|-------------|----------|--------------------------------------|--------------------------|--------------|
| order_id    | integer  | Unique identifier of the order       | PK, NOT NULL             | 3001         |
| customer_id | integer  | Customer who placed the order        | FK → CUSTOMER, NOT NULL  | 9001         |
| order_date  | datetime | Date and time of order               | NOT NULL                 | 2026-04-30   |
| status      | string   | Current order status                 | NOT NULL                 | Confirmed    |
| channel     | string   | Channel used to place the order      | NOT NULL                 | E-commerce   |

---

## Table: ORDER_ITEM
Represents individual products within an order.

| Field Name    | Data Type | Description                          | Constraints             | Example |
|---------------|----------|--------------------------------------|-------------------------|---------|
| order_item_id | integer  | Unique identifier of order item      | PK, NOT NULL            | 1       |
| order_id      | integer  | Associated order                     | FK → ORDER, NOT NULL    | 3001    |
| product_id    | integer  | Product included in the order        | FK → PRODUCT, NOT NULL  | 101     |
| quantity      | integer  | Quantity of the product              | NOT NULL                | 2       |
| price         | decimal  | Price at the moment of purchase      | NOT NULL                | 59.90   |

---

## Table: PAYMENT
Represents payment information for orders.

| Field Name   | Data Type | Description                        | Constraints            | Example     |
|--------------|----------|------------------------------------|------------------------|-------------|
| payment_id   | integer  | Unique identifier of payment       | PK, NOT NULL           | 7001        |
| order_id     | integer  | Associated order                   | FK → ORDER, NOT NULL   | 3001        |
| amount       | decimal  | Payment amount                     | NOT NULL               | 119.80      |
| status       | string   | Payment status                     | NOT NULL               | Paid        |
| payment_date | datetime | Date of payment                    | NOT NULL               | 2026-04-30  |

---

## Table: SHIPMENT
Represents shipping and delivery information.

| Field Name    | Data Type | Description                         | Constraints            | Example     |
|---------------|----------|-------------------------------------|------------------------|-------------|
| shipment_id   | integer  | Unique identifier of shipment       | PK, NOT NULL           | 8001        |
| order_id      | integer  | Associated order                    | FK → ORDER, NOT NULL   | 3001        |
| status        | string   | Shipment status                     | NOT NULL               | Shipped     |
| shipment_date | datetime | Date of shipment                    | NULL                   | 2026-05-01  |
| delivery_date | datetime | Date of delivery                    | NULL                   | 2026-05-03  |

---

## Table: RETURN
Represents product returns and post-sale processes.

| Field Name   | Data Type | Description                          | Constraints            | Example     |
|--------------|----------|--------------------------------------|------------------------|-------------|
| return_id    | integer  | Unique identifier of return          | PK, NOT NULL           | 6001        |
| order_id     | integer  | Associated order                     | FK → ORDER, NOT NULL   | 3001        |
| product_id   | integer  | Product being returned               | FK → PRODUCT, NOT NULL | 101         |
| reason       | string   | Reason for return                    | NOT NULL               | Defective   |
| status       | string   | Return status                        | NOT NULL               | Pending     |
| request_date | datetime | Date of return request               | NOT NULL               | 2026-05-05  |

---

## Table: INVENTORY_EVENT
Tracks inventory changes for event-driven architecture.

| Field Name | Data Type | Description                              | Constraints            | Example     |
|------------|----------|------------------------------------------|------------------------|-------------|
| event_id   | integer  | Unique identifier of the event           | PK, NOT NULL           | 10001       |
| product_id | integer  | Related product                          | FK → PRODUCT, NOT NULL | 101         |
| event_type | string   | Type of inventory event                  | NOT NULL               | StockUpdate |
| quantity   | integer  | Quantity affected                        | NOT NULL               | -2          |
| event_date | datetime | Timestamp of the event                   | NOT NULL               | 2026-04-30  |

---

## Relationships

- A CUSTOMER places one or many ORDER records.  
- An ORDER contains multiple ORDER_ITEM, each linked to a PRODUCT.  
- ORDER is associated with one PAYMENT and one SHIPMENT.  
- ORDER may generate multiple RETURN records.  
- PRODUCT is tracked in INVENTORY across STORE and CHANNEL.  
- PRODUCT generates INVENTORY_EVENT entries for stock traceability.  

---

## Notes

- The model supports omnichannel retail operations (physical + digital channels).  
- Inventory includes reserved_quantity to prevent overselling.  
- INVENTORY_EVENT enables event-driven tracking of stock changes.  
- The order lifecycle is fully traceable: order → payment → shipment → return.  

---