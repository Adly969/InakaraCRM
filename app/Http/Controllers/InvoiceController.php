<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): InertiaResponse
    {
        Gate::authorize('viewAny', Invoice::class);

        $query = Invoice::with(['customer', 'salesOrder', 'deliveryOrder'])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('reference_no', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        return Inertia::render('invoices/index', [
            'invoices' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'customer_id']),
            'customers' => Customer::get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(): InertiaResponse
    {
        Gate::authorize('create', Invoice::class);

        // Fetch source documents available for billing
        $salesOrders = SalesOrder::where('status', 'confirmed')
            ->with(['customer', 'items'])
            ->get();

        $deliveryOrders = DeliveryOrder::whereIn('status', ['approved', 'partially_shipped', 'shipped'])
            ->with(['customer', 'items'])
            ->get();

        return Inertia::render('invoices/create', [
            'salesOrders' => $salesOrders,
            'deliveryOrders' => $deliveryOrders,
            'customers' => Customer::get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $invoice = $this->invoiceService->create($request->validated());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice draft created successfully.');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): InertiaResponse
    {
        Gate::authorize('view', $invoice);

        $invoice->load([
            'customer',
            'salesOrder',
            'deliveryOrder',
            'items.salesOrderItem',
            'items.deliveryOrderItem',
            'adjustments',
            'events.creator',
        ]);

        return Inertia::render('invoices/show', [
            'invoice' => $invoice,
        ]);
    }
}
