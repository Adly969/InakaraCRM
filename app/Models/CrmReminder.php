<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CrmReminder extends Model
{
    use HasTenantIsolation;

    public $timestamps = false;

    protected $table = 'crm_reminders';

    protected $fillable = [
        'remindable_type',
        'remindable_id',
        'remind_at',
        'message',
        'is_sent',
        'sent_at',
        'user_id',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'remind_at' => 'datetime',
            'sent_at' => 'datetime',
            'is_sent' => 'boolean',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
