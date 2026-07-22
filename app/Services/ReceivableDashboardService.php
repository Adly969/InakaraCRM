<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReceivableDashboardService
{
    /**
     * Get all dashboard metrics.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        $calculator = function () {
            $today = Carbon::today();
            $startOfWeek = Carbon::today()->startOfWeek();
            $startOfMonth = Carbon::today()->startOfMonth();

            // 1. Outstanding Balance
            $totalOutstanding = round((float) Invoice::whereIn('status', ['issued', 'overdue'])->sum('outstanding_balance'), 2);

            // 2. Overdue Summary
            $totalOverdue = round((float) Invoice::where('status', 'overdue')->sum('outstanding_balance'), 2);

            // 3. Today's, Weekly, Monthly Collection
            $todayCollection = round((float) Payment::where('status', 'posted')->whereDate('payment_date', $today)->sum('amount'), 2);
            $weeklyCollection = round((float) Payment::where('status', 'posted')->whereBetween('payment_date', [$startOfWeek, $today])->sum('amount'), 2);
            $monthlyCollection = round((float) Payment::where('status', 'posted')->whereBetween('payment_date', [$startOfMonth, $today])->sum('amount'), 2);

            // 4. Collection Rate (Collections / Total Invoiced)
            $totalInvoicedThisMonth = (float) Invoice::whereBetween('invoice_date', [$startOfMonth, $today])->whereIn('status', ['issued', 'overdue', 'approved'])->sum('total_amount');
            $collectionRate = $totalInvoicedThisMonth > 0 ? round(($monthlyCollection / $totalInvoicedThisMonth) * 100, 2) : 100.00;

            // 5. Average Collection Days (DSO stub/approximation)
            $salesLast30Days = (float) Invoice::where('invoice_date', '>=', Carbon::today()->subDays(30))->whereIn('status', ['issued', 'overdue', 'approved'])->sum('total_amount');
            $dso = $salesLast30Days > 0 ? round(($totalOutstanding / $salesLast30Days) * 30, 1) : 0.0;

            // 6. Top Debtors
            $topDebtors = DB::table('customers')
                ->join('invoices', 'customers.id', '=', 'invoices.customer_id')
                ->whereIn('invoices.status', ['issued', 'overdue'])
                ->whereNull('invoices.deleted_at')
                ->select('customers.id', 'customers.name', DB::raw('SUM(invoices.outstanding_balance) as outstanding'))
                ->groupBy('customers.id', 'customers.name')
                ->orderBy('outstanding', 'desc')
                ->limit(5)
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'outstanding' => round((float) $item->outstanding, 2),
                ])
                ->toArray();

            // 7. Cash Forecast (Invoices due in next 7, 14, 30 days)
            $due7Days = round((float) Invoice::whereIn('status', ['issued', 'overdue'])
                ->whereBetween('due_date', [$today, Carbon::today()->addDays(7)])
                ->sum('outstanding_balance'), 2);
            $due14Days = round((float) Invoice::whereIn('status', ['issued', 'overdue'])
                ->whereBetween('due_date', [$today, Carbon::today()->addDays(14)])
                ->sum('outstanding_balance'), 2);
            $due30Days = round((float) Invoice::whereIn('status', ['issued', 'overdue'])
                ->whereBetween('due_date', [$today, Carbon::today()->addDays(30)])
                ->sum('outstanding_balance'), 2);

            return [
                'total_outstanding' => $totalOutstanding,
                'total_overdue' => $totalOverdue,
                'today_collection' => $todayCollection,
                'weekly_collection' => $weeklyCollection,
                'monthly_collection' => $monthlyCollection,
                'collection_rate' => $collectionRate,
                'dso' => $dso,
                'top_debtors' => $topDebtors,
                'forecast' => [
                    'next_7_days' => $due7Days,
                    'next_14_days' => $due14Days,
                    'next_30_days' => $due30Days,
                ],
            ];
        };

        if (Cache::store() instanceof TaggableStore) {
            return Cache::tags(['receivables', 'dashboard'])->remember('receivable_dashboard_metrics', 3600, $calculator);
        }

        return Cache::remember('receivable_dashboard_metrics', 3600, $calculator);
    }

    /**
     * Invalidate dashboard metrics cache.
     */
    public function invalidateCache(): void
    {
        if (Cache::store() instanceof TaggableStore) {
            Cache::tags(['receivables', 'dashboard'])->flush();
        } else {
            Cache::forget('receivable_dashboard_metrics');
        }
    }
}
