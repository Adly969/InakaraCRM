<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentNumberRange extends Model
{
    use HasFactory, HasTenantIsolation;

    protected $fillable = [
        'company_id',
        'branch_id',
        'document_type',
        'prefix',
        'current_value',
    ];

    protected $casts = [
        'current_value' => 'integer',
    ];
}
