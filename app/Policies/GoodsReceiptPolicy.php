<?php

namespace App\Policies;

use App\Enums\GoodsReceiptStatus;
use App\Models\GoodsReceipt;
use App\Models\User;

class GoodsReceiptPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-goods-receipts');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GoodsReceipt $gr): bool
    {
        return $user->hasPermissionTo('view-goods-receipts');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-goods-receipts');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GoodsReceipt $gr): bool
    {
        return $user->hasPermissionTo('create-goods-receipts') && $gr->status === GoodsReceiptStatus::Draft;
    }

    /**
     * Determine whether the user can approve/receive the model.
     */
    public function approve(User $user, GoodsReceipt $gr): bool
    {
        return $user->hasPermissionTo('approve-goods-receipts') && $gr->status === GoodsReceiptStatus::Draft;
    }
}
