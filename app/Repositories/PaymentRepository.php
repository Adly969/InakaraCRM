<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Support\Facades\DB;

class PaymentRepository
{
    /**
     * Find a payment by ID.
     */
    public function find(int $id): Payment
    {
        return Payment::findOrFail($id);
    }

    /**
     * Find a payment by ID with locks for update.
     */
    public function lockForUpdate(int $id): Payment
    {
        return Payment::where('id', $id)->lockForUpdate()->firstOrFail();
    }

    /**
     * Save a payment.
     */
    public function save(Payment $payment): bool
    {
        return $payment->save();
    }

    /**
     * Create a payment with allocations inside a transaction.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $allocations
     */
    public function createWithAllocations(array $data, array $allocations): Payment
    {
        return DB::transaction(function () use ($data, $allocations) {
            $payment = Payment::create($data);

            foreach ($allocations as $alloc) {
                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $alloc['invoice_id'],
                    'amount' => $alloc['amount'],
                    'notes' => $alloc['notes'] ?? null,
                ]);
            }

            return $payment;
        });
    }
}
