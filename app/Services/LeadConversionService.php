<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Enums\OpportunityStatus;
use App\Events\LeadConverted;
use App\Events\OpportunityCreated;
use App\Models\CrmPipelineDefinition;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class LeadConversionService
{
    /**
     * Convert a qualified lead into an opportunity.
     *
     * @param  array<string, mixed>  $data  Must include customer_id, title, deal_value, expected_close_date.
     *
     * @throws DomainException If lead is not in Qualified status.
     */
    public function convert(Lead $lead, array $data, User $converter): Opportunity
    {
        if ($lead->status !== LeadStatus::Qualified) {
            throw new DomainException('Only qualified leads can be converted to opportunities.');
        }

        return DB::transaction(function () use ($lead, $data, $converter) {
            // Lock the lead row for update to prevent concurrent conversions
            $lockedLead = Lead::where('id', $lead->id)->lockForUpdate()->firstOrFail();

            if ($lockedLead->status !== LeadStatus::Qualified) {
                throw new DomainException('Lead was updated by another process. Conversion cancelled.');
            }

            // Resolve the first active stage of the default pipeline
            $defaultPipeline = CrmPipelineDefinition::where('is_default', true)->first();
            $firstStage = $defaultPipeline?->stages()->where('is_active', true)->orderBy('stage_sequence')->first();

            if (! $firstStage) {
                throw new DomainException('No active pipeline stages configured. Seed pipeline definitions first.');
            }

            $opportunity = new Opportunity;
            $opportunity->lead_id = $lockedLead->id;
            $opportunity->customer_id = $data['customer_id'];
            $opportunity->title = $data['title'];
            $opportunity->pipeline_stage_id = $firstStage->id;
            $opportunity->status = OpportunityStatus::Qualification;
            $opportunity->deal_value = $data['deal_value'];
            $opportunity->expected_close_date = $data['expected_close_date'];
            $opportunity->assigned_to = $lockedLead->assigned_to ?? $converter->id;
            $opportunity->created_by = $converter->id;

            // Replicate tenant isolation fields from lead
            $opportunity->company_id = $lockedLead->company_id;
            $opportunity->branch_id = $lockedLead->branch_id;

            $opportunity->save();

            // Mark lead as converted
            $lockedLead->status = LeadStatus::Converted;
            $lockedLead->updated_by = $converter->id;
            $lockedLead->save();

            LeadConverted::dispatch(
                $lockedLead->id,
                $opportunity->id,
                $converter->id,
                $lockedLead->company_id,
                $lockedLead->branch_id
            );
            OpportunityCreated::dispatch(
                $opportunity->id,
                $opportunity->title,
                $opportunity->customer_id,
                (float) $opportunity->deal_value,
                $opportunity->assigned_to,
                $opportunity->company_id,
                $opportunity->branch_id
            );

            return $opportunity;
        });
    }
}
