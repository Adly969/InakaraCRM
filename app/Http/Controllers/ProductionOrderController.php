<?php

namespace App\Http\Controllers;

use App\Enums\ProductionOrderStatus;
use App\Http\Requests\StoreProductionOrderRequest;
use App\Http\Requests\UpdateProductionOrderRequest;
use App\Models\Customer;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\ProductionOrderService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProductionOrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ProductionOrderService $productionOrderService
    ) {}

    /**
     * Display a listing of the production orders.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', ProductionOrder::class);

        $productionOrders = ProductionOrder::query()
            ->search($request->input('search'))
            ->status($request->input('status'))
            ->priority($request->input('priority'))
            ->assigned($request->input('assigned_to'))
            ->visibleTo($request->user())
            ->with(['customer', 'assignedTo'])
            ->latest('production_orders.created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('production-orders/index', [
            'productionOrders' => $productionOrders,
            'filters' => $request->only(['search', 'status', 'priority', 'assigned_to']),
        ]);
    }

    /**
     * Show the form for creating a new production order.
     */
    public function create(): Response
    {
        Gate::authorize('create', ProductionOrder::class);

        $customers = Customer::select(['id', 'name', 'company_name'])->get();
        $users = User::select(['id', 'name'])->get();

        return Inertia::render('production-orders/create', [
            'customers' => $customers,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created production order in storage.
     */
    public function store(StoreProductionOrderRequest $request): RedirectResponse
    {
        Gate::authorize('create', ProductionOrder::class);

        $po = $this->productionOrderService->create($request->validated(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Production order created successfully.',
        ]);

        return to_route('production-orders.show', $po);
    }

    /**
     * Display the specified production order.
     */
    public function show(ProductionOrder $productionOrder): Response
    {
        Gate::authorize('view', $productionOrder);

        $productionOrder->load([
            'items',
            'customer',
            'salesOrder',
            'assignedTo',
            'creator',
            'updater',
            'logs.creator',
        ]);

        return Inertia::render('production-orders/show', [
            'productionOrder' => $productionOrder,
        ]);
    }

    /**
     * Show the form for editing the specified production order.
     */
    public function edit(ProductionOrder $productionOrder): Response|RedirectResponse
    {
        Gate::authorize('update', $productionOrder);

        if (! in_array($productionOrder->status, [ProductionOrderStatus::Draft, ProductionOrderStatus::Scheduled], true)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Only draft or scheduled production orders can be fully edited.',
            ]);

            return to_route('production-orders.show', $productionOrder);
        }

        $customers = Customer::select(['id', 'name', 'company_name'])->get();
        $users = User::select(['id', 'name'])->get();

        return Inertia::render('production-orders/edit', [
            'productionOrder' => $productionOrder->load('items'),
            'customers' => $customers,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified production order in storage.
     */
    public function update(ProductionOrder $productionOrder, UpdateProductionOrderRequest $request): RedirectResponse
    {
        Gate::authorize('update', $productionOrder);

        try {
            $this->productionOrderService->update($productionOrder, $request->validated(), $request->user());

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Production order updated successfully.',
            ]);

            return to_route('production-orders.show', $productionOrder);
        } catch (ValidationException $e) {
            throw $e;
        } catch (DomainException $e) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);

            return back();
        }
    }

    /**
     * Remove the specified production order from storage.
     */
    public function destroy(ProductionOrder $productionOrder): RedirectResponse
    {
        Gate::authorize('delete', $productionOrder);

        $productionOrder->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Production order deleted successfully.',
        ]);

        return to_route('production-orders.index');
    }

    /**
     * Convert a Sales Order to a Production Order.
     */
    public function convertFromSalesOrder(SalesOrder $salesOrder, Request $request): RedirectResponse
    {
        Gate::authorize('create', ProductionOrder::class);

        try {
            $po = $this->productionOrderService->createFromSalesOrder($salesOrder, $request->user());

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Sales Order successfully converted to Production Order.',
            ]);

            return to_route('production-orders.show', $po);
        } catch (DomainException $e) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);

            return back();
        }
    }
}
