<?php

namespace App\Http\Controllers;

use App\Enums\SalesOrderStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\CreditLimitValidator;
use App\Services\SalesOrderService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SalesOrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected SalesOrderService $salesOrderService) {}

    /**
     * Display a listing of the sales orders.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', SalesOrder::class);

        $query = SalesOrder::query();

        // Join customer table for search queries
        $query->leftJoin('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->select('sales_orders.*');

        // Search scoping
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sales_orders.reference_no', 'like', "%{$search}%")
                    ->orWhere('sales_orders.subject', 'like', "%{$search}%")
                    ->orWhere('customers.name', 'like', "%{$search}%");
            });
        }

        // Filtering scoping
        if ($status = $request->input('status')) {
            $query->where('sales_orders.status', $status);
        }

        if ($assignedTo = $request->input('assigned_to')) {
            $query->where('sales_orders.assigned_to', $assignedTo);
        }

        // Row-level scoping
        $user = $request->user();
        if (! ($user->hasRole(UserRole::Owner->value) ||
               $user->hasRole(UserRole::Admin->value) ||
               $user->hasRole(UserRole::Manager->value) ||
               $user->hasRole(UserRole::CustomerService->value))) {
            $query->where('sales_orders.assigned_to', $user->id);
        }

        $salesOrders = $query->with(['customer', 'assignedTo'])
            ->latest('sales_orders.created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('sales-orders/index', [
            'salesOrders' => $salesOrders,
            'filters' => $request->only(['search', 'status', 'assigned_to']),
        ]);
    }

    /**
     * Show the form for creating a new sales order.
     */
    public function create(): Response
    {
        Gate::authorize('create', SalesOrder::class);

        $customers = Customer::select(['id', 'name', 'company_name'])->get();
        $users = User::select(['id', 'name'])->get();

        return Inertia::render('sales-orders/create', [
            'customers' => $customers,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created sales order in storage.
     */
    public function store(StoreSalesOrderRequest $request): RedirectResponse
    {
        Gate::authorize('create', SalesOrder::class);

        $this->salesOrderService->create($request->validated(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Sales order created successfully.',
        ]);

        return to_route('sales-orders.index');
    }

    /**
     * Display the specified sales order.
     */
    public function show(SalesOrder $salesOrder): Response
    {
        Gate::authorize('view', $salesOrder);

        return Inertia::render('sales-orders/show', [
            'salesOrder' => $salesOrder->load(['items', 'customer', 'quotation', 'assignedTo', 'creator', 'updater', 'productionOrder']),
        ]);
    }

    /**
     * Show the form for editing the specified sales order.
     */
    public function edit(SalesOrder $salesOrder): Response|RedirectResponse
    {
        Gate::authorize('update', $salesOrder);

        if ($salesOrder->status !== SalesOrderStatus::Draft) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Sales orders that are confirmed or cancelled cannot be edited.',
            ]);

            return to_route('sales-orders.show', $salesOrder);
        }

        $customers = Customer::select(['id', 'name', 'company_name'])->get();
        $users = User::select(['id', 'name'])->get();

        return Inertia::render('sales-orders/edit', [
            'salesOrder' => $salesOrder->load('items'),
            'customers' => $customers,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified sales order in storage.
     */
    public function update(SalesOrder $salesOrder, UpdateSalesOrderRequest $request): RedirectResponse
    {
        Gate::authorize('update', $salesOrder);

        $this->salesOrderService->update($salesOrder, $request->validated(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Sales order updated successfully.',
        ]);

        return to_route('sales-orders.show', $salesOrder);
    }

    /**
     * Remove the specified sales order from storage.
     */
    public function destroy(SalesOrder $salesOrder): RedirectResponse
    {
        Gate::authorize('delete', $salesOrder);

        $salesOrder->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Sales order deleted successfully.',
        ]);

        return to_route('sales-orders.index');
    }

    /**
     * Convert a Quotation into a Sales Order.
     */
    public function convertFromQuotation(Quotation $quotation, Request $request): RedirectResponse
    {
        Gate::authorize('create', SalesOrder::class);

        try {
            $salesOrder = $this->salesOrderService->createFromQuotation($quotation, $request->user());

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Quotation successfully converted to Sales Order.',
            ]);

            return to_route('sales-orders.show', $salesOrder);
        } catch (DomainException $e) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);

            return back();
        }
    }

    /**
     * Release a credit hold on a Sales Order manually.
     */
    public function releaseCreditHold(SalesOrder $salesOrder, Request $request): RedirectResponse
    {
        Gate::authorize('update', $salesOrder);

        $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        try {
            $validator = app(CreditLimitValidator::class);
            $validator->releaseHold($salesOrder, $request->user(), $request->input('reason'));

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Credit hold manually overridden and released successfully.',
            ]);
        } catch (\Throwable $e) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        return to_route('sales-orders.show', $salesOrder);
    }
}
