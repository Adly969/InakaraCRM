export interface Payment {
    id: number;
    reference_no: string | null;
    customer_id: number;
    customer?: {
        id: number;
        name: string;
    };
    status: 'draft' | 'submitted' | 'verified' | 'finance_supervisor_approved' | 'finance_manager_approved' | 'approved' | 'posted' | 'cancelled' | 'reversed';
    payment_date: string;
    payment_method: string;
    amount: number | string;
    allocated_amount: number | string;
    unallocated_amount: number | string;
    base_currency: string;
    transaction_currency: string;
    exchange_rate: number | string;
    bank_name: string | null;
    bank_account_no: string | null;
    cheque_no: string | null;
    transaction_ref: string | null;
    notes: string | null;
    cancellation_reason: string | null;
    reversal_reason: string | null;
    allocations?: PaymentAllocation[];
    events?: PaymentEvent[];
    attachments?: PaymentAttachment[];
    histories?: PaymentAllocationHistory[];
    created_by: number;
    creator?: { id: number; name: string };
    submitted_by: number | null;
    submitted_at: string | null;
    verified_by: number | null;
    verified_at: string | null;
    approved_by: number | null;
    approved_at: string | null;
    posted_by: number | null;
    posted_at: string | null;
    reversed_by: number | null;
    reversed_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface PaymentAllocation {
    id: number;
    payment_id: number;
    invoice_id: number;
    invoice?: {
        id: number;
        reference_no: string;
        total_amount: number | string;
        outstanding_balance: number | string;
        due_date: string;
    };
    amount: number | string;
    notes: string | null;
}

export interface PaymentAttachment {
    id: number;
    payment_id: number;
    file_path: string;
    file_name: string;
    file_size: number;
    mime_type: string;
    uploaded_by: number;
    uploader?: { id: number; name: string };
    created_at: string;
}

export interface PaymentAllocationHistory {
    id: number;
    payment_allocation_id: number;
    payment_id: number;
    invoice_id: number;
    before_amount: number | string;
    after_amount: number | string;
    modified_by: number;
    modifier?: { id: number; name: string };
    reason: string;
    created_at: string;
}

export interface PaymentEvent {
    id: number;
    payment_id: number;
    event_type: string;
    event_data: Record<string, any> | null;
    ip_address: string | null;
    user_agent: string | null;
    created_by: number;
    creator?: { id: number; name: string };
    created_at: string;
}
