import type { User } from './auth';
import type { Customer } from './customers';
import type { SalesOrder } from './sales-orders';

export const ProductionOrderStatus = {
    Draft: 'draft',
    Scheduled: 'scheduled',
    InProduction: 'in_production',
    QualityControl: 'quality_control',
    Completed: 'completed',
    Cancelled: 'cancelled',
} as const;

export type ProductionOrderStatusType = (typeof ProductionOrderStatus)[keyof typeof ProductionOrderStatus];

export const ProductionPriority = {
    Low: 'low',
    Normal: 'normal',
    High: 'high',
    Urgent: 'urgent',
} as const;

export type ProductionPriorityType = (typeof ProductionPriority)[keyof typeof ProductionPriority];

export interface ProductionOrderItem {
    id: number;
    production_order_id: number;
    sales_order_item_id: number | null;
    description: string;
    quantity: number;
    unit: string;
    unit_price: number;
    total_price: number;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

export interface ProductionOrderLog {
    id: number;
    production_order_id: number;
    status_from: string | null;
    status_to: string;
    note: string | null;
    created_by: number | null;
    created_at: string;
    creator?: User | null;
}

export interface ProductionOrder {
    id: number;
    reference_no: string | null;
    sales_order_id: number;
    customer_id: number;
    subject: string;
    status: ProductionOrderStatusType;
    priority: ProductionPriorityType;
    target_completion_date: string | null;
    actual_completion_date: string | null;
    started_at: string | null;
    completed_at: string | null;
    estimated_hours: number | null;
    actual_hours: number | null;
    production_notes: string | null;
    cancellation_reason: string | null;
    currency: string;
    tax_rate: number;
    subtotal: number;
    tax_amount: number;
    total_amount: number;
    assigned_to: number | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;

    // Relations
    customer?: Customer | null;
    sales_order?: SalesOrder | null;
    items?: ProductionOrderItem[];
    logs?: ProductionOrderLog[];
    assigned_to_user?: User | null;
    creator?: User | null;
    updater?: User | null;
}
