<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Repositories\ReceivableRepository;
use App\Services\ReceivableAgingService;
use App\Services\ReceivableDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ReceivableController extends Controller
{
    public function __construct(
        protected ReceivableRepository $receivableRepository,
        protected ReceivableAgingService $agingService,
        protected ReceivableDashboardService $dashboardService
    ) {}

    /**
     * Display customer receivables index.
     */
    public function index(Request $request): InertiaResponse
    {
        Gate::authorize('viewReceivables', Payment::class);

        $summary = $this->receivableRepository->getOutstandingSummary();
        $metrics = $this->dashboardService->getMetrics();

        return Inertia::render('receivables/index', [
            'summary' => $summary,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Display aging matrix.
     */
    public function aging(): InertiaResponse
    {
        Gate::authorize('viewReceivables', Payment::class);

        $agingSummary = $this->agingService->getAgingSummary();

        return Inertia::render('receivables/aging', [
            'agingSummary' => $agingSummary,
        ]);
    }

    /**
     * Display a specific customer's receivables breakdown.
     */
    public function customer(Customer $customer): InertiaResponse
    {
        Gate::authorize('viewReceivables', Payment::class);

        $invoices = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['issued', 'overdue'])
            ->where('outstanding_balance', '>', 0)
            ->orderBy('due_date', 'asc')
            ->get();

        $payments = Payment::where('customer_id', $customer->id)
            ->with('allocations')
            ->orderBy('payment_date', 'desc')
            ->get();

        $aging = $this->agingService->getAgingByCustomer($customer->id);

        return Inertia::render('receivables/customer', [
            'customer' => $customer,
            'invoices' => $invoices,
            'payments' => $payments,
            'aging' => $aging,
        ]);
    }
}
