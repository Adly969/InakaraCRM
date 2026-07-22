<?php

namespace App\Policies;

use App\Enums\Permission as AppPermission;
use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::ViewLeads->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lead $lead): bool
    {
        if (! $user->hasPermissionTo(AppPermission::ViewLeads->value)) {
            return false;
        }

        // Owners, Admins, and Managers can view all leads
        if ($user->hasRole(UserRole::Owner->value) || $user->hasRole(UserRole::Admin->value) || $user->hasRole(UserRole::Manager->value)) {
            return true;
        }

        // Sales representatives can only view leads assigned to them
        return $lead->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::CreateLeads->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lead $lead): bool
    {
        if (! $user->hasPermissionTo(AppPermission::EditLeads->value)) {
            return false;
        }

        // Owners, Admins, and Managers can update all leads
        if ($user->hasRole(UserRole::Owner->value) || $user->hasRole(UserRole::Admin->value) || $user->hasRole(UserRole::Manager->value)) {
            return true;
        }

        // Sales representatives can only update leads assigned to them
        return $lead->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lead $lead): bool
    {
        return $user->hasPermissionTo(AppPermission::DeleteLeads->value);
    }

    /**
     * Determine whether the user can reopen the model.
     */
    public function reopen(User $user, Lead $lead): bool
    {
        if (! $user->hasPermissionTo(AppPermission::EditLeads->value)) {
            return false;
        }

        // Reopening is restricted to Owners, Admins, and Managers only
        return $user->hasRole(UserRole::Owner->value) || $user->hasRole(UserRole::Admin->value) || $user->hasRole(UserRole::Manager->value);
    }
}
