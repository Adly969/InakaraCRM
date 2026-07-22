<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $payment_id
 * @property string $event_type
 * @property array $event_data
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $created_by
 * @property Carbon|null $created_at
 */
class PaymentEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'event_type',
        'event_data',
        'ip_address',
        'user_agent',
        'created_by',
    ];

    protected $casts = [
        'event_data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
