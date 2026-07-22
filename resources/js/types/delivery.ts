import { Warehouse } from './warehouse';

export type DeliveryOrderStatus = 'draft' | 'approved' | 'partially_shipped' | 'shipped' | 'partially_delivered' | 'delivered' | 'cancelled';
export type ShipmentStatus = 'pending_dispatch' | 'in_transit' | 'delivered' | 'failed_delivery' | 'returned' | 'cancelled';
export type CourierType = 'internal' | 'third_party' | 'expedition' | 'pickup';

export interface Carrier {
    id: number;
    code: string;
    name: string;
    status: 'active' | 'inactive';
}

export interface Driver {
    id: number;
    name: string;
    phone: string;
    vehicle_plate_no: string | null;
    status: 'active' | 'inactive';
}

export interface DeliveryOrderItem {
    id: number;
    delivery_order_id: number;
    sales_order_item_id: number;
    sales_order_item?: { id: number; description: string; quantity: number };
    sku: string;
    description: string;
    quantity_requested: number | string;
    quantity_shipped: number | string;
    quantity_delivered: number | string;
    unit: string;
    item_specifications_snapshot: {
        weight: number;
        volume: number;
        dimensions: string;
    };
    sort_order: number;
}

export interface DeliveryOrder {
    id: number;
    reference_no: string;
    sales_order_id: number;
    sales_order?: { id: number; reference_no: string };
    warehouse_id: number;
    warehouse?: Warehouse;
    customer_id: number;
    customer?: { id: number; name: string };
    status: DeliveryOrderStatus;
    shipping_address_snapshot: {
        name: string;
        address: string;
    };
    billing_address_snapshot: {
        name: string;
        address: string;
    };
    notes: string | null;
    approved_by: number | null;
    approved_at: string | null;
    items?: DeliveryOrderItem[];
    shipments?: Shipment[];
    events?: DeliveryEvent[];
    created_at?: string;
    updated_at?: string;
}

export interface ShipmentItem {
    id: number;
    shipment_id: number;
    delivery_order_item_id: number;
    delivery_order_item?: DeliveryOrderItem;
    quantity_shipped: number | string;
}

export interface Shipment {
    id: number;
    delivery_order_id: number;
    delivery_order?: DeliveryOrder;
    reference_no: string;
    carrier_id: number | null;
    carrier?: Carrier | null;
    driver_id: number | null;
    driver?: Driver | null;
    courier_type: CourierType;
    tracking_number: string | null;
    status: ShipmentStatus;
    estimated_cost: number | string;
    actual_cost: number | string;
    currency: string;
    exchange_rate: number | string;
    estimated_delivery_date: string | null;
    actual_delivery_date: string | null;
    items?: ShipmentItem[];
    created_at?: string;
}

export interface DeliveryEvent {
    id: number;
    delivery_order_id: number;
    shipment_id: number | null;
    event_type: string;
    event_data: Record<string, any>;
    ip_address: string | null;
    user_agent: string | null;
    created_by: number;
    creator?: { id: number; name: string };
    created_at: string;
}
