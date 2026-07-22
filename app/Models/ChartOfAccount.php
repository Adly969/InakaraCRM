<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasFactory, HasTenantIsolation;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'company_id',
        'branch_id',
        'account_code',
        'name',
        'account_type',
        'normal_balance',
        'is_control_account',
        'is_posting_allowed',
        'parent_id',
    ];

    protected $casts = [
        'is_control_account' => 'boolean',
        'is_posting_allowed' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'account_id');
    }
}
