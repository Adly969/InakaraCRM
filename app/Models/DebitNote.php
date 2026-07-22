<?php

namespace App\Models;

use App\Models\Traits\HasTenantIsolation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $tenant_id
 * @property int $invoice_id
 * @property string $debit_note_no
 * @property float $amount
 * @property string $type
 * @property string $status
 * @property int $version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'invoice_id',
    'debit_note_no',
    'amount',
    'type',
    'status',
    'version',
])]
class DebitNote extends Model
{
    use HasTenantIsolation, HasUuids, SoftDeletes;

    protected $casts = [
        'amount' => 'decimal:4',
    ];

    /**
     * Get the parent invoice.
     *
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
