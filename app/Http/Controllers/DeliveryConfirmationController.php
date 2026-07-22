<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmDeliveryRequest;
use App\Models\Shipment;
use App\Services\DeliveryConfirmationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeliveryConfirmationController extends Controller
{
    public function __construct(
        protected DeliveryConfirmationService $confirmationService
    ) {}

    /**
     * Confirm delivery of the shipment (delivered).
     */
    public function confirm(Shipment $shipment, ConfirmDeliveryRequest $request): RedirectResponse
    {
        Gate::authorize('confirm', $shipment);

        $this->confirmationService->confirmDelivery($shipment, $request->validated());

        return redirect()->route('delivery-orders.show', $shipment->delivery_order_id)
            ->with('success', 'Delivery has been successfully confirmed.');
    }

    /**
     * Mark shipment as failed delivery.
     */
    public function fail(Shipment $shipment, Request $request): RedirectResponse
    {
        Gate::authorize('confirm', $shipment);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->confirmationService->failDelivery($shipment, $request->input('reason'));

        return redirect()->route('delivery-orders.show', $shipment->delivery_order_id)
            ->with('success', 'Shipment marked as Failed Delivery.');
    }

    /**
     * Mark shipment as returned.
     */
    public function returnShipment(Shipment $shipment, Request $request): RedirectResponse
    {
        Gate::authorize('confirm', $shipment);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->confirmationService->processReturn($shipment, $request->input('reason'));

        return redirect()->route('delivery-orders.show', $shipment->delivery_order_id)
            ->with('success', 'Shipment marked as Returned. Stock has been re-adjusted.');
    }
}
