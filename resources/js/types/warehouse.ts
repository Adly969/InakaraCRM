export type WarehouseType = 'central' | 'transit' | 'damaged' | 'return';
export type WarehouseStatus = 'active' | 'inactive';
export type GoodsReceiptStatus = 'draft' | 'received' | 'cancelled';
export type GoodsIssueStatus = 'draft' | 'issued' | 'cancelled';
export type StockAdjustmentStatus = 'draft' | 'approved' | 'rejected';
export type StockAdjustmentType = 'addition' | 'deduction';

export interface Warehouse {
    id: number;
    code: string;
    name: string;
    type: WarehouseType;
    is_default: boolean;
    status: WarehouseStatus;
    address: string | null;
    manager_id: number | null;
    manager?: { id: number; name: string } | null;
    created_at?: string;
    updated_at?: string;
}

export interface InventoryItem {
    id: number;
    warehouse_id: number;
    warehouse?: Warehouse;
    product_id: number | null;
    sku: string;
    name: string;
    description: string | null;
    quantity_current: number | string;
    quantity_reserved: number | string;
    unit: string;
    avg_cost_price: number | string;
    created_at?: string;
    updated_at?: string;
}

export interface InventoryTransaction {
    id: number;
    inventory_item_id: number;
    inventory_item?: InventoryItem;
    warehouse_id: number;
    warehouse?: Warehouse;
    transaction_type: 'receipt' | 'issue' | 'adjustment_in' | 'adjustment_out';
    reference_type: string;
    reference_id: number;
    movement_direction: 'in' | 'out' | 'none';
    quantity_before: number | string;
    quantity_change: number | string;
    quantity_after: number | string;
    reserved_before: number | string;
    reserved_after: number | string;
    cost_price: number | string;
    total_value_change: number | string;
    current_avg_cost_after: number | string;
    notes: string | null;
    created_by: number | null;
    creator?: { id: number; name: string } | null;
    created_at?: string;
}

export interface InventoryReservation {
    id: number;
    sales_order_id: number;
    sales_order?: { id: number; reference_no: string };
    inventory_item_id: number;
    quantity_reserved: number | string;
    quantity_released: number | string;
    status: 'active' | 'released' | 'cancelled';
    created_at?: string;
}

export interface GoodsReceiptItem {
    id: number;
    goods_receipt_id: number;
    production_order_item_id: number | null;
    sku: string;
    description: string;
    quantity_received: number | string;
    unit: string;
    unit_cost: number | string;
    sort_order: number;
}

export interface GoodsReceipt {
    id: number;
    reference_no: string;
    production_order_id: number | null;
    production_order?: { id: number; reference_no: string } | null;
    warehouse_id: number;
    warehouse?: Warehouse;
    received_date: string;
    status: GoodsReceiptStatus;
    notes: string | null;
    remark: string | null;
    items?: GoodsReceiptItem[];
    created_at?: string;
}

export interface GoodsIssueItem {
    id: number;
    goods_issue_id: number;
    sales_order_item_id: number | null;
    sku: string;
    description: string;
    quantity_issued: number | string;
    unit: string;
    sort_order: number;
}

export interface GoodsIssue {
    id: number;
    reference_no: string;
    sales_order_id: number | null;
    sales_order?: { id: number; reference_no: string } | null;
    warehouse_id: number;
    warehouse?: Warehouse;
    issued_date: string;
    status: GoodsIssueStatus;
    notes: string | null;
    remark: string | null;
    items?: GoodsIssueItem[];
    created_at?: string;
}

export interface StockAdjustmentItem {
    id: number;
    stock_adjustment_id: number;
    inventory_item_id: number;
    inventory_item?: InventoryItem;
    type: StockAdjustmentType;
    quantity_adjusted: number | string;
    unit_cost: number | string;
    sort_order: number;
}

export interface StockAdjustment {
    id: number;
    reference_no: string;
    warehouse_id: number;
    warehouse?: Warehouse;
    adjustment_date: string;
    status: StockAdjustmentStatus;
    notes: string;
    approval_note: string | null;
    approver?: { id: number; name: string } | null;
    items?: StockAdjustmentItem[];
    created_at?: string;
}
