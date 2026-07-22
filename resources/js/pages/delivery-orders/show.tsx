import { Head, Link, router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import type { DeliveryOrder, Shipment } from '@/types';
import { ArrowLeft, Check, X, Truck, Calendar, DollarSign, User as UserIcon, ShieldAlert } from 'lucide-react';
import React, { useState } from 'react';

interface Props {
    deliveryOrder: DeliveryOrder;
}

export default function ShowDeliveryOrder({ deliveryOrder }: Props) {
    const { can } = usePermission();
    const [showShipModal, setShowShipModal] = useState(false);
    const [selectedShipment, setSelectedShipment] = useState<Shipment | null>(null);
    const [showConfirmModal, setShowConfirmModal] = useState(false);

    // Form for creating shipment
    const shipForm = useForm({
        courier_type: 'internal',
        carrier_id: '',
        driver_id: '',
        tracking_number: '',
        estimated_cost: 0.00,
        currency: 'IDR',
        exchange_rate: 1.000000,
        estimated_delivery_date: '',
        items: (deliveryOrder.items || []).map((item) => ({
            delivery_order_item_id: item.id,
            sku: item.sku,
            description: item.description,
            quantity_shipped: Number(item.quantity_requested) - Number(item.quantity_shipped),
        })),
    });

    // Form for delivery confirmation
    const confirmForm = useForm({
        receiver_name: '',
        receiver_signature: 'data:image/png;base64,stub_signature',
        notes: '',
        gps_latitude: -8.650000,
        gps_longitude: 115.216667,
        actual_cost: 0.00,
    });

    const handleApprove = () => {
        if (confirm('Approve this Delivery Order? This will lock all details.')) {
            router.post(`/delivery-orders/${deliveryOrder.id}/approve`);
        }
    };

    const handleCancel = () => {
        const reason = prompt('Reason for cancellation:');
        if (reason !== null) {
            router.post(`/delivery-orders/${deliveryOrder.id}/cancel`, { reason });
        }
    };

    const handleCreateShipment = (e: React.FormEvent) => {
        e.preventDefault();
        shipForm.post(`/delivery-orders/${deliveryOrder.id}/shipments`, {
            onSuccess: () => {
                setShowShipModal(false);
                shipForm.reset();
            },
        });
    };

    const handleDispatch = (shipmentId: number) => {
        if (confirm('Dispatch shipment? Status will change to In Transit.')) {
            router.post(`/shipments/${shipmentId}/dispatch`);
        }
    };

    const handleOpenConfirm = (shipment: Shipment) => {
        setSelectedShipment(shipment);
        confirmForm.setData('actual_cost', Number(shipment.estimated_cost));
        setShowConfirmModal(true);
    };

    const handleConfirmDelivery = (e: React.FormEvent) => {
        e.preventDefault();
        if (selectedShipment) {
            confirmForm.post(`/shipments/${selectedShipment.id}/confirm`, {
                onSuccess: () => {
                    setShowConfirmModal(false);
                    setSelectedShipment(null);
                    confirmForm.reset();
                },
            });
        }
    };

    const handleFailShipment = (shipmentId: number) => {
        const reason = prompt('Enter delivery failure reason:');
        if (reason) {
            router.post(`/shipments/${shipmentId}/fail`, { reason });
        }
    };

    const handleReturnShipment = (shipmentId: number) => {
        const reason = prompt('Enter return reason (Stock will be adjusted back to warehouse):');
        if (reason) {
            router.post(`/shipments/${shipmentId}/return`, { reason });
        }
    };

    const getStatusBadgeClass = (status: string) => {
        switch (status) {
            case 'draft':
                return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
            case 'approved':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
            case 'partially_shipped':
                return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400';
            case 'shipped':
                return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400';
            case 'partially_delivered':
                return 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400';
            case 'delivered':
                return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
            case 'cancelled':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            default:
                return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
        }
    };

    const getStatusLabel = (status: string) => {
        return status.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
    };

    return (
        <>
            <Head title={`Delivery Order ${deliveryOrder.reference_no}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header Section */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button asChild variant="outline" size="icon">
                            <Link href="/delivery-orders">
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col gap-1">
                            <div className="flex items-center gap-2">
                                <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                    {deliveryOrder.reference_no}
                                </h1>
                                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${getStatusBadgeClass(deliveryOrder.status)}`}>
                                    {getStatusLabel(deliveryOrder.status)}
                                </span>
                            </div>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Sales Order Ref: {deliveryOrder.sales_order?.reference_no}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        {deliveryOrder.status === 'draft' && can('approve-delivery-orders') && (
                            <Button onClick={handleApprove} className="bg-emerald-600 hover:bg-emerald-700 text-white">
                                <Check className="mr-2 h-4 w-4" />
                                Approve DO
                            </Button>
                        )}
                        {['draft', 'approved', 'partially_shipped'].includes(deliveryOrder.status) && can('cancel-delivery-orders') && (
                            <Button onClick={handleCancel} variant="destructive">
                                <X className="mr-2 h-4 w-4" />
                                Cancel DO
                            </Button>
                        )}
                        {['approved', 'partially_shipped'].includes(deliveryOrder.status) && can('dispatch-shipments') && (
                            <Button onClick={() => setShowShipModal(true)} className="bg-indigo-600 hover:bg-indigo-700 text-white">
                                <Truck className="mr-2 h-4 w-4" />
                                Dispatch Shipment
                            </Button>
                        )}
                    </div>
                </div>

                {/* Grid Metadata */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm md:col-span-2">
                        <CardHeader>
                            <CardTitle className="text-base font-semibold">Shipping details</CardTitle>
                        </CardHeader>
                        <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 className="text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">Shipping Address Snapshot</h3>
                                <p className="text-sm font-medium text-neutral-900 dark:text-neutral-100">{deliveryOrder.shipping_address_snapshot.name}</p>
                                <p className="text-sm text-neutral-600 dark:text-neutral-400 whitespace-pre-line">{deliveryOrder.shipping_address_snapshot.address}</p>
                            </div>
                            <div>
                                <h3 className="text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">Billing Address Snapshot</h3>
                                <p className="text-sm font-medium text-neutral-900 dark:text-neutral-100">{deliveryOrder.billing_address_snapshot.name}</p>
                                <p className="text-sm text-neutral-600 dark:text-neutral-400 whitespace-pre-line">{deliveryOrder.billing_address_snapshot.address}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base font-semibold">General Metadata</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3">
                            <div>
                                <h3 className="text-xs font-semibold uppercase tracking-wider text-neutral-500">Warehouse Origin</h3>
                                <p className="text-sm text-neutral-800 dark:text-neutral-200 font-medium">{deliveryOrder.warehouse?.name}</p>
                            </div>
                            <div>
                                <h3 className="text-xs font-semibold uppercase tracking-wider text-neutral-500">Customer Profile</h3>
                                <p className="text-sm text-neutral-800 dark:text-neutral-200 font-medium">{deliveryOrder.customer?.name}</p>
                            </div>
                            {deliveryOrder.approved_at && (
                                <div>
                                    <h3 className="text-xs font-semibold uppercase tracking-wider text-neutral-500">Approved Date</h3>
                                    <p className="text-sm text-neutral-800 dark:text-neutral-200 font-medium">{deliveryOrder.approved_at}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Items requested grid */}
                <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base font-semibold">Delivery Order Line Items</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="relative w-full overflow-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-neutral-100/50 dark:bg-neutral-800/50 border-b border-neutral-200 dark:border-neutral-800">
                                    <tr>
                                        <th className="p-3 text-left">SKU</th>
                                        <th className="p-3 text-left">Description</th>
                                        <th className="p-3 text-center">Requested Qty</th>
                                        <th className="p-3 text-center">Shipped Qty</th>
                                        <th className="p-3 text-center">Delivered Qty</th>
                                        <th className="p-3 text-left">Unit</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {(deliveryOrder.items || []).map((item) => (
                                        <tr key={item.id}>
                                            <td className="p-3 font-medium text-neutral-900 dark:text-neutral-100">{item.sku}</td>
                                            <td className="p-3 text-neutral-700 dark:text-neutral-300">{item.description}</td>
                                            <td className="p-3 text-center font-semibold">{item.quantity_requested}</td>
                                            <td className="p-3 text-center text-indigo-600 dark:text-indigo-400 font-semibold">{item.quantity_shipped}</td>
                                            <td className="p-3 text-center text-emerald-600 dark:text-emerald-400 font-semibold">{item.quantity_delivered}</td>
                                            <td className="p-3 text-neutral-500">{item.unit}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {/* Active Dispatched Shipments */}
                {(deliveryOrder.shipments || []).length > 0 && (
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base font-bold">Logistics Dispatched Shipments</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            {deliveryOrder.shipments?.map((ship) => (
                                <div key={ship.id} className="border border-neutral-200 dark:border-neutral-800 rounded-lg p-4 bg-white dark:bg-neutral-950 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div className="flex flex-col gap-1">
                                        <div className="flex items-center gap-2">
                                            <span className="font-semibold text-sm text-neutral-900 dark:text-neutral-50">{ship.reference_no}</span>
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-2xs font-medium ${getStatusBadgeClass(ship.status)}`}>
                                                {getStatusLabel(ship.status)}
                                            </span>
                                        </div>
                                        <div className="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-1 text-xs text-neutral-500">
                                            <div>Courier: {getStatusLabel(ship.courier_type)} {ship.carrier?.name && `(${ship.carrier.name})`}</div>
                                            {ship.driver && <div>Driver: {ship.driver.name} ({ship.driver.vehicle_plate_no})</div>}
                                            {ship.tracking_number && <div>Tracking No: {ship.tracking_number}</div>}
                                            <div>Est Cost: {ship.estimated_cost} {ship.currency}</div>
                                        </div>
                                    </div>

                                    <div className="flex flex-wrap gap-2">
                                        {ship.status === 'pending_dispatch' && can('dispatch-shipments') && (
                                            <Button onClick={() => handleDispatch(ship.id)} size="sm" className="bg-indigo-600 hover:bg-indigo-700 text-white">
                                                Dispatch Outbound
                                            </Button>
                                        )}
                                        {ship.status === 'in_transit' && can('confirm-deliveries') && (
                                            <>
                                                <Button onClick={() => handleOpenConfirm(ship)} size="sm" className="bg-emerald-600 hover:bg-emerald-700 text-white">
                                                    Confirm Delivery
                                                </Button>
                                                <Button onClick={() => handleFailShipment(ship.id)} size="sm" variant="outline" className="text-red-600 border-red-200 hover:bg-red-50">
                                                    Failed Delivery
                                                </Button>
                                            </>
                                        )}
                                        {['in_transit', 'failed_delivery'].includes(ship.status) && can('confirm-deliveries') && (
                                            <Button onClick={() => handleReturnShipment(ship.id)} size="sm" variant="secondary">
                                                Return Cargo
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                )}

                {/* Audit events logging */}
                <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base font-semibold">Delivery Audit Logs</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        {(deliveryOrder.events || []).map((ev) => (
                            <div key={ev.id} className="flex gap-4 text-sm border-l-2 border-indigo-200 dark:border-indigo-800 pl-4 py-1">
                                <div className="min-w-[120px] text-neutral-450">{ev.created_at}</div>
                                <div className="flex-1">
                                    <span className="font-semibold uppercase text-xs tracking-wider mr-2 bg-indigo-50 dark:bg-indigo-900/25 px-1.5 py-0.5 rounded text-indigo-700 dark:text-indigo-400">{ev.event_type}</span>
                                    <span className="text-neutral-700 dark:text-neutral-350">{ev.creator?.name && `By ${ev.creator.name}`}</span>
                                    {ev.ip_address && <span className="text-2xs text-neutral-400 ml-2">({ev.ip_address})</span>}
                                </div>
                            </div>
                        ))}
                    </CardContent>
                </Card>

                {/* Ship cargo Modal */}
                {showShipModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <Card className="w-full max-w-lg border-neutral-200/80 dark:border-neutral-800/80 shadow-sm bg-white dark:bg-neutral-900">
                            <CardHeader>
                                <CardTitle className="text-base font-bold">Dispatch Cargo Shipment</CardTitle>
                            </CardHeader>
                            <form onSubmit={handleCreateShipment}>
                                <CardContent className="flex flex-col gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">Courier Type</label>
                                        <select
                                            value={shipForm.data.courier_type}
                                            onChange={(e) => shipForm.setData('courier_type', e.target.value)}
                                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                        >
                                            <option value="internal">Internal Driver</option>
                                            <option value="third_party">Third Party Courier</option>
                                            <option value="expedition">Expedition Partner</option>
                                            <option value="pickup">Customer Pickup</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">Driver ID (For internal)</label>
                                        <input
                                            type="text"
                                            value={shipForm.data.driver_id}
                                            onChange={(e) => shipForm.setData('driver_id', e.target.value)}
                                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                            placeholder="Internal driver ID if available..."
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">Carrier ID (For expedition)</label>
                                        <input
                                            type="text"
                                            value={shipForm.data.carrier_id}
                                            onChange={(e) => shipForm.setData('carrier_id', e.target.value)}
                                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                            placeholder="Carrier ID if third-party..."
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">Tracking Number</label>
                                        <input
                                            type="text"
                                            value={shipForm.data.tracking_number}
                                            onChange={(e) => shipForm.setData('tracking_number', e.target.value)}
                                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                            placeholder="Courier tracking reference code..."
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">Estimated Cost (IDR)</label>
                                        <input
                                            type="number"
                                            value={shipForm.data.estimated_cost}
                                            onChange={(e) => shipForm.setData('estimated_cost', Number(e.target.value))}
                                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                        />
                                    </div>

                                    <div className="flex justify-end gap-3 mt-4">
                                        <Button type="button" variant="outline" onClick={() => setShowShipModal(false)}>Close</Button>
                                        <Button type="submit" disabled={shipForm.processing} className="bg-indigo-600 hover:bg-indigo-700 text-white">Save Dispatch</Button>
                                    </div>
                                </CardContent>
                            </form>
                        </Card>
                    </div>
                )}

                {/* Confirm Delivery Modal */}
                {showConfirmModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <Card className="w-full max-w-lg border-neutral-200/80 dark:border-neutral-800/80 shadow-sm bg-white dark:bg-neutral-900">
                            <CardHeader>
                                <CardTitle className="text-base font-bold">Confirm Delivery Receipt</CardTitle>
                            </CardHeader>
                            <form onSubmit={handleConfirmDelivery}>
                                <CardContent className="flex flex-col gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">Receiver Name</label>
                                        <input
                                            type="text"
                                            required
                                            value={confirmForm.data.receiver_name}
                                            onChange={(e) => confirmForm.setData('receiver_name', e.target.value)}
                                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                            placeholder="Enter recipient full name..."
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">Actual Shipping Cost Paid</label>
                                        <input
                                            type="number"
                                            value={confirmForm.data.actual_cost}
                                            onChange={(e) => confirmForm.setData('actual_cost', Number(e.target.value))}
                                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">Receiver Signature Proof</label>
                                        <div className="border border-neutral-200 dark:border-neutral-800 rounded bg-neutral-100 dark:bg-neutral-950 p-4 text-center text-xs text-neutral-500">
                                            [ HTML5 Canvas Signature Pad Stub ]
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold uppercase tracking-wider text-neutral-500 mb-1">Fulfillment Notes</label>
                                        <textarea
                                            value={confirmForm.data.notes}
                                            onChange={(e) => confirmForm.setData('notes', e.target.value)}
                                            rows={2}
                                            className="flex w-full rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm dark:border-neutral-800 dark:bg-neutral-950"
                                            placeholder="Add remarks on cargo status..."
                                        />
                                    </div>

                                    <div className="flex justify-end gap-3 mt-4">
                                        <Button type="button" variant="outline" onClick={() => setShowConfirmModal(false)}>Close</Button>
                                        <Button type="submit" disabled={confirmForm.processing} className="bg-emerald-600 hover:bg-emerald-700 text-white">Post Confirmation</Button>
                                    </div>
                                </CardContent>
                            </form>
                        </Card>
                    </div>
                )}
            </div>
        </>
    );
}
