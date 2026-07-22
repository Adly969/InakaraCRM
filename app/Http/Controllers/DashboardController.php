<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $currentYear = Carbon::now()->year;
        $previousYear = $currentYear - 1;

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        // 1. DYNAMIC REAL-TIME QUERY: Monthly Sales Orders Revenue for Current & Previous Year
        $ordersCurrentYear = SalesOrder::whereYear('created_at', $currentYear)
            ->selectRaw('MONTH(created_at) as month_num, SUM(total_amount) as total_rev, COUNT(id) as total_orders')
            ->groupBy('month_num')
            ->pluck('total_rev', 'month_num')
            ->all();

        $ordersCountCurrentYear = SalesOrder::whereYear('created_at', $currentYear)
            ->selectRaw('MONTH(created_at) as month_num, COUNT(id) as total_orders')
            ->groupBy('month_num')
            ->pluck('total_orders', 'month_num')
            ->all();

        $ordersPreviousYear = SalesOrder::whereYear('created_at', $previousYear)
            ->selectRaw('MONTH(created_at) as month_num, SUM(total_amount) as total_rev')
            ->groupBy('month_num')
            ->pluck('total_rev', 'month_num')
            ->all();

        $comparisonData = [];
        $currentMonth = Carbon::now()->month;

        for ($m = 1; $m <= 12; $m++) {
            $rev2026 = (float) ($ordersCurrentYear[$m] ?? 0);
            $rev2025 = (float) ($ordersPreviousYear[$m] ?? 0);
            $ordersCount = (int) ($ordersCountCurrentYear[$m] ?? 0);

            $growthNum = $rev2025 > 0 ? (($rev2026 - $rev2025) / $rev2025) * 100 : ($rev2026 > 0 ? 100 : 0);
            $growthStr = ($growthNum >= 0 ? '+' : '') . number_format($growthNum, 1) . '%';

            $comparisonData[] = [
                'month' => $monthNames[$m - 1],
                'rev2026' => $rev2026,
                'rev2025' => $rev2025,
                'orders2026' => $ordersCount,
                'growth' => $growthStr,
            ];
        }

        // 2. DYNAMIC REAL-TIME QUERY: Total Aggregations from DB
        $totalRevenue = (float) SalesOrder::sum('total_amount');
        $totalLeads = Lead::count();
        $totalSalesOrders = SalesOrder::count();

        $qualifiedLeads = Lead::whereIn('status', ['qualified', 'converted'])->count();
        $conversionRate = $totalLeads > 0 ? number_format(($qualifiedLeads / $totalLeads) * 100, 1) : '0.0';

        // 3. DYNAMIC REAL-TIME QUERY: Lead Sources Breakdown from DB
        $sourcesDB = Lead::selectRaw('source, count(*) as count')
            ->groupBy('source')
            ->pluck('count', 'source')
            ->all();

        $sourceColorMap = [
            'referral' => ['label' => 'Referral Klien', 'color' => '#10b981'],
            'marketing' => ['label' => 'Marketing Ads', 'color' => '#0284c7'],
            'walk_in' => ['label' => 'Walk-In Customer', 'color' => '#f59e0b'],
            'phone' => ['label' => 'Telepon Hotline', 'color' => '#6366f1'],
            'digital' => ['label' => 'Kanal Digital / Website', 'color' => '#ec4899'],
            'event' => ['label' => 'Pameran / Event', 'color' => '#8b5cf6'],
        ];

        $leadSourceData = [];
        $totalSourceCount = array_sum($sourcesDB);

        foreach ($sourcesDB as $sourceKey => $count) {
            $key = is_object($sourceKey) && property_exists($sourceKey, 'value') ? $sourceKey->value : (string) $sourceKey;
            $meta = $sourceColorMap[$key] ?? ['label' => ucfirst(str_replace('_', ' ', $key)), 'color' => '#64748b'];
            $pct = $totalSourceCount > 0 ? number_format(($count / $totalSourceCount) * 100, 1) . '%' : '0%';

            $leadSourceData[] = [
                'name' => $meta['label'],
                'value' => (int) $count,
                'color' => $meta['color'],
                'percentage' => $pct,
            ];
        }

        // If no sources exist yet in DB, map standard lead enum values with 0
        if (empty($leadSourceData)) {
            $leadSourceData = [
                ['name' => 'Kanal Digital', 'value' => 0, 'color' => '#10b981', 'percentage' => '0%'],
                ['name' => 'Referral Klien', 'value' => 0, 'color' => '#0284c7', 'percentage' => '0%'],
            ];
        }

        // 4. DYNAMIC REAL-TIME QUERY: Activity Timeline Logs from DB
        $recentOrders = SalesOrder::with('customer')->latest()->take(4)->get()->map(function ($so) {
            return [
                'title' => "Pesanan Penjualan {$so->reference_no} Diterbitkan",
                'time' => $so->created_at ? $so->created_at->diffForHumans() : 'Baru saja',
                'desc' => "Nilai total pesanan " . ($so->customer->name ?? 'Pelanggan') . ": Rp " . number_format($so->total_amount, 0, ',', '.'),
            ];
        })->toArray();

        return Inertia::render('dashboard', [
            'dynamicData' => [
                'comparisonData' => $comparisonData,
                'totalRevenue' => $totalRevenue,
                'totalLeads' => $totalLeads,
                'totalSalesOrders' => $totalSalesOrders,
                'conversionRate' => $conversionRate,
                'leadSourceData' => $leadSourceData,
                'recentOrders' => $recentOrders,
                'currentYear' => $currentYear,
                'currentMonthName' => $monthNames[$currentMonth - 1],
            ],
        ]);
    }
}
