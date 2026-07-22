<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentPostingService $postingService
    ) {}

    /**
     * Display a listing of payments.
     */
    public function index(Request $request): InertiaResponse
    {
        Gate::authorize('viewAny', Payment::class);

        $query = Payment::with(['customer', 'creator'])
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

        return Inertia::render('payments/index', [
            'payments' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'customer_id']),
            'customers' => Customer::get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(): InertiaResponse
    {
        Gate::authorize('create', Payment::class);

        // Load open invoices (issued or overdue) for dynamic allocation forms
        $invoices = Invoice::whereIn('status', ['issued', 'overdue'])
            ->where('outstanding_balance', '>', 0)
            ->with('customer')
            ->get();

        return Inertia::render('payments/create', [
            'invoices' => $invoices,
            'customers' => Customer::get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created payment draft.
     */
    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $payment = $this->postingService->create($request->validated());

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment draft created successfully.');
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment): InertiaResponse
    {
        Gate::authorize('view', $payment);

        $payment->load([
            'customer',
            'allocations.invoice',
            'events.creator',
            'attachments.uploader',
            'histories.modifier',
        ]);

        return Inertia::render('payments/show', [
            'payment' => $payment,
        ]);
    }
}
