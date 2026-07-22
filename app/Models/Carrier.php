<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $status
 */
#[Fillable([
    'code',
    'name',
    'status',
])]
class Carrier extends Model
{
    use HasFactory;
}
