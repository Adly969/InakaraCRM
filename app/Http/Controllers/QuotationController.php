<?php

namespace App\Http\Controllers;

use App\Enums\QuotationStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class QuotationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected QuotationService $quotationService) {}

    /**
     * Display a listing of the quotations.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Quotation::class);

        $query = Quotation::query();

        // Join customer and lead tables for search queries
        $query->leftJoin('customers', 'quotations.customer_id', '=', 'customers.id')
            ->leftJoin('leads', 'quotations.lead_id', '=', 'leads.id')
            ->select('quotations.*');

        // Search scoping
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quotations.reference_no', 'like', "%{$search}%")
                    ->orWhere('quotations.subject', 'like', "%{$search}%")
                    ->orWhere('customers.name', 'like', "%{$search}%")
                    ->orWhere('leads.name', 'like', "%{$search}%");
            });
        }

        // Filtering scoping
        if ($status = $request->input('status')) {
            $query->where('quotations.status', $status);
        }

        if ($assignedTo = $request->input('assigned_to')) {
            $query->where('quotations.assigned_to', $assignedTo);
        }

        // Row-level scoping
        $user = $request->user();
        if (! ($user->hasRole(UserRole::Owner->value) ||
               $user->hasRole(UserRole::Admin->value) ||
               $user->hasRole(UserRole::Manager->value) ||
               $user->hasRole(UserRole::CustomerService->value))) {
            $query->where('quotations.assigned_to', $user->id);
        }

        $quotations = $query->with(['customer', 'lead', 'assignedTo'])
            ->latest('quotations.created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('quotations/index', [
            'quotations' => $quotations,
            'filters' => $request->only(['search', 'status', 'assigned_to']),
        ]);
    }

    /**
     * Show the form for creating a new quotation.
     */
    public function create(): Response
    {
        Gate::authorize('create', Quotation::class);

        $customers = Customer::select(['id', 'name', 'company_name'])->get();
        $leads = Lead::select(['id', 'name', 'company_name'])->get();
        $users = User::select(['id', 'name'])->get();

        return Inertia::render('quotations/create', [
            'customers' => $customers,
            'leads' => $leads,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created quotation in storage.
     */
    public function store(StoreQuotationRequest $request): RedirectResponse
    {
        Gate::authorize('create', Quotation::class);

        $this->quotationService->create($request->validated(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Quotation created successfully.',
        ]);

        return to_route('quotations.index');
    }

    /**
     * Display the specified quotation.
     */
    public function show(Quotation $quotation): Response
    {
        Gate::authorize('view', $quotation);

        return Inertia::render('quotations/show', [
            'quotation' => $quotation->load(['items', 'customer', 'lead', 'assignedTo', 'creator', 'updater']),
        ]);
    }

    /**
     * Show the form for editing the specified quotation.
     */
    public function edit(Quotation $quotation): Response|RedirectResponse
    {
        Gate::authorize('update', $quotation);

        // Sent, accepted, or rejected quotations are locked against editing details
        if ($quotation->status !== QuotationStatus::Draft) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Quotations that are sent, accepted, or rejected cannot be edited.',
            ]);

            return to_route('quotations.show', $quotation);
        }

        $customers = Customer::select(['id', 'name', 'company_name'])->get();
        $leads = Lead::select(['id', 'name', 'company_name'])->get();
        $users = User::select(['id', 'name'])->get();

        return Inertia::render('quotations/edit', [
            'quotation' => $quotation->load('items'),
            'customers' => $customers,
            'leads' => $leads,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified quotation in storage.
     */
    public function update(Quotation $quotation, UpdateQuotationRequest $request): RedirectResponse
    {
        Gate::authorize('update', $quotation);

        $this->quotationService->update($quotation, $request->validated(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Quotation updated successfully.',
        ]);

        return to_route('quotations.index');
    }

    /**
     * Remove the specified quotation from storage.
     */
    public function destroy(Quotation $quotation): RedirectResponse
    {
        Gate::authorize('delete', $quotation);

        $quotation->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Quotation deleted successfully.',
        ]);

        return to_route('quotations.index');
    }
}
