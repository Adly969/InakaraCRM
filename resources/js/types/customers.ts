import type { User } from './auth';

export const CustomerStatus = {
    Active: 'active',
    Inactive: 'inactive',
    Blacklisted: 'blacklisted',
} as const;

export type CustomerStatusType = (typeof CustomerStatus)[keyof typeof CustomerStatus];

export interface Customer {
    id: number;
    reference_no: string | null;
    name: string;
    company_name: string | null;
    email: string | null;
    phone: string | null;
    type: 'individual' | 'organization';
    status: CustomerStatusType;
    notes: string | null;
    assigned_to: number | User | null;
    created_by: number | User | null;
    updated_by: number | User | null;
    deleted_by: number | User | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;

    // Relationship aliases
    assigned_to_user?: User | null;
    creator?: User | null;
    updater?: User | null;
}
