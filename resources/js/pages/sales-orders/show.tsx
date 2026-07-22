import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexSalesOrdersRoute, edit as editSalesOrderRoute, update as updateSalesOrderRoute } from '@/routes/sales-orders';
import type { SalesOrder } from '@/types';
import { ArrowLeft, Pencil, AlertCircle } from 'lucide-react';
import { router } from '@inertiajs/react';

interface Props {
    salesOrder: SalesOrder;
}

export default function SalesOrderShow({ salesOrder }: Props) {
    const { can } = usePermission();

    const isDraft = salesOrder.status === 'draft';
    const isConfirmed = salesOrder.status === 'confirmed';

    const handleStatusChange = (newStatus: string) => {
        if (confirm(`Are you sure you want to transition this sales order to ${newStatus}?`)) {
            router.put(updateSalesOrderRoute(salesOrder.id).url, {
                customer_id: salesOrder.customer_id,
                quotation_id: salesOrder.quotation_id,
                subject: salesOrder.subject,
                delivery_terms: salesOrder.delivery_terms,
                notes: salesOrder.notes,
                currency: salesOrder.currency,
                tax_rate: salesOrder.tax_rate,
                items: (salesOrder.items ?? []) as any,
                status: newStatus,
            });
        }
    };

    const handleCancelOrder = () => {
        const reason = prompt('Please enter the reason for cancelling this sales order:');
        if (reason === null) return; // User clicked Cancel
        if (!reason.trim()) {
            alert('A cancellation reason is required.');
            return;
        }

        router.put(updateSalesOrderRoute(salesOrder.id).url, {
            customer_id: salesOrder.customer_id,
            quotation_id: salesOrder.quotation_id,
            subject: salesOrder.subject,
            delivery_terms: salesOrder.delivery_terms,
            notes: salesOrder.notes,
            currency: salesOrder.currency,
            tax_rate: salesOrder.tax_rate,
            items: (salesOrder.items ?? []) as any,
            status: 'cancelled',
            cancellation_reason: reason,
        });
    };

    const formatDate = (dateString?: string) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('en-US', {
            dateStyle: 'medium',
        });
    };

    return (
        <>
            <Head title={`Sales Order - ${salesOrder.reference_no ?? salesOrder.subject}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={indexSalesOrdersRoute()}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col gap-0.5">
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {salesOrder.reference_no ?? 'Reference Pending'}
                            </h1>
                            <p className="text-xs text-neutral-500">
                                {salesOrder.subject}
                            </p>
                        </div>
                    </div>
                    {can('edit-sales-orders') && isDraft && (
                        <Button asChild variant="outline">
                            <Link href={editSalesOrderRoute(salesOrder.id)}>
                                <Pencil className="mr-2 h-4 w-4" />
                                Edit Sales Order
                            </Link>
                        </Button>
                    )}
                </div>

                {salesOrder.status === 'cancelled' && (
                    <div className="flex items-start gap-3 p-4 rounded-lg bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50">
                        <AlertCircle className="h-5 w-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                        <div className="flex flex-col gap-1">
                            <span className="text-sm font-semibold text-red-800 dark:text-red-200">
                                Sales Order Cancelled
                            </span>
                            <span className="text-xs text-red-700 dark:text-red-300">
                                <span className="font-semibold">Reason:</span> {salesOrder.cancellation_reason ?? 'No reason provided.'}
                            </span>
                        </div>
                    </div>
                )}

                {(salesOrder as any).credit_hold_status === 'hold' && (
                    <div className="flex flex-col gap-3 p-4 rounded-lg bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50">
                        <div className="flex items-start gap-3">
                            <AlertCircle className="h-5 w-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                            <div className="flex flex-col gap-1">
                                <span className="text-sm font-semibold text-amber-800 dark:text-amber-200">
                                    Credit Hold Active
                                </span>
                                <span className="text-xs text-amber-700 dark:text-amber-300">
                                    This sales order exceeds the customer exposure credit limit and is currently locked.
                                </span>
                            </div>
                        </div>
                        <form onSubmit={(e) => {
                            e.preventDefault();
                            const reason = new FormData(e.currentTarget).get('reason') as string;
                            if (!reason || reason.trim().length < 5) {
                                alert('A release override reason of at least 5 characters is required.');
                                return;
                            }
                            router.post(`/sales-orders/${salesOrder.id}/release-credit`, { reason });
                        }} className="flex items-center gap-2">
                            <input
                                name="reason"
                                type="text"
                                placeholder="Enter justification to release hold..."
                                className="flex h-9 w-full max-w-sm rounded-md border border-neutral-200 bg-white dark:bg-neutral-900 px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-neutral-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-neutral-950 disabled:cursor-not-allowed disabled:opacity-50 dark:border-neutral-800 dark:focus-visible:ring-neutral-300 text-neutral-900 dark:text-neutral-50"
                            />
                            <Button type="submit" size="sm" className="bg-amber-600 hover:bg-amber-700 text-white shrink-0">
                                Override & Release
                            </Button>
                        </form>
                    </div>
                )}

                {(salesOrder as any).credit_hold_status === 'released' && (
                    <div className="flex items-start gap-3 p-4 rounded-lg bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900/50">
                        <AlertCircle className="h-5 w-5 text-green-600 dark:text-green-400 shrink-0 mt-0.5" />
                        <div className="flex flex-col gap-1">
                            <span className="text-sm font-semibold text-green-800 dark:text-green-200">
                                Credit Hold Released (Override)
                            </span>
                            <span className="text-xs text-green-700 dark:text-green-300">
                                <span className="font-semibold">Justification:</span> {(salesOrder as any).credit_hold_override_reason}
                            </span>
                        </div>
                    </div>
                )}

                <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    {/* Left main information column */}
                    <div className="lg:col-span-3 flex flex-col gap-6">
                        {/* Header Details */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold">General Details</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Customer</span>
                                    <span className="text-sm font-medium">
                                        {salesOrder.customer ? salesOrder.customer.name : '-'}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Subject</span>
                                    <span className="text-sm font-medium">{salesOrder.subject}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Quotation Link</span>
                                    <span className="text-sm font-medium">
                                        {salesOrder.quotation ? (
                                            <Link
                                                href={`/quotations/${salesOrder.quotation_id}`}
                                                className="text-neutral-900 dark:text-neutral-50 underline font-semibold"
                                            >
                                                {salesOrder.quotation.reference_no ?? 'View Source Quotation'}
                                            </Link>
                                        ) : '-'}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Currency</span>
                                    <span className="text-sm font-medium">{salesOrder.currency}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Assigned To</span>
                                    <span className="text-sm font-medium">
                                        {salesOrder.assigned_to_user?.name ?? '-'}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Delivery Terms</span>
                                    <span className="text-sm font-medium">{salesOrder.delivery_terms ?? '-'}</span>
                                </div>
                                {salesOrder.notes && (
                                    <div className="sm:col-span-2 flex flex-col gap-1">
                                        <span className="text-xs text-neutral-500">Notes</span>
                                        <span className="text-sm text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-950 p-3 rounded border border-neutral-200 dark:border-neutral-800">
                                            {salesOrder.notes}
                                        </span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Line Items Table */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50 overflow-hidden">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold">Sales Order Line Items</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                <table className="w-full text-sm">
                                    <thead className="border-b border-neutral-200 bg-neutral-100/50 dark:border-neutral-800 dark:bg-neutral-800/50">
                                        <tr>
                                            <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">Description</th>
                                            <th className="h-10 px-4 text-right align-middle font-medium text-neutral-500 dark:text-neutral-400">Qty</th>
                                            <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">Unit</th>
                                            <th className="h-10 px-4 text-right align-middle font-medium text-neutral-500 dark:text-neutral-400">Unit Price</th>
                                            <th className="h-10 px-4 text-right align-middle font-medium text-neutral-500 dark:text-neutral-400">Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                        {salesOrder.items?.map((item) => (
                                            <tr key={item.id}>
                                                <td className="p-4 align-middle font-medium text-neutral-900 dark:text-neutral-50">{item.description}</td>
                                                <td className="p-4 align-middle text-right font-mono">{Number(item.quantity).toLocaleString()}</td>
                                                <td className="p-4 align-middle">{item.unit}</td>
                                                <td className="p-4 align-middle text-right font-mono">{Number(item.unit_price).toLocaleString()}</td>
                                                <td className="p-4 align-middle text-right font-mono">{Number(item.total_price).toLocaleString()}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>

                                {/* Totals Block */}
                                <div className="border-t border-neutral-200 dark:border-neutral-800 p-6 flex flex-col items-end gap-2 bg-white dark:bg-neutral-950/20">
                                    <div className="flex justify-between w-64 text-sm text-neutral-600 dark:text-neutral-400">
                                        <span>Subtotal:</span>
                                        <span className="font-mono">{salesOrder.currency} {Number(salesOrder.subtotal).toLocaleString()}</span>
                                    </div>
                                    <div className="flex justify-between w-64 text-sm text-neutral-600 dark:text-neutral-400">
                                        <span>Tax ({Number(salesOrder.tax_rate)}%):</span>
                                        <span className="font-mono">{salesOrder.currency} {Number(salesOrder.tax_amount).toLocaleString()}</span>
                                    </div>
                                    <div className="flex justify-between w-64 text-base font-bold text-neutral-900 dark:text-neutral-50 pt-2 border-t border-neutral-200 dark:border-neutral-800">
                                        <span>Total:</span>
                                        <span className="font-mono">{salesOrder.currency} {Number(salesOrder.total_amount).toLocaleString()}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Right column sidebar */}
                    <div className="lg:col-span-1 flex flex-col gap-6">
                        {/* Status Transition Card */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold">Status Transitions</CardTitle>
                            </CardHeader>
                            <CardContent className="p-4 flex flex-col gap-2">
                                <div className="mb-2">
                                    <span className="text-xs text-neutral-500 block">Current Status:</span>
                                    <span className="text-sm font-semibold capitalize text-neutral-900 dark:text-neutral-50">{salesOrder.status}</span>
                                </div>

                                {isDraft && (
                                    <>
                                        <Button
                                            onClick={() => handleStatusChange('confirmed')}
                                            className="w-full text-xs h-9 justify-center bg-green-600 hover:bg-green-700 text-white dark:bg-green-700 dark:hover:bg-green-800"
                                        >
                                            Confirm Order
                                        </Button>
                                        <Button
                                            variant="outline"
                                            onClick={handleCancelOrder}
                                            className="w-full text-xs h-9 justify-center border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20"
                                        >
                                            Cancel Order
                                        </Button>
                                    </>
                                )}

                                {isConfirmed && (
                                    <Button
                                        variant="outline"
                                        onClick={handleCancelOrder}
                                        className="w-full text-xs h-9 justify-center border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20"
                                    >
                                        Cancel Order
                                    </Button>
                                )}

                                {salesOrder.status === 'cancelled' && (
                                    <p className="text-xs text-neutral-500 text-center py-2 bg-white dark:bg-neutral-950/30 rounded border border-dashed border-neutral-200 dark:border-neutral-800">
                                        This sales order is cancelled.
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        {/* Production Status Card */}
                        {isConfirmed && (
                            <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                                <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                    <CardTitle className="text-base font-semibold">Production Status</CardTitle>
                                </CardHeader>
                                <CardContent className="p-4 flex flex-col gap-2">
                                    {salesOrder.production_order ? (
                                        <div className="flex flex-col gap-2">
                                            <div className="flex justify-between items-center text-xs">
                                                <span className="text-neutral-500">Status:</span>
                                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ring-inset ${
                                                    salesOrder.production_order.status === 'draft' ? 'bg-neutral-50 text-neutral-600 ring-neutral-500/20' :
                                                    salesOrder.production_order.status === 'scheduled' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' :
                                                    salesOrder.production_order.status === 'in_production' ? 'bg-amber-50 text-amber-700 ring-amber-600/20' :
                                                    salesOrder.production_order.status === 'quality_control' ? 'bg-indigo-50 text-indigo-700 ring-indigo-600/20' :
                                                    salesOrder.production_order.status === 'completed' ? 'bg-green-50 text-green-700 ring-green-600/20' :
                                                    'bg-red-50 text-red-700 ring-red-600/20' // cancelled
                                                }`}>
                                                    {salesOrder.production_order.status.replace('_', ' ')}
                                                </span>
                                            </div>
                                            <Button variant="outline" className="w-full text-xs h-9 justify-center" asChild>
                                                <Link href={`/production-orders/${salesOrder.production_order.id}`}>
                                                    View Production Order
                                                </Link>
                                            </Button>
                                        </div>
                                    ) : (
                                        <div className="flex flex-col gap-2">
                                            <p className="text-xs text-neutral-500">No production order created yet.</p>
                                            {can('create-production-orders') && (
                                                <Button
                                                    onClick={() => {
                                                        if (confirm('Create a Production Order from this Sales Order?')) {
                                                            router.post(`/sales-orders/${salesOrder.id}/production`);
                                                        }
                                                    }}
                                                    className="w-full text-xs h-9 justify-center bg-indigo-600 hover:bg-indigo-700 text-white"
                                                >
                                                    Create Production Order
                                                </Button>
                                            )}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {/* Audit Details */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold">Audit Details</CardTitle>
                            </CardHeader>
                            <CardContent className="p-4 flex flex-col gap-3 text-xs text-neutral-600 dark:text-neutral-400">
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Created By</span>
                                    <span>{salesOrder.creator?.name ?? 'System'}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Created At</span>
                                    <span>{formatDate(salesOrder.created_at)}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Last Updated By</span>
                                    <span>{salesOrder.updater?.name ?? 'System'}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Last Updated At</span>
                                    <span>{formatDate(salesOrder.updated_at)}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

SalesOrderShow.layout = {
    breadcrumbs: [
        {
            title: 'Sales Orders',
            href: indexSalesOrdersRoute(),
        },
        {
            title: 'Details',
        },
    ],
};
