<?php

namespace App\Models\Traits;

use App\Models\CrmAuditLog;
use App\Models\Scopes\BranchScope;
use App\Models\Scopes\CompanyScope;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @mixin Model
 */
trait HasTenantIsolation
{
    /**
     * Boot the tenant isolation trait for the model.
     */
    protected static function bootHasTenantIsolation(): void
    {
        static::addGlobalScope(new CompanyScope);
        static::addGlobalScope(new BranchScope);
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            $table = $model->getTable();

            // Resolve Tenant Context
            if (app()->bound('current_tenant')) {
                $tenant = app('current_tenant');
                if ($tenant && empty($model->tenant_id) && Schema::hasColumn($table, 'tenant_id')) {
                    $model->tenant_id = $tenant->id;
                }
            } elseif (Auth::hasUser()) {
                $user = Auth::user();
                if (empty($model->tenant_id) && Schema::hasColumn($table, 'tenant_id')) {
                    $model->tenant_id = $user->tenant_id;
                }
            }

            if (Auth::hasUser()) {
                $user = Auth::user();
                if (empty($model->company_id) && Schema::hasColumn($table, 'company_id')) {
                    $model->company_id = $user->company_id;
                }
                if (empty($model->branch_id) && Schema::hasColumn($table, 'branch_id')) {
                    $model->branch_id = $user->branch_id;
                }
            }

            // Safe fallback only for CLI/testing seeders if no tenant context is resolved
            if (empty($model->company_id) && Schema::hasColumn($table, 'company_id')) {
                $firstCompany = null;
                if (! empty($model->tenant_id)) {
                    $firstCompany = DB::table('companies')->where('tenant_id', $model->tenant_id)->first();
                }

                if ($firstCompany) {
                    $model->company_id = $firstCompany->id;
                } else {
                    $model->company_id = 1;
                }
            }
            if (empty($model->branch_id) && Schema::hasColumn($table, 'branch_id')) {
                $firstBranch = null;
                if (! empty($model->tenant_id)) {
                    $firstBranch = DB::table('branches')->where('tenant_id', $model->tenant_id)->first();
                }

                if ($firstBranch) {
                    $model->branch_id = $firstBranch->id;
                } else {
                    $model->branch_id = 1;
                }
            }
        });

        static::updated(function ($model) {
            self::logAudit($model, 'updated');
        });

        static::deleted(function ($model) {
            self::logAudit($model, 'deleted');
        });
    }

    /**
     * Records audit log details for target model actions.
     */
    protected static function logAudit(Model $model, string $event): void
    {
        $dirty = $model->getDirty();
        if (empty($dirty) && $event === 'updated') {
            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach ($dirty as $key => $newValue) {
            if (in_array($key, ['updated_at', 'created_at'])) {
                continue;
            }
            $oldValues[$key] = $model->getOriginal($key);
            $newValues[$key] = $newValue;
        }

        if (empty($newValues) && $event === 'updated') {
            return;
        }

        // Avoid logging audit log creations to avoid infinity loops
        if ($model instanceof CrmAuditLog) {
            return;
        }

        CrmAuditLog::create([
            'tenant_id' => $model->tenant_id ?? (app()->bound('current_tenant') ? app('current_tenant')->id : (Auth::hasUser() ? Auth::user()->tenant_id : null)),
            'company_id' => $model->company_id ?? (Auth::hasUser() ? Auth::user()->company_id : null),
            'branch_id' => $model->branch_id ?? (Auth::hasUser() ? Auth::user()->branch_id : null),
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'event' => $event,
            'action' => $event,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'user_id' => Auth::hasUser() ? Auth::user()->id : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'transition_reason' => request()->input('disqualification_reason') ?? request()->input('loss_notes'),
        ]);
    }
}
