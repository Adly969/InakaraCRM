<?php

namespace App\Policies;

use App\Enums\StockAdjustmentStatus;
use App\Models\StockAdjustment;
use App\Models\User;

class StockAdjustmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-inventory');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StockAdjustment $adj): bool
    {
        return $user->hasPermissionTo('view-inventory');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('adjust-inventory');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StockAdjustment $adj): bool
    {
        return $user->hasPermissionTo('adjust-inventory') && $adj->status === StockAdjustmentStatus::Draft;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, StockAdjustment $adj): bool
    {
        return $user->hasPermissionTo('approve-inventory-adjustment') && $adj->status === StockAdjustmentStatus::Draft;
    }
}
