<?php

namespace App\Models\Scopes;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::hasUser()) {
            $user = Auth::user();

            // Skip branch filtering for Owner, Admin, and Manager roles
            if ($user->hasRole(UserRole::Owner->value) ||
                $user->hasRole(UserRole::Admin->value) ||
                $user->hasRole(UserRole::Manager->value)) {
                return;
            }

            if ($user->branch_id !== null && Schema::hasColumn($model->getTable(), 'branch_id')) {
                $builder->where($model->getTable().'.branch_id', $user->branch_id);
            }
        }
    }
}
