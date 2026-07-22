<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $payment_id
 * @property string $file_path
 * @property string $file_name
 * @property int $file_size
 * @property string $mime_type
 * @property int $uploaded_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class PaymentAttachment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'uploaded_by',
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
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
