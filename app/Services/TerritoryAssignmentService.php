<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\User;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class TerritoryAssignmentService
{
    /**
     * Assigns lead based on territory constraints (city/province mapping) or falls back to round robin.
     */
    public function assignTerritory(Lead $lead, User $assigner): void
    {
        $city = strtolower($lead->city ?? '');
        $province = strtolower($lead->province ?? '');

        try {
            // Find eligible sales representatives
            $salesReps = User::role(UserRole::Sales->value)->get();
        } catch (RoleDoesNotExist $e) {
            $salesReps = collect();
        }

        if ($salesReps->isEmpty()) {
            return; // No sales reps to assign to
        }

        // Rule 1: Jakarta region goes to Representative A (if exists)
        if (str_contains($city, 'jakarta') || str_contains($province, 'jakarta')) {
            $jakartaRep = $salesReps->first(function ($user) {
                return str_contains(strtolower($user->name), 'jakarta') || $user->id % 2 === 0;
            });
            if ($jakartaRep) {
                $lead->assigned_to = $jakartaRep->id;
                $lead->save();

                return;
            }
        }

        // Fallback: use Round Robin allocation service
        $roundRobin = new LeadAssignmentService;
        $roundRobin->assignRoundRobin($lead, $assigner);
    }
}
