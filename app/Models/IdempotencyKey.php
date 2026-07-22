<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    protected $fillable = [
        'key_hash',
        'expiry_time',
    ];

    protected $casts = [
        'expiry_time' => 'datetime',
    ];
}
