<?php

namespace App\Services;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    /**
     * Create a new quotation record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $creator): Quotation
    {
        return DB::transaction(function () use ($data, $creator) {
            $itemsData = $data['items'] ?? [];
            unset($data['items']);

            $quotation = new Quotation;
            $quotation->fill($data);
            $quotation->created_by = $creator->id;

            // Initial calculations
            $taxRate = (float) ($data['tax_rate'] ?? 11.00);
            $this->calculateTotals($quotation, $itemsData, $taxRate);

            $quotation->save();

            // Auto-generate reference number
            $quotation->reference_no = 'QT-'.str_pad((string) $quotation->id, 6, '0', STR_PAD_LEFT);
            $quotation->saveQuietly();

            // Insert line items
            $this->insertItems($quotation, $itemsData);

            return $quotation->load('items');
        });
    }

    /**
     * Update an existing quotation record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Quotation $quotation, array $data, User $updater): Quotation
    {
        return DB::transaction(function () use ($quotation, $data, $updater) {
            $newStatus = isset($data['status'])
                ? ($data['status'] instanceof QuotationStatus ? $data['status'] : QuotationStatus::from($data['status']))
                : null;

            // 1. Enforce Status Transition Matrix
            if ($newStatus && $quotation->status !== $newStatus) {
                if ($quotation->status === QuotationStatus::Sent) {
                    if (! in_array($newStatus, [QuotationStatus::Accepted, QuotationStatus::Rejected])) {
                        throw new DomainException("Invalid status transition from {$quotation->status->value} to {$newStatus->value}.");
                    }
                } elseif ($quotation->status === QuotationStatus::Accepted || $quotation->status === QuotationStatus::Rejected) {
                    throw new DomainException('Cannot change status of a completed quotation.');
                }
            }

            // 2. Enforce locked fields if not in Draft status
            if ($quotation->status !== QuotationStatus::Draft) {
                // If attempting to edit fields other than status, throw Exception
                $editableData = ['status' => $newStatus?->value];
                $filteredUpdate = array_filter($data, function ($key) use ($editableData) {
                    return ! array_key_exists($key, $editableData);
                }, ARRAY_FILTER_USE_KEY);

                if (! empty($filteredUpdate) || isset($data['items'])) {
                    throw new DomainException('Quotations that are sent, accepted, or rejected cannot be edited. You can only transition their status.');
                }

                if ($newStatus) {
                    $quotation->status = $newStatus;
                    $quotation->updated_by = $updater->id;
                    $quotation->save();
                }

                return $quotation->load('items');
            }

            // 3. Perform standard draft update (Delete-and-Insert line items)
            $itemsData = $data['items'] ?? [];
            unset($data['items']);

            $quotation->fill($data);
            $quotation->updated_by = $updater->id;

            // Recalculate totals
            $taxRate = (float) ($data['tax_rate'] ?? 11.00);
            $this->calculateTotals($quotation, $itemsData, $taxRate);

            $quotation->save();

            // Replace line items
            $quotation->items()->delete();
            $this->insertItems($quotation, $itemsData);

            return $quotation->load('items');
        });
    }

    /**
     * Calculate and set subtotal, tax_amount, and total_amount on the quotation header.
     *
     * @param  array<int, array<string, mixed>>  $itemsData
     */
    protected function calculateTotals(Quotation $quotation, array $itemsData, float $taxRate = 11.00): void
    {
        $subtotal = 0.00;

        foreach ($itemsData as $item) {
            $qty = (float) ($item['quantity'] ?? 1.00);
            $price = (float) ($item['unit_price'] ?? 0.00);
            $subtotal += ($qty * $price);
        }

        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        $quotation->tax_rate = $taxRate;
        $quotation->subtotal = $subtotal;
        $quotation->tax_amount = $taxAmount;
        $quotation->total_amount = $totalAmount;
    }

    /**
     * Insert line items for a quotation.
     *
     * @param  array<int, array<string, mixed>>  $itemsData
     */
    protected function insertItems(Quotation $quotation, array $itemsData): void
    {
        foreach ($itemsData as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 1.00);
            $price = (float) ($item['unit_price'] ?? 0.00);

            $quotationItem = new QuotationItem([
                'description' => $item['description'],
                'quantity' => $qty,
                'unit' => $item['unit'] ?? 'pcs',
                'unit_price' => $price,
                'total_price' => $qty * $price,
                'sort_order' => $item['sort_order'] ?? $index,
            ]);

            $quotation->items()->save($quotationItem);
        }
    }
}
