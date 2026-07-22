export type InvoiceStatus = 'draft' | 'approved' | 'issued' | 'overdue' | 'cancelled' | 'void';

export interface InvoiceItem {
    id: number;
    invoice_id: number;
    sales_order_item_id: number | null;
    sales_order_item?: { id: number; description: string; quantity: number };
    delivery_order_item_id: number | null;
    delivery_order_item?: { id: number; description: string; quantity_requested: number };
    sku: string;
    description: string;
    quantity: number | string;
    unit: string;
    unit_price: number | string;
    discount_percentage: number | string;
    discount_amount: number | string;
    tax_percentage: number | string;
    tax_amount: number | string;
    total_amount: number | string;
    sort_order: number;
}

export interface InvoiceAdjustment {
    id: number;
    invoice_id: number;
    type: string;
    description: string;
    amount: number | string;
    is_taxable: boolean;
}

export interface InvoiceEvent {
    id: number;
    invoice_id: number;
    event_type: string;
    event_data: Record<string, any> | null;
    ip_address: string | null;
    user_agent: string | null;
    created_by: number;
    creator?: { id: number; name: string };
    created_at: string;
}

export interface Invoice {
    id: number;
    reference_no: string | null;
    sales_order_id: number | null;
    sales_order?: { id: number; reference_no: string };
    delivery_order_id: number | null;
    delivery_order?: { id: number; reference_no: string };
    customer_id: number;
    customer?: { id: number; name: string };
    company_id: number | null;
    branch_id: number | null;
    status: InvoiceStatus;
    invoice_date: string;
    due_date: string;
    payment_term_code: string;
    subtotal: number | string;
    tax_amount: number | string;
    discount_amount: number | string;
    adjustment_amount: number | string;
    total_amount: number | string;
    outstanding_balance: number | string;
    currency: string;
    exchange_rate: number | string;
    billing_address_snapshot: {
        name: string;
        address: string;
    };
    shipping_address_snapshot: {
        name: string;
        address: string;
    };
    notes: string | null;
    void_reason: string | null;
    created_by: number;
    approved_by: number | null;
    approved_at: string | null;
    items?: InvoiceItem[];
    adjustments?: InvoiceAdjustment[];
    events?: InvoiceEvent[];
    created_at?: string;
    updated_at?: string;
}
