<?php

namespace App\Services;

use App\Enums\ProductionOrderStatus;
use App\Enums\ProductionPriority;
use App\Enums\SalesOrderStatus;
use App\Events\ProductionCancelled;
use App\Events\ProductionCompleted;
use App\Events\ProductionOrderCreated;
use App\Events\ProductionOrderScheduled;
use App\Events\ProductionSentToQC;
use App\Events\ProductionStarted;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\SalesOrder;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ProductionOrderService
{
    /**
     * Create a new Production Order.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $creator): ProductionOrder
    {
        return DB::transaction(function () use ($data, $creator) {
            $itemsData = $data['items'] ?? [];
            unset($data['items']);

            $po = new ProductionOrder;
            $po->fill($data);
            $po->status = ProductionOrderStatus::Draft;
            $po->created_by = $creator->id;

            $taxRate = (float) ($data['tax_rate'] ?? 11.00);
            $this->calculateTotals($po, $itemsData, $taxRate);

            $po->save();

            $po->reference_no = 'PO-'.str_pad((string) $po->id, 6, '0', STR_PAD_LEFT);
            $po->saveQuietly();

            $this->insertItems($po, $itemsData);

            $this->logTransition($po, null, ProductionOrderStatus::Draft, $creator, 'Production order created.');

            ProductionOrderCreated::dispatch($po, $creator);

            return $po->load('items');
        });
    }

    /**
     * Update an existing Production Order.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(ProductionOrder $po, array $data, User $updater): ProductionOrder
    {
        return DB::transaction(function () use ($po, $data, $updater) {
            // 1. Optimistic Locking Check
            $this->checkOptimisticLock($po, $data);

            // Resolve status if passed in data
            $newStatus = isset($data['status'])
                ? ($data['status'] instanceof ProductionOrderStatus ? $data['status'] : ProductionOrderStatus::from($data['status']))
                : null;

            // 2. Enforce Field Locking by Current Status
            if (! in_array($po->status, [ProductionOrderStatus::Draft, ProductionOrderStatus::Scheduled], true)) {
                $lockedFields = ['customer_id', 'subject', 'currency', 'tax_rate', 'subtotal', 'tax_amount', 'total_amount', 'sales_order_id'];
                foreach ($lockedFields as $field) {
                    if (isset($data[$field]) && $data[$field] != $po->$field) {
                        throw new DomainException('Only draft or scheduled production orders can be fully edited.');
                    }
                }
                if (isset($data['items'])) {
                    throw new DomainException('Only draft or scheduled production orders can have their items edited.');
                }
            }

            // Enforce notes locking once production begins
            if (in_array($po->status, [ProductionOrderStatus::InProduction, ProductionOrderStatus::QualityControl, ProductionOrderStatus::Completed, ProductionOrderStatus::Cancelled], true)) {
                if (isset($data['production_notes']) && $po->production_notes !== $data['production_notes']) {
                    throw new DomainException('Production notes cannot be edited once production begins.');
                }
            }

            // 3. Status Transition Handling
            if ($newStatus && $po->status !== $newStatus) {
                if (isset($data['cancellation_reason'])) {
                    $po->cancellation_reason = $data['cancellation_reason'];
                }
                if (isset($data['target_completion_date'])) {
                    $po->target_completion_date = $data['target_completion_date'];
                }
                $this->transitionStatus($po, $newStatus, $updater, $data['transition_note'] ?? null);
            }

            // 4. Update remaining editable fields (if status allows full update)
            if (in_array($po->status, [ProductionOrderStatus::Draft, ProductionOrderStatus::Scheduled], true)) {
                $itemsData = $data['items'] ?? [];
                unset($data['items']);

                $po->fill($data);
                $po->updated_by = $updater->id;

                $taxRate = (float) ($data['tax_rate'] ?? 11.00);
                $this->calculateTotals($po, $itemsData, $taxRate);

                $po->save();

                if (! empty($itemsData)) {
                    $po->items()->delete();
                    $this->insertItems($po, $itemsData);
                }
            } else {
                // If not in draft/scheduled, only update non-locked fields like priority or assigned_to if passed
                if (isset($data['priority'])) {
                    $po->priority = $data['priority'] instanceof ProductionPriority ? $data['priority'] : ProductionPriority::from($data['priority']);
                }
                if (array_key_exists('assigned_to', $data)) {
                    $po->assigned_to = $data['assigned_to'];
                }
                if (array_key_exists('actual_hours', $data)) {
                    $po->actual_hours = $data['actual_hours'];
                }
                if (array_key_exists('estimated_hours', $data)) {
                    $po->estimated_hours = $data['estimated_hours'];
                }
                $po->updated_by = $updater->id;
                $po->save();
            }

            return $po->load(['items', 'logs']);
        });
    }

    /**
     * Transition a Production Order to a new status.
     */
    public function transitionStatus(ProductionOrder $po, ProductionOrderStatus $newStatus, User $actor, ?string $note = null): ProductionOrder
    {
        return DB::transaction(function () use ($po, $newStatus, $actor, $note) {
            if (! self::canTransition($po->status, $newStatus)) {
                throw new DomainException("Invalid status transition from {$po->status->label()} to {$newStatus->label()}.");
            }

            // Target completion date validation when scheduling
            if ($newStatus === ProductionOrderStatus::Scheduled && ! $po->target_completion_date) {
                throw new InvalidArgumentException('A target completion date is required when scheduling production.');
            }

            // Cancellation reason validation
            if ($newStatus === ProductionOrderStatus::Cancelled && empty($po->cancellation_reason)) {
                throw new InvalidArgumentException('A cancellation reason is required when cancelling production.');
            }

            $oldStatus = $po->status;

            // Handle transition-specific business actions & metrics
            if ($newStatus === ProductionOrderStatus::InProduction && ! $po->started_at) {
                $po->started_at = now();
            }

            if ($newStatus === ProductionOrderStatus::Completed) {
                $po->completed_at = now();
                $po->actual_completion_date = now();
            }

            $po->status = $newStatus;
            $po->updated_by = $actor->id;
            $po->save();

            $this->logTransition($po, $oldStatus, $newStatus, $actor, $note);

            // Dispatch domain events
            match ($newStatus) {
                ProductionOrderStatus::Scheduled => ProductionOrderScheduled::dispatch($po, $actor),
                ProductionOrderStatus::InProduction => ProductionStarted::dispatch($po, $actor),
                ProductionOrderStatus::QualityControl => ProductionSentToQC::dispatch($po, $actor),
                ProductionOrderStatus::Completed => ProductionCompleted::dispatch($po, $actor),
                ProductionOrderStatus::Cancelled => ProductionCancelled::dispatch($po, $actor),
                default => null,
            };

            return $po;
        });
    }

    /**
     * Create a Production Order from a Confirmed Sales Order.
     */
    public function createFromSalesOrder(SalesOrder $so, User $creator): ProductionOrder
    {
        return DB::transaction(function () use ($so, $creator) {
            if ($so->status !== SalesOrderStatus::Confirmed) {
                throw new DomainException('A Production Order can only be created from a confirmed Sales Order.');
            }

            $exists = ProductionOrder::where('sales_order_id', $so->id)->exists();
            if ($exists) {
                throw new DomainException('This Sales Order has already been converted to a Production Order.');
            }

            $po = new ProductionOrder;
            $po->sales_order_id = $so->id;
            $po->customer_id = $so->customer_id;
            $po->subject = $so->subject;
            $po->status = ProductionOrderStatus::Draft;
            $po->priority = ProductionPriority::Normal;
            $po->currency = $so->currency;
            $po->tax_rate = $so->tax_rate;
            $po->subtotal = $so->subtotal;
            $po->tax_amount = $so->tax_amount;
            $po->total_amount = $so->total_amount;
            $po->assigned_to = $so->assigned_to;
            $po->created_by = $creator->id;

            $po->save();

            $po->reference_no = 'PO-'.str_pad((string) $po->id, 6, '0', STR_PAD_LEFT);
            $po->saveQuietly();

            // Copy items with traceability
            foreach ($so->items as $index => $soItem) {
                $poItem = new ProductionOrderItem([
                    'sales_order_item_id' => $soItem->id,
                    'description' => $soItem->description,
                    'quantity' => $soItem->quantity,
                    'unit' => $soItem->unit,
                    'unit_price' => $soItem->unit_price,
                    'total_price' => $soItem->total_price,
                    'sort_order' => $soItem->sort_order ?? $index,
                ]);
                $po->items()->save($poItem);
            }

            $this->logTransition($po, null, ProductionOrderStatus::Draft, $creator, 'Created from Sales Order '.$so->reference_no);

            ProductionOrderCreated::dispatch($po, $creator);

            return $po->load('items');
        });
    }

    /**
     * Cancel a Production Order.
     */
    public function cancel(ProductionOrder $po, string $reason, User $actor): ProductionOrder
    {
        return DB::transaction(function () use ($po, $reason, $actor) {
            $po->cancellation_reason = $reason;

            return $this->transitionStatus($po, ProductionOrderStatus::Cancelled, $actor, 'Production cancelled.');
        });
    }

    /**
     * Check if status transition is allowed.
     */
    public static function canTransition(ProductionOrderStatus $from, ProductionOrderStatus $to): bool
    {
        return $from->canTransitionTo($to);
    }

    /**
     * Check optimistic lock via updated_at.
     *
     * @param  array<string, mixed>  $data
     */
    protected function checkOptimisticLock(ProductionOrder $po, array $data): void
    {
        if (isset($data['_updated_at'])) {
            $dbTime = $po->updated_at->format('Y-m-d H:i:s');
            $reqTime = Carbon::parse($data['_updated_at'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
            if ($dbTime !== $reqTime) {
                throw ValidationException::withMessages([
                    '_updated_at' => [__('This record has been modified by another user. Please refresh and try again.')],
                ]);
            }
        }
    }

    /**
     * Calculate subtotal, tax_amount, and total_amount.
     */
    protected function calculateTotals(ProductionOrder $po, array $itemsData, float $taxRate = 11.00): void
    {
        $subtotal = 0.00;
        foreach ($itemsData as $item) {
            $qty = (float) ($item['quantity'] ?? 0.00);
            $price = (float) ($item['unit_price'] ?? 0.00);
            $subtotal += ($qty * $price);
        }
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        $po->tax_rate = $taxRate;
        $po->subtotal = $subtotal;
        $po->tax_amount = $taxAmount;
        $po->total_amount = $totalAmount;
    }

    /**
     * Insert items list.
     */
    protected function insertItems(ProductionOrder $po, array $itemsData): void
    {
        foreach ($itemsData as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 1.00);
            $price = (float) ($item['unit_price'] ?? 0.00);

            $poItem = new ProductionOrderItem([
                'sales_order_item_id' => $item['sales_order_item_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $qty,
                'unit' => $item['unit'] ?? 'pcs',
                'unit_price' => $price,
                'total_price' => $qty * $price,
                'sort_order' => $item['sort_order'] ?? $index,
            ]);

            $po->items()->save($poItem);
        }
    }

    /**
     * Log the status transition.
     */
    protected function logTransition(ProductionOrder $po, ?ProductionOrderStatus $from, ProductionOrderStatus $to, User $actor, ?string $note = null): void
    {
        $po->logs()->create([
            'status_from' => $from?->value,
            'status_to' => $to->value,
            'note' => $note,
            'created_by' => $actor->id,
        ]);
    }
}
