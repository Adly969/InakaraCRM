import type { User } from './auth';
import type { Customer } from './customers';
import type { Lead } from './leads';

export const QuotationStatus = {
    Draft: 'draft',
    Sent: 'sent',
    Accepted: 'accepted',
    Rejected: 'rejected',
} as const;

export type QuotationStatusType = (typeof QuotationStatus)[keyof typeof QuotationStatus];

export interface QuotationItem {
    id: number;
    quotation_id: number;
    description: string;
    quantity: number;
    unit: string;
    unit_price: number;
    total_price: number;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

export interface Quotation {
    id: number;
    reference_no: string | null;
    lead_id: number | null;
    customer_id: number | null;
    subject: string;
    revision: number;
    status: QuotationStatusType;
    valid_until: string;
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
    lead?: Lead | null;
    items?: QuotationItem[];
    assigned_to_user?: User | null;
    creator?: User | null;
    updater?: User | null;
}
