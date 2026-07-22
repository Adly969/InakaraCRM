<?php

namespace App\Models;

use App\Enums\ProductionOrderStatus;
use App\Enums\ProductionPriority;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string|null $reference_no
 * @property int $sales_order_id
 * @property int $customer_id
 * @property string $subject
 * @property ProductionOrderStatus $status
 * @property ProductionPriority $priority
 * @property Carbon|null $target_completion_date
 * @property Carbon|null $actual_completion_date
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property float|null $estimated_hours
 * @property float|null $actual_hours
 * @property string|null $production_notes
 * @property string|null $cancellation_reason
 * @property string $currency
 * @property float $tax_rate
 * @property float $subtotal
 * @property float $tax_amount
 * @property float $total_amount
 * @property int|null $assigned_to
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'reference_no',
    'sales_order_id',
    'customer_id',
    'subject',
    'status',
    'priority',
    'target_completion_date',
    'actual_completion_date',
    'started_at',
    'completed_at',
    'estimated_hours',
    'actual_hours',
    'production_notes',
    'cancellation_reason',
    'currency',
    'tax_rate',
    'subtotal',
    'tax_amount',
    'total_amount',
    'assigned_to',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class ProductionOrder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProductionOrderStatus::class,
            'priority' => ProductionPriority::class,
            'target_completion_date' => 'date',
            'actual_completion_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'tax_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
        ];
    }

    /**
     * Scope to search.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('production_orders.reference_no', 'like', "%{$term}%")
                ->orWhere('production_orders.subject', 'like', "%{$term}%")
                ->orWhereExists(function ($sub) use ($term) {
                    $sub->select(DB::raw(1))
                        ->from('customers')
                        ->whereColumn('customers.id', 'production_orders.customer_id')
                        ->where('customers.name', 'like', "%{$term}%");
                });
        });
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('production_orders.status', $status);
    }

    /**
     * Scope to filter by priority.
     */
    public function scopePriority(Builder $query, ?string $priority): Builder
    {
        if (empty($priority)) {
            return $query;
        }

        return $query->where('production_orders.priority', $priority);
    }

    /**
     * Scope to filter by assigned user.
     */
    public function scopeAssigned(Builder $query, ?int $userId): Builder
    {
        if (empty($userId)) {
            return $query;
        }

        return $query->where('production_orders.assigned_to', $userId);
    }

    /**
     * Scope to apply row-level visibility.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value) ||
            $user->hasRole(UserRole::CustomerService->value)) {
            return $query;
        }

        if ($user->hasRole(UserRole::Sales->value)) {
            return $query->where(function ($q) use ($user) {
                $q->where('production_orders.assigned_to', $user->id)
                    ->orWhereExists(function ($sub) use ($user) {
                        $sub->select(DB::raw(1))
                            ->from('sales_orders')
                            ->whereColumn('sales_orders.id', 'production_orders.sales_order_id')
                            ->where('sales_orders.assigned_to', $user->id);
                    });
            });
        }

        // Default scoping for Produksi and others: must be explicitly assigned to them
        return $query->where('production_orders.assigned_to', $user->id);
    }

    /**
     * Get the items of this production order.
     *
     * @return HasMany<ProductionOrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    /**
     * Get the logs of this production order.
     *
     * @return HasMany<ProductionOrderLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ProductionOrderLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the customer associated with the production order.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sales order associated with the production order.
     *
     * @return BelongsTo<SalesOrder, $this>
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the user assigned to this production order.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this production order.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this production order.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this production order.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
