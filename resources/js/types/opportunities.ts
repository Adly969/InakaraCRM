import type { User } from './auth';
import type { Customer } from './customers';
import type { Lead } from './leads';

export const OpportunityStatus = {
    Qualification: 'qualification',
    Discovery: 'discovery',
    Proposal: 'proposal',
    Negotiation: 'negotiation',
    VerbalCommit: 'verbal_commit',
    Won: 'won',
    Lost: 'lost',
} as const;

export type OpportunityStatusType = (typeof OpportunityStatus)[keyof typeof OpportunityStatus];

export interface CrmPipelineDefinition {
    id: number;
    name: string;
    description: string | null;
    is_default: boolean;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface CrmPipelineStage {
    id: number;
    pipeline_definition_id: number;
    name: string;
    probability: number;
    stage_sequence: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface CrmLossReason {
    id: number;
    name: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface CrmOpportunityCompetitor {
    id: number;
    opportunity_id: number;
    competitor_name: string;
    strengths: string | null;
    weaknesses: string | null;
    created_at: string;
}

export interface CrmStageHistory {
    id: number;
    opportunity_id: number;
    from_stage_id: number;
    to_stage_id: number;
    changed_by: number;
    duration_in_seconds: number;
    created_at: string;
    from_stage?: CrmPipelineStage;
    to_stage?: CrmPipelineStage;
    changed_by_user?: User;
}

export interface CrmActivity {
    id: number;
    activity_type: 'phone_call' | 'email' | 'meeting' | 'site_visit' | 'note' | 'whatsapp';
    subject: string;
    description: string | null;
    start_time: string;
    end_time: string | null;
    status: 'pending' | 'completed' | 'cancelled';
    lead_id: number | null;
    opportunity_id: number | null;
    created_by: number;
    created_at: string;
    updated_at: string;
    creator?: User;
}

export interface Opportunity {
    id: number;
    lead_id: number | null;
    customer_id: number;
    title: string;
    pipeline_stage_id: number;
    status: OpportunityStatusType;
    deal_value: number;
    expected_close_date: string;
    expected_revenue: number; // dynamically computed in backend, sent in JSON response
    loss_reason_id: number | null;
    loss_notes: string | null;
    assigned_to: number;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;

    // Relationships
    lead?: Lead | null;
    customer?: Customer;
    stage?: CrmPipelineStage;
    loss_reason?: CrmLossReason | null;
    assigned_to_user?: User;
    creator?: User | null;
    competitors?: CrmOpportunityCompetitor[];
    stage_histories?: CrmStageHistory[];
    activities?: CrmActivity[];
}
