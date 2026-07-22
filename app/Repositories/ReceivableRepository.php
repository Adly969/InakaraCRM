<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReceivableRepository
{
    /**
     * Get outstanding balance summary grouped by customer.
     *
     * @return array<int, object>
     */
    public function getOutstandingSummary(): array
    {
        return DB::table('customers')
            ->leftJoin('invoices', function ($join) {
                $join->on('customers.id', '=', 'invoices.customer_id')
                    ->whereIn('invoices.status', ['issued', 'overdue'])
                    ->whereNull('invoices.deleted_at');
            })
            ->select([
                'customers.id as customer_id',
                'customers.name as customer_name',
                DB::raw('COALESCE(SUM(invoices.outstanding_balance), 0.00) as total_outstanding'),
                DB::raw('COUNT(invoices.id) as open_invoices_count'),
                DB::raw('MIN(invoices.due_date) as oldest_due_date'),
            ])
            ->groupBy('customers.id', 'customers.name')
            ->having('total_outstanding', '>', 0)
            ->orderBy('total_outstanding', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get aging raw invoice details by customer.
     *
     * @return Collection<int, Invoice>
     */
    public function getAgingInvoices(?int $customerId = null): Collection
    {
        $query = Invoice::whereIn('status', ['issued', 'overdue'])
            ->where('outstanding_balance', '>', 0);

        if ($customerId !== null) {
            $query->where('customer_id', $customerId);
        }

        return $query->orderBy('due_date', 'asc')->get();
    }
}
