<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Enums\UserRole;
use App\Events\LeadAssigned;
use App\Events\LeadQualified;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;

class LeadService
{
    /**
     * Create a new lead record and generate its reference number.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $creator): Lead
    {
        $lead = new Lead;
        $lead->fill($data);
        $lead->created_by = $creator->id;
        $lead->save();

        $lead->reference_no = 'LD-'.str_pad((string) $lead->id, 6, '0', STR_PAD_LEFT);
        $lead->saveQuietly();

        // Automatically assign lead based on territory routing rules
        $territoryService = new TerritoryAssignmentService;
        $territoryService->assignTerritory($lead, $creator);

        return $lead;
    }

    /**
     * Update a lead's basic attributes.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Lead $lead, array $data, User $updater): Lead
    {
        $lead->fill($data);
        $lead->updated_by = $updater->id;
        $lead->save();

        return $lead;
    }

    /**
     * Assign a lead to a user.
     */
    public function assign(Lead $lead, ?User $user, User $updater): Lead
    {
        $lead->assigned_to = $user?->id;
        $lead->updated_by = $updater->id;

        if ($user && $lead->status === LeadStatus::New) {
            $lead->status = LeadStatus::Assigned;
        }

        $lead->save();

        if ($user) {
            LeadAssigned::dispatch($lead->id, $user->id, $updater->id, $lead->company_id, $lead->branch_id);
        }

        return $lead;
    }

    /**
     * Change a lead's status.
     *
     * @throws AuthorizationException
     * @throws InvalidArgumentException
     */
    public function changeStatus(Lead $lead, LeadStatus $status, ?string $disqualificationReason, User $updater): Lead
    {
        // Enforce role-based reopen check: Only managers, admins, or owners can reopen a disqualified lead
        if ($lead->status === LeadStatus::Disqualified && $status !== LeadStatus::Disqualified) {
            if (! ($updater->hasRole(UserRole::Owner->value) ||
                   $updater->hasRole(UserRole::Admin->value) ||
                   $updater->hasRole(UserRole::Manager->value))) {
                throw new AuthorizationException('Only managers, admins, or owners can reopen a disqualified lead.');
            }
        }

        // Enforce mandatory disqualification reason
        if ($status === LeadStatus::Disqualified) {
            if (empty(trim($disqualificationReason ?? ''))) {
                throw new InvalidArgumentException('A disqualification reason is required when status is disqualified.');
            }
            $lead->disqualification_reason = $disqualificationReason;
        } else {
            $lead->disqualification_reason = null;
        }

        $lead->status = $status;
        $lead->updated_by = $updater->id;
        $lead->save();

        if ($status === LeadStatus::Qualified) {
            LeadQualified::dispatch($lead->id, $updater->id, $lead->company_id, $lead->branch_id);
        }

        return $lead;
    }
}
