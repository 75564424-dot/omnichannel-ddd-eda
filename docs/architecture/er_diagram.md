erDiagram

    PRODUCT {
        int product_id PK
        string name
        string category
        string collection
        decimal price
    }

    CHANNEL {
        int channel_id PK
        string name
        string type
    }

    STORE {
        int store_id PK
        string name
        string location
    }

    INVENTORY {
        int inventory_id PK
        int product_id FK
        int store_id FK
        int channel_id FK
        int stock_quantity
        int reserved_quantity
        datetime last_update
    }

    CUSTOMER {
        int customer_id PK
        string name
        string contact_info
    }

    ORDER {
        int order_id PK
        int customer_id FK
        datetime order_date
        string status
        string channel
    }

    ORDER_ITEM {
        int order_item_id PK
        int order_id FK
        int product_id FK
        int quantity
        decimal price
    }

    PAYMENT {
        int payment_id PK
        int order_id FK
        decimal amount
        string status
        datetime payment_date
    }

    SHIPMENT {
        int shipment_id PK
        int order_id FK
        string status
        datetime shipment_date
        datetime delivery_date
    }

    RETURN {
        int return_id PK
        int order_id FK
        int product_id FK
        string reason
        string status
        datetime request_date
    }

    INVENTORY_EVENT {
        int event_id PK
        int product_id FK
        string event_type
        int quantity
        datetime event_date
    }

    ORDER ||--o{ ORDER_ITEM : contains
    PRODUCT ||--o{ ORDER_ITEM : included_in
    CUSTOMER ||--o{ ORDER : places
    ORDER ||--|| PAYMENT : has
    ORDER ||--|| SHIPMENT : generates
    ORDER ||--o{ RETURN : may_have
    PRODUCT ||--o{ INVENTORY : tracked_in
    STORE ||--o{ INVENTORY : holds
    CHANNEL ||--o{ INVENTORY : visible_in
    PRODUCT ||--o{ INVENTORY_EVENT : triggers