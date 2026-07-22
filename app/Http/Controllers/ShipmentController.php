<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentRequest;
use App\Models\Carrier;
use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\Shipment;
use App\Services\ShipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService
    ) {}

    /**
     * Display a listing of shipments/dispatches.
     */
    public function index(): InertiaResponse
    {
        Gate::authorize('view-delivery-orders');

        $shipments = Shipment::with(['deliveryOrder.customer', 'carrier', 'driver'])
            ->orderBy('reference_no', 'desc')
            ->paginate(10);

        return Inertia::render('shipments/index', [
            'shipments' => $shipments,
            'carriers' => Carrier::where('status', 'active')->get(['id', 'name']),
            'drivers' => Driver::where('status', 'active')->get(['id', 'name', 'vehicle_plate_no']),
        ]);
    }

    /**
     * Store a newly created Shipment in storage.
     */
    public function store(DeliveryOrder $deliveryOrder, StoreShipmentRequest $request): RedirectResponse
    {
        $this->shipmentService->createShipment($deliveryOrder, $request->validated());

        return redirect()->route('delivery-orders.show', $deliveryOrder)
            ->with('success', 'Shipment dispatched successfully.');
    }

    /**
     * Dispatch a pending shipment to in_transit status.
     */
    public function dispatchShipment(Shipment $shipment): RedirectResponse
    {
        Gate::authorize('dispatch', $shipment);

        $this->shipmentService->dispatch($shipment);

        return redirect()->route('delivery-orders.show', $shipment->delivery_order_id)
            ->with('success', 'Shipment status updated to In Transit.');
    }
}
