<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSchemaRegistry extends Model
{
    protected $table = 'event_schema_registry';

    protected $fillable = [
        'schema_name',
        'schema_version',
        'required_fields',
        'optional_fields',
        'deprecated_fields',
        'payload_hash',
        'validation_rules',
        'is_active',
    ];

    protected $casts = [
        'required_fields' => 'json',
        'optional_fields' => 'json',
        'deprecated_fields' => 'json',
        'validation_rules' => 'json',
        'is_active' => 'boolean',
    ];
}
