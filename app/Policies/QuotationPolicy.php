<?php

namespace App\Policies;

use App\Enums\Permission as AppPermission;
use App\Enums\UserRole;
use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::ViewQuotations->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Quotation $quotation): bool
    {
        if (! $user->hasPermissionTo(AppPermission::ViewQuotations->value)) {
            return false;
        }

        // Owners, Admins, Managers, and Customer Service can view all quotations
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value) ||
            $user->hasRole(UserRole::CustomerService->value)) {
            return true;
        }

        // Sales representatives can only view quotations assigned to them
        return $quotation->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::CreateQuotations->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Quotation $quotation): bool
    {
        if (! $user->hasPermissionTo(AppPermission::EditQuotations->value)) {
            return false;
        }

        // Owners, Admins, and Managers can update all quotations
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value)) {
            return true;
        }

        // Sales representatives can only update quotations assigned to them
        return $quotation->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Quotation $quotation): bool
    {
        return $user->hasPermissionTo(AppPermission::DeleteQuotations->value);
    }
}
