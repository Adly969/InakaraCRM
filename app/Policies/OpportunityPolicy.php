<?php

namespace App\Policies;

use App\Enums\Permission as AppPermission;
use App\Enums\UserRole;
use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::ViewOpportunities->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Opportunity $opportunity): bool
    {
        if (! $user->hasPermissionTo(AppPermission::ViewOpportunities->value)) {
            return false;
        }

        // Owners, Admins, and Managers can view all opportunities
        if ($user->hasRole(UserRole::Owner->value) || $user->hasRole(UserRole::Admin->value) || $user->hasRole(UserRole::Manager->value)) {
            return true;
        }

        // Sales representatives can only view opportunities assigned to them
        return $opportunity->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::CreateOpportunities->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Opportunity $opportunity): bool
    {
        if (! $user->hasPermissionTo(AppPermission::EditOpportunities->value)) {
            return false;
        }

        // Owners, Admins, and Managers can update all opportunities
        if ($user->hasRole(UserRole::Owner->value) || $user->hasRole(UserRole::Admin->value) || $user->hasRole(UserRole::Manager->value)) {
            return true;
        }

        // Sales representatives can only update opportunities assigned to them
        return $opportunity->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Opportunity $opportunity): bool
    {
        return $user->hasPermissionTo(AppPermission::DeleteOpportunities->value);
    }
}
