<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $payment_id
 * @property string $receipt_no
 * @property string $status
 * @property string|null $pdf_path
 * @property Carbon|null $sent_at
 * @property Carbon|null $printed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OfficialReceipt extends Model
{
    protected $fillable = [
        'payment_id',
        'receipt_no',
        'status',
        'pdf_path',
        'sent_at',
        'printed_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'printed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
