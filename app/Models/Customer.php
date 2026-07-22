<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Models\Traits\HasTenantIsolation;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $reference_no
 * @property string $name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string $type
 * @property CustomerStatus $status
 * @property string|null $notes
 * @property int|null $assigned_to
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $parent_id
 * @property int $version
 * @property string $tenant_id
 * @property int|null $company_id
 * @property int|null $branch_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'reference_no',
    'name',
    'company_name',
    'email',
    'phone',
    'type',
    'status',
    'notes',
    'assigned_to',
    'created_by',
    'updated_by',
    'deleted_by',
    'company_id',
    'branch_id',
    'parent_id',
    'version',
])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, HasTenantIsolation, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CustomerStatus::class,
        ];
    }

    /**
     * Get the user assigned to this customer.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this customer.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this customer.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this customer.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get all invoices for this customer.
     *
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all payments for this customer.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all contacts for this customer.
     *
     * @return HasMany<CustomerContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class, 'customer_id');
    }

    /**
     * Get all addresses for this customer.
     *
     * @return HasMany<CustomerAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    /**
     * Get all tags for this customer.
     *
     * @return BelongsToMany<CustomerTag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(CustomerTag::class, 'customer_taggables', 'customer_id', 'tag_id');
    }

    /**
     * Get all ownership transfer histories for this customer.
     *
     * @return HasMany<CustomerOwnerHistory, $this>
     */
    public function ownerHistories(): HasMany
    {
        return $this->hasMany(CustomerOwnerHistory::class, 'customer_id');
    }

    /**
     * Get all timeline activities for this customer.
     *
     * @return HasMany<Activity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'customer_id');
    }

    /**
     * Get all follow-ups for this customer.
     *
     * @return HasMany<FollowUp, $this>
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'customer_id');
    }

    /**
     * Get all custom field values for this customer.
     *
     * @return HasMany<CustomerCustomFieldValue, $this>
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomerCustomFieldValue::class, 'customer_id');
    }
}
