<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringJournal extends Model
{
    use HasFactory, HasTenantIsolation;

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'frequency',
        'template_lines',
        'next_execution_date',
        'expiration_date',
        'skip_holidays',
        'is_active',
    ];

    protected $casts = [
        'template_lines' => 'array',
        'next_execution_date' => 'date',
        'expiration_date' => 'date',
        'skip_holidays' => 'boolean',
        'is_active' => 'boolean',
    ];
}
