<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string|null $vehicle_plate_no
 * @property string $status
 */
#[Fillable([
    'name',
    'phone',
    'vehicle_plate_no',
    'status',
])]
class Driver extends Model
{
    use HasFactory;
}
