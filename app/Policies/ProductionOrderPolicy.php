<?php

namespace App\Policies;

use App\Enums\Permission as AppPermission;
use App\Enums\UserRole;
use App\Models\ProductionOrder;
use App\Models\User;

class ProductionOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::ViewProductionOrders->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProductionOrder $productionOrder): bool
    {
        if (! $user->hasPermissionTo(AppPermission::ViewProductionOrders->value)) {
            return false;
        }

        // Owners, Admins, Managers, and Customer Service can view all production orders
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value) ||
            $user->hasRole(UserRole::CustomerService->value)) {
            return true;
        }

        // Sales reps can view if assigned to them or if they own the related Sales Order
        if ($user->hasRole(UserRole::Sales->value)) {
            return $productionOrder->assigned_to === $user->id ||
                ($productionOrder->salesOrder && $productionOrder->salesOrder->assigned_to === $user->id);
        }

        // Produksi/other roles can view only if assigned to them
        return $productionOrder->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::CreateProductionOrders->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductionOrder $productionOrder): bool
    {
        if (! $user->hasPermissionTo(AppPermission::EditProductionOrders->value)) {
            return false;
        }

        // Owners, Admins, and Managers can update all production orders
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value)) {
            return true;
        }

        // Produksi/other roles can update only if assigned to them
        return $productionOrder->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductionOrder $productionOrder): bool
    {
        return $user->hasPermissionTo(AppPermission::DeleteProductionOrders->value);
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, ProductionOrder $productionOrder): bool
    {
        if (! $user->hasPermissionTo(AppPermission::CancelProductionOrders->value)) {
            return false;
        }

        // Owners, Admins, and Managers can cancel all production orders
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value)) {
            return true;
        }

        // Produksi/other roles can cancel only if assigned to them
        return $productionOrder->assigned_to === $user->id;
    }
}
