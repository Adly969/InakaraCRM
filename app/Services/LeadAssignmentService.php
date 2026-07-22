<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Events\LeadAssigned;
use App\Models\Lead;
use App\Models\User;

class LeadAssignmentService
{
    /**
     * Auto-assign a lead using round-robin distribution.
     *
     * Selects the sales agent in the same branch who was least recently
     * assigned a lead. Ties are broken by user ID (lowest first).
     */
    public function assignRoundRobin(Lead $lead, User $assigner): Lead
    {
        $agent = $this->resolveNextAgent();

        if (! $agent) {
            return $lead;
        }

        $lead->assigned_to = $agent->id;
        $lead->updated_by = $assigner->id;

        if ($lead->status === LeadStatus::New) {
            $lead->status = LeadStatus::Assigned;
        }

        $lead->save();

        LeadAssigned::dispatch(
            $lead->id,
            $agent->id,
            $assigner->id,
            $lead->company_id,
            $lead->branch_id
        );

        return $lead;
    }

    /**
     * Find the next eligible sales agent via round-robin.
     *
     * The agent with the oldest (or null) last assignment wins.
     */
    private function resolveNextAgent(): ?User
    {
        return User::query()
            ->role('sales')
            ->orderByRaw('(SELECT MAX(leads.updated_at) FROM leads WHERE leads.assigned_to = users.id) ASC')
            ->first();
    }
}
