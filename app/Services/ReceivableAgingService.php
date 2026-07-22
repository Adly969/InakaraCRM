<?php

namespace App\Services;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReceivableAgingService
{
    /**
     * Get aging matrix buckets by customer.
     *
     * @return array<string, float>
     */
    public function getAgingByCustomer(int $customerId): array
    {
        $invoices = Invoice::where('customer_id', $customerId)
            ->whereIn('status', ['issued', 'overdue'])
            ->where('outstanding_balance', '>', 0)
            ->get();

        return $this->categorizeInvoices($invoices);
    }

    /**
     * Get company-wide aging summary grouped by customer.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAgingSummary(): array
    {
        // ponytail: Cached/materialized aging — EXT-10-007. Future cache lookup here.
        $invoices = Invoice::with('customer')
            ->whereIn('status', ['issued', 'overdue'])
            ->where('outstanding_balance', '>', 0)
            ->get();

        $summary = [];
        $grouped = $invoices->groupBy('customer_id');

        foreach ($grouped as $customerId => $custInvoices) {
            $customer = $custInvoices->first()->customer;
            $buckets = $this->categorizeInvoices($custInvoices);

            $summary[] = array_merge([
                'customer_id' => $customerId,
                'customer_name' => $customer ? $customer->name : 'Unknown Customer',
                'credit_limit' => 50000000.00, // 50M IDR standard
            ], $buckets);
        }

        // Sort by total outstanding descending
        usort($summary, fn ($a, $b) => $b['total'] <=> $a['total']);

        return $summary;
    }

    /**
     * Categorize a list of invoices into aging buckets.
     *
     * @param  Collection<int, Invoice>  $invoices
     * @return array{current: float, bucket_1_30: float, bucket_31_60: float, bucket_61_90: float, bucket_over_90: float, total: float}
     */
    protected function categorizeInvoices($invoices): array
    {
        $today = Carbon::today();
        $buckets = [
            'current' => 0.00,
            'bucket_1_30' => 0.00,
            'bucket_31_60' => 0.00,
            'bucket_61_90' => 0.00,
            'bucket_over_90' => 0.00,
            'total' => 0.00,
        ];

        foreach ($invoices as $invoice) {
            $balance = (float) $invoice->outstanding_balance;
            $dueDate = Carbon::parse($invoice->due_date);

            $buckets['total'] += $balance;

            if ($dueDate->greaterThanOrEqualTo($today)) {
                $buckets['current'] += $balance;
            } else {
                $days = abs((int) $today->diffInDays($dueDate));

                if ($days >= 1 && $days <= 30) {
                    $buckets['bucket_1_30'] += $balance;
                } elseif ($days >= 31 && $days <= 60) {
                    $buckets['bucket_31_60'] += $balance;
                } elseif ($days >= 61 && $days <= 90) {
                    $buckets['bucket_61_90'] += $balance;
                } else {
                    $buckets['bucket_over_90'] += $balance;
                }
            }
        }

        // Round final values
        foreach ($buckets as $key => $val) {
            $buckets[$key] = round($val, 2);
        }

        return $buckets;
    }
}
