import type { User } from './auth';

export const LeadSource = {
    Referral: 'referral',
    Marketing: 'marketing',
    WalkIn: 'walk_in',
    Phone: 'phone',
    Digital: 'digital',
    Event: 'event',
} as const;

export type LeadSourceType = (typeof LeadSource)[keyof typeof LeadSource];

export const LeadStatus = {
    New: 'new',
    Assigned: 'assigned',
    Contacted: 'contacted',
    Qualified: 'qualified',
    Converted: 'converted',
    Disqualified: 'disqualified',
} as const;

export type LeadStatusType = (typeof LeadStatus)[keyof typeof LeadStatus];

export interface Lead {
    id: number;
    reference_no: string | null;
    name: string;
    company_name: string | null;
    email: string | null;
    phone: string | null;
    website: string | null;
    job_title: string | null;
    source: LeadSourceType;
    campaign_source: string | null;
    status: LeadStatusType;
    priority: 'low' | 'medium' | 'high';
    heat_score: 'cold' | 'warm' | 'hot';
    disqualification_reason: string | null;
    assigned_to: number | User | null;
    created_by: number | User | null;
    updated_by: number | User | null;
    deleted_by: number | User | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;

    // Explicit relationship aliases if mapped in resources/js
    assigned_to_user?: User | null;
    creator?: User | null;
    updater?: User | null;
}
