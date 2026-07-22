import type { User } from './auth';
import type { Customer } from './customers';
import type { Quotation } from './quotations';
import type { ProductionOrder } from './production-orders';

export const SalesOrderStatus = {
    Draft: 'draft',
    Confirmed: 'confirmed',
    Cancelled: 'cancelled',
} as const;

export type SalesOrderStatusType = (typeof SalesOrderStatus)[keyof typeof SalesOrderStatus];

export interface SalesOrderItem {
    id: number;
    sales_order_id: number;
    description: string;
    quantity: number;
    unit: string;
    unit_price: number;
    total_price: number;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

export interface SalesOrder {
    id: number;
    reference_no: string | null;
    quotation_id: number | null;
    customer_id: number;
    subject: string;
    status: SalesOrderStatusType;
    delivery_terms: string | null;
    cancellation_reason: string | null;
    notes: string | null;
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
    quotation?: Quotation | null;
    items?: SalesOrderItem[];
    assigned_to_user?: User | null;
    creator?: User | null;
    updater?: User | null;
    production_order?: ProductionOrder | null;
}
