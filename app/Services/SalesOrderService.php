<?php

namespace App\Services;

use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalesOrderService
{
    /**
     * Use constructor promotion to inject CustomerService dependency.
     */
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Create a new sales order record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $creator): SalesOrder
    {
        return DB::transaction(function () use ($data, $creator) {
            $itemsData = $data['items'] ?? [];
            unset($data['items']);

            $salesOrder = new SalesOrder;
            $salesOrder->fill($data);
            $salesOrder->created_by = $creator->id;

            $taxRate = (float) ($data['tax_rate'] ?? 11.00);
            $this->calculateTotals($salesOrder, $itemsData, $taxRate);

            $salesOrder->save();

            $salesOrder->reference_no = 'SO-'.str_pad((string) $salesOrder->id, 6, '0', STR_PAD_LEFT);
            $salesOrder->saveQuietly();

            $this->insertItems($salesOrder, $itemsData);

            return $salesOrder->load('items');
        });
    }

    /**
     * Update an existing sales order.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(SalesOrder $salesOrder, array $data, User $updater): SalesOrder
    {
        return DB::transaction(function () use ($salesOrder, $data, $updater) {
            $newStatus = isset($data['status'])
                ? ($data['status'] instanceof SalesOrderStatus ? $data['status'] : SalesOrderStatus::from($data['status']))
                : null;

            // 1. Enforce Status Transition Matrix
            if ($newStatus && $salesOrder->status !== $newStatus) {
                if ($salesOrder->status === SalesOrderStatus::Draft) {
                    if (! in_array($newStatus, [SalesOrderStatus::Confirmed, SalesOrderStatus::Cancelled])) {
                        throw new DomainException("Invalid status transition from {$salesOrder->status->value} to {$newStatus->value}.");
                    }
                } elseif ($salesOrder->status === SalesOrderStatus::Confirmed) {
                    if ($newStatus !== SalesOrderStatus::Cancelled) {
                        throw new DomainException("Invalid status transition from {$salesOrder->status->value} to {$newStatus->value}.");
                    }
                } elseif ($salesOrder->status === SalesOrderStatus::Cancelled) {
                    throw new DomainException('Cannot change status of a cancelled sales order.');
                }
            }

            // 2. Enforce locked fields if not in Draft status
            if ($salesOrder->status !== SalesOrderStatus::Draft) {
                $editableData = [
                    'status' => $newStatus?->value,
                    'cancellation_reason' => $data['cancellation_reason'] ?? null,
                ];
                $filteredUpdate = array_filter($data, function ($key) use ($editableData) {
                    return ! array_key_exists($key, $editableData);
                }, ARRAY_FILTER_USE_KEY);

                if (! empty($filteredUpdate) || isset($data['items'])) {
                    throw new DomainException('Sales orders that are confirmed or cancelled cannot be edited. You can only transition their status.');
                }

                if ($newStatus) {
                    if ($newStatus === SalesOrderStatus::Cancelled) {
                        $reason = trim($data['cancellation_reason'] ?? '');
                        if (empty($reason)) {
                            throw new InvalidArgumentException('A cancellation reason is required when status is cancelled.');
                        }
                        $salesOrder->cancellation_reason = $reason;
                    }
                    $salesOrder->status = $newStatus;
                    $salesOrder->updated_by = $updater->id;
                    $salesOrder->save();
                }

                return $salesOrder->load('items');
            }

            // 3. Perform standard draft update (Delete-and-Insert line items)
            if ($newStatus === SalesOrderStatus::Cancelled) {
                $reason = trim($data['cancellation_reason'] ?? '');
                if (empty($reason)) {
                    throw new InvalidArgumentException('A cancellation reason is required when status is cancelled.');
                }
                $salesOrder->cancellation_reason = $reason;
            }

            $itemsData = $data['items'] ?? [];
            unset($data['items']);

            $salesOrder->fill($data);
            $salesOrder->updated_by = $updater->id;

            $taxRate = (float) ($data['tax_rate'] ?? 11.00);
            $this->calculateTotals($salesOrder, $itemsData, $taxRate);

            $salesOrder->save();

            $salesOrder->items()->delete();
            $this->insertItems($salesOrder, $itemsData);

            return $salesOrder->load('items');
        });
    }

    /**
     * Cancel an existing sales order.
     */
    public function cancel(SalesOrder $salesOrder, string $reason, User $canceller): SalesOrder
    {
        return $this->update($salesOrder, [
            'status' => SalesOrderStatus::Cancelled->value,
            'cancellation_reason' => $reason,
        ], $canceller);
    }

    /**
     * Create a Sales Order directly from an accepted Quotation.
     */
    public function createFromQuotation(Quotation $quotation, User $creator): SalesOrder
    {
        return DB::transaction(function () use ($quotation, $creator) {
            if ($quotation->status === QuotationStatus::Accepted) {
                throw new DomainException('This quotation has already been converted to a Sales Order.');
            }

            // Promote Lead to Customer if Quotation is only associated with a Lead
            if ($quotation->lead_id && ! $quotation->customer_id) {
                $lead = $quotation->lead;
                $customer = $this->customerService->promoteLead($lead, $creator);
                $quotation->customer_id = $customer->id;
                $quotation->saveQuietly();
            }

            $salesOrder = new SalesOrder;
            $salesOrder->quotation_id = $quotation->id;
            $salesOrder->customer_id = $quotation->customer_id;
            $salesOrder->subject = $quotation->subject;
            $salesOrder->status = SalesOrderStatus::Draft;
            $salesOrder->currency = $quotation->currency;
            $salesOrder->tax_rate = $quotation->tax_rate;
            $salesOrder->subtotal = $quotation->subtotal;
            $salesOrder->tax_amount = $quotation->tax_amount;
            $salesOrder->total_amount = $quotation->total_amount;
            $salesOrder->assigned_to = $quotation->assigned_to;
            $salesOrder->created_by = $creator->id;
            $salesOrder->save();

            $salesOrder->reference_no = 'SO-'.str_pad((string) $salesOrder->id, 6, '0', STR_PAD_LEFT);
            $salesOrder->saveQuietly();

            // Copy items
            foreach ($quotation->items as $index => $item) {
                $orderItem = new SalesOrderItem([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'sort_order' => $item->sort_order ?? $index,
                ]);
                $salesOrder->items()->save($orderItem);
            }

            // Update Quotation Status
            $quotation->status = QuotationStatus::Accepted;
            $quotation->updated_by = $creator->id;
            $quotation->save();

            return $salesOrder->load('items');
        });
    }

    /**
     * Calculate and set subtotal, tax_amount, and total_amount on the sales order header.
     *
     * @param  array<int, array<string, mixed>>  $itemsData
     */
    protected function calculateTotals(SalesOrder $salesOrder, array $itemsData, float $taxRate = 11.00): void
    {
        $subtotal = 0.00;

        foreach ($itemsData as $item) {
            $qty = (float) ($item['quantity'] ?? 1.00);
            $price = (float) ($item['unit_price'] ?? 0.00);
            $subtotal += ($qty * $price);
        }

        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        $salesOrder->tax_rate = $taxRate;
        $salesOrder->subtotal = $subtotal;
        $salesOrder->tax_amount = $taxAmount;
        $salesOrder->total_amount = $totalAmount;
    }

    /**
     * Insert line items for a sales order.
     *
     * @param  array<int, array<string, mixed>>  $itemsData
     */
    protected function insertItems(SalesOrder $salesOrder, array $itemsData): void
    {
        foreach ($itemsData as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 1.00);
            $price = (float) ($item['unit_price'] ?? 0.00);

            $orderItem = new SalesOrderItem([
                'description' => $item['description'],
                'quantity' => $qty,
                'unit' => $item['unit'] ?? 'pcs',
                'unit_price' => $price,
                'total_price' => $qty * $price,
                'sort_order' => $item['sort_order'] ?? $index,
            ]);

            $salesOrder->items()->save($orderItem);
        }
    }
}
