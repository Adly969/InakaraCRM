<?php

namespace App\Policies;

use App\Enums\Permission as AppPermission;
use App\Enums\UserRole;
use App\Models\SalesOrder;
use App\Models\User;

class SalesOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::ViewSalesOrders->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SalesOrder $salesOrder): bool
    {
        if (! $user->hasPermissionTo(AppPermission::ViewSalesOrders->value)) {
            return false;
        }

        // Owners, Admins, Managers, and Customer Service can view all sales orders
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value) ||
            $user->hasRole(UserRole::CustomerService->value)) {
            return true;
        }

        // Sales representatives can only view sales orders assigned to them
        return $salesOrder->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::CreateSalesOrders->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SalesOrder $salesOrder): bool
    {
        if (! $user->hasPermissionTo(AppPermission::EditSalesOrders->value)) {
            return false;
        }

        // Owners, Admins, and Managers can update all sales orders
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value)) {
            return true;
        }

        // Sales representatives can only update sales orders assigned to them
        return $salesOrder->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SalesOrder $salesOrder): bool
    {
        return $user->hasPermissionTo(AppPermission::DeleteSalesOrders->value);
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, SalesOrder $salesOrder): bool
    {
        if (! $user->hasPermissionTo(AppPermission::CancelSalesOrders->value)) {
            return false;
        }

        // Owners, Admins, and Managers can cancel all sales orders
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value)) {
            return true;
        }

        // Sales representatives can only cancel sales orders assigned to them
        return $salesOrder->assigned_to === $user->id;
    }
}
