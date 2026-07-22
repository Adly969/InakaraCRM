<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

class InvoiceRepository
{
    /**
     * Find an invoice by ID.
     */
    public function find(int $id): Invoice
    {
        return Invoice::findOrFail($id);
    }

    /**
     * Find an invoice by ID with locks for update.
     */
    public function lockForUpdate(int $id): Invoice
    {
        return Invoice::where('id', $id)->lockForUpdate()->firstOrFail();
    }

    /**
     * Get all open (issued/overdue) invoices with outstanding balance for a customer.
     *
     * @return Collection<int, Invoice>
     */
    public function getOpenInvoicesByCustomer(int $customerId): Collection
    {
        return Invoice::where('customer_id', $customerId)
            ->whereIn('status', ['issued', 'overdue'])
            ->where('outstanding_balance', '>', 0)
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Save an invoice.
     */
    public function save(Invoice $invoice): bool
    {
        return $invoice->save();
    }
}
