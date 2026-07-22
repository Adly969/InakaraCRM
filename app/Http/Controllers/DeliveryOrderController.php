<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeliveryOrderRequest;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Services\DeliveryOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DeliveryOrderController extends Controller
{
    public function __construct(
        protected DeliveryOrderService $deliveryOrderService
    ) {}

    /**
     * Display a listing of Delivery Orders.
     */
    public function index(Request $request): InertiaResponse
    {
        Gate::authorize('viewAny', DeliveryOrder::class);

        $query = DeliveryOrder::with(['salesOrder', 'warehouse', 'customer'])
            ->orderBy('reference_no', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('reference_no', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        return Inertia::render('delivery-orders/index', [
            'deliveryOrders' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'warehouse_id']),
            'warehouses' => Warehouse::where('status', 'active')->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new Delivery Order.
     */
    public function create(): InertiaResponse
    {
        Gate::authorize('create', DeliveryOrder::class);

        // Fetch active sales orders that have unfulfilled/outstanding items
        $salesOrders = SalesOrder::where('status', 'confirmed')
            ->with(['customer', 'items'])
            ->get();

        return Inertia::render('delivery-orders/create', [
            'salesOrders' => $salesOrders,
            'warehouses' => Warehouse::where('status', 'active')->get(['id', 'name']),
            'customers' => Customer::get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created Delivery Order in storage.
     */
    public function store(StoreDeliveryOrderRequest $request): RedirectResponse
    {
        $do = $this->deliveryOrderService->create($request->validated());

        return redirect()->route('delivery-orders.show', $do)
            ->with('success', 'Delivery Order created successfully as Draft.');
    }

    /**
     * Display the specified Delivery Order.
     */
    public function show(DeliveryOrder $deliveryOrder): InertiaResponse
    {
        Gate::authorize('view', $deliveryOrder);

        $deliveryOrder->load([
            'salesOrder',
            'warehouse',
            'customer',
            'items.salesOrderItem',
            'shipments.carrier',
            'shipments.driver',
            'events.creator',
        ]);

        return Inertia::render('delivery-orders/show', [
            'deliveryOrder' => $deliveryOrder,
        ]);
    }

    /**
     * Approve the specified Delivery Order.
     */
    public function approve(DeliveryOrder $deliveryOrder): RedirectResponse
    {
        Gate::authorize('approve', $deliveryOrder);

        $this->deliveryOrderService->approve($deliveryOrder);

        return redirect()->route('delivery-orders.show', $deliveryOrder)
            ->with('success', 'Delivery Order has been approved.');
    }

    /**
     * Cancel the specified Delivery Order.
     */
    public function cancel(DeliveryOrder $deliveryOrder, Request $request): RedirectResponse
    {
        Gate::authorize('cancel', $deliveryOrder);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->deliveryOrderService->cancel($deliveryOrder, $request->input('reason'));

        return redirect()->route('delivery-orders.show', $deliveryOrder)
            ->with('success', 'Delivery Order has been cancelled.');
    }
}
