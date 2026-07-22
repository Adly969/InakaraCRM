<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->bound('current_tenant') && Schema::hasColumn($model->getTable(), 'tenant_id')) {
            $tenant = app('current_tenant');
            if ($tenant) {
                $builder->where($model->getTable().'.tenant_id', $tenant->id);
            }
        }
    }
}
