import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexProductionOrdersRoute, edit as editProductionOrderRoute } from '@/routes/production-orders';
import type { ProductionOrder } from '@/types';
import { ArrowLeft, Pencil, AlertCircle, Calendar, Clock, User, Activity } from 'lucide-react';
import { router } from '@inertiajs/react';

interface Props {
    productionOrder: ProductionOrder;
}

export default function ProductionOrderShow({ productionOrder }: Props) {
    const { can } = usePermission();

    const isDraft = productionOrder.status === 'draft';
    const isScheduled = productionOrder.status === 'scheduled';
    const isInProduction = productionOrder.status === 'in_production';
    const isQualityControl = productionOrder.status === 'quality_control';

    const handleTransition = (newStatus: string) => {
        let extra: any = {};
        if (newStatus === 'scheduled') {
            const date = prompt('Enter target completion date (YYYY-MM-DD):', productionOrder.target_completion_date ?? '');
            if (date === null) return;
            if (!date.trim()) {
                alert('Target completion date is required.');
                return;
            }
            extra.target_completion_date = date;
        }
        if (newStatus === 'cancelled') {
            const reason = prompt('Enter cancellation reason:');
            if (reason === null) return;
            if (!reason.trim()) {
                alert('Cancellation reason is required.');
                return;
            }
            extra.cancellation_reason = reason;
        }

        if (confirm(`Are you sure you want to transition this production order to ${newStatus.replace('_', ' ')}?`)) {
            router.put(`/production-orders/${productionOrder.id}`, {
                customer_id: productionOrder.customer_id,
                subject: productionOrder.subject,
                currency: productionOrder.currency,
                tax_rate: productionOrder.tax_rate,
                items: (productionOrder.items ?? []) as any,
                status: newStatus,
                _updated_at: productionOrder.updated_at,
                ...extra,
            });
        }
    };

    const formatDate = (dateString?: string | null) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('en-US', {
            dateStyle: 'medium',
        });
    };

    const formatDateTime = (dateTimeString?: string | null) => {
        if (!dateTimeString) return '-';
        return new Date(dateTimeString).toLocaleString('en-US', {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    };

    return (
        <>
            <Head title={`Production Order - ${productionOrder.reference_no ?? productionOrder.subject}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={indexProductionOrdersRoute()}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col gap-0.5">
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {productionOrder.reference_no ?? 'Reference Pending'}
                            </h1>
                            <p className="text-xs text-neutral-500">
                                {productionOrder.subject}
                            </p>
                        </div>
                    </div>
                    {can('edit-production-orders') && ['draft', 'scheduled'].includes(productionOrder.status) && (
                        <Button asChild variant="outline">
                            <Link href={editProductionOrderRoute(productionOrder.id)}>
                                <Pencil className="mr-2 h-4 w-4" />
                                Edit Production Order
                            </Link>
                        </Button>
                    )}
                </div>

                {productionOrder.status === 'cancelled' && (
                    <div className="flex items-start gap-3 p-4 rounded-lg bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50">
                        <AlertCircle className="h-5 w-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                        <div className="flex flex-col gap-1">
                            <span className="text-sm font-semibold text-red-800 dark:text-red-200">
                                Production Order Cancelled
                            </span>
                            <span className="text-xs text-red-700 dark:text-red-300">
                                <span className="font-semibold">Reason:</span> {productionOrder.cancellation_reason ?? 'No reason provided.'}
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
                                        {productionOrder.customer ? productionOrder.customer.name : '-'}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Subject</span>
                                    <span className="text-sm font-medium">{productionOrder.subject}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Sales Order Link</span>
                                    <span className="text-sm font-medium">
                                        {productionOrder.sales_order ? (
                                            <Link
                                                href={`/sales-orders/${productionOrder.sales_order_id}`}
                                                className="text-neutral-900 dark:text-neutral-50 underline font-semibold"
                                            >
                                                {productionOrder.sales_order.reference_no ?? 'View Source Sales Order'}
                                            </Link>
                                        ) : '-'}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Priority</span>
                                    <span className="text-sm font-semibold capitalize">{productionOrder.priority}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Target Completion Date</span>
                                    <span className="text-sm font-medium">{formatDate(productionOrder.target_completion_date)}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Assigned Craftsman</span>
                                    <span className="text-sm font-medium">
                                        {productionOrder.assigned_to && typeof productionOrder.assigned_to === 'object'
                                            ? (productionOrder.assigned_to as any).name
                                            : productionOrder.assigned_to_user?.name ?? '-'}
                                    </span>
                                </div>
                                {productionOrder.production_notes && (
                                    <div className="sm:col-span-2 flex flex-col gap-1">
                                        <span className="text-xs text-neutral-500">Production Notes</span>
                                        <span className="text-sm text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-950 p-3 rounded border border-neutral-200 dark:border-neutral-800">
                                            {productionOrder.production_notes}
                                        </span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Production Metrics Card */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold">Production Metrics</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div className="flex items-center gap-3">
                                    <Calendar className="h-5 w-5 text-neutral-400" />
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-xs text-neutral-500">Started At</span>
                                        <span className="text-sm font-medium">{formatDateTime(productionOrder.started_at)}</span>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <Calendar className="h-5 w-5 text-neutral-400" />
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-xs text-neutral-500">Completed At</span>
                                        <span className="text-sm font-medium">{formatDateTime(productionOrder.completed_at)}</span>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <Clock className="h-5 w-5 text-neutral-400" />
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-xs text-neutral-500">Estimated Hours</span>
                                        <span className="text-sm font-medium">
                                            {productionOrder.estimated_hours ? `${productionOrder.estimated_hours} hrs` : '-'}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <Clock className="h-5 w-5 text-neutral-400" />
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-xs text-neutral-500">Actual Hours</span>
                                        <span className="text-sm font-medium">
                                            {productionOrder.actual_hours ? `${productionOrder.actual_hours} hrs` : '-'}
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Line Items Table */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50 overflow-hidden">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold">Production Line Items</CardTitle>
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
                                        {productionOrder.items?.map((item) => (
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
                                        <span className="font-mono">{productionOrder.currency} {Number(productionOrder.subtotal).toLocaleString()}</span>
                                    </div>
                                    <div className="flex justify-between w-64 text-sm text-neutral-600 dark:text-neutral-400">
                                        <span>Tax ({Number(productionOrder.tax_rate)}%):</span>
                                        <span className="font-mono">{productionOrder.currency} {Number(productionOrder.tax_amount).toLocaleString()}</span>
                                    </div>
                                    <div className="flex justify-between w-64 text-base font-bold text-neutral-900 dark:text-neutral-50 pt-2 border-t border-neutral-200 dark:border-neutral-800">
                                        <span>Total:</span>
                                        <span className="font-mono">{productionOrder.currency} {Number(productionOrder.total_amount).toLocaleString()}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Audit Log Timeline Card */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold flex items-center gap-2">
                                    <Activity className="h-4 w-4 text-neutral-400" />
                                    Production Audit Logs
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                {productionOrder.logs && productionOrder.logs.length === 0 ? (
                                    <p className="text-sm text-neutral-500">No status logs recorded.</p>
                                ) : (
                                    <div className="relative border-l border-neutral-200 dark:border-neutral-800 ml-3 pl-6 space-y-6">
                                        {productionOrder.logs?.map((log) => (
                                            <div key={log.id} className="relative">
                                                <span className="absolute left-[-31px] top-0 flex h-4 w-4 items-center justify-center rounded-full bg-neutral-200 dark:bg-neutral-800 ring-4 ring-white dark:ring-neutral-900">
                                                    <span className="h-1.5 w-1.5 rounded-full bg-neutral-600 dark:bg-neutral-400" />
                                                </span>
                                                <div className="flex flex-col gap-1">
                                                    <div className="flex flex-wrap items-center gap-2 text-xs">
                                                        <span className="font-semibold text-neutral-950 dark:text-neutral-50">
                                                            {log.creator?.name ?? 'System'}
                                                        </span>
                                                        <span className="text-neutral-500">
                                                            {formatDateTime(log.created_at)}
                                                        </span>
                                                    </div>
                                                    <p className="text-sm text-neutral-700 dark:text-neutral-300">
                                                        Transitioned status from <span className="font-semibold capitalize">{log.status_from ? log.status_from.replace('_', ' ') : 'None'}</span> to <span className="font-semibold capitalize text-neutral-950 dark:text-neutral-50">{log.status_to.replace('_', ' ')}</span>
                                                    </p>
                                                    {log.note && (
                                                        <p className="text-xs text-neutral-500 italic mt-0.5 bg-neutral-100 dark:bg-neutral-800/40 p-2 rounded">
                                                            "{log.note}"
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
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
                                    <span className="text-sm font-semibold capitalize text-neutral-900 dark:text-neutral-50">
                                        {productionOrder.status.replace('_', ' ')}
                                    </span>
                                </div>

                                {isDraft && (
                                    <>
                                        <Button
                                            onClick={() => handleTransition('scheduled')}
                                            className="w-full text-xs h-9 justify-center bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-700 dark:hover:bg-blue-800"
                                        >
                                            Schedule Production
                                        </Button>
                                        <Button
                                            variant="outline"
                                            onClick={() => handleTransition('cancelled')}
                                            className="w-full text-xs h-9 justify-center border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20"
                                        >
                                            Cancel Order
                                        </Button>
                                    </>
                                )}

                                {isScheduled && (
                                    <>
                                        <Button
                                            onClick={() => handleTransition('in_production')}
                                            className="w-full text-xs h-9 justify-center bg-amber-600 hover:bg-amber-700 text-white"
                                        >
                                            Start Production
                                        </Button>
                                        <Button
                                            variant="outline"
                                            onClick={() => handleTransition('cancelled')}
                                            className="w-full text-xs h-9 justify-center border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20"
                                        >
                                            Cancel Order
                                        </Button>
                                    </>
                                )}

                                {isInProduction && (
                                    <>
                                        <Button
                                            onClick={() => handleTransition('quality_control')}
                                            className="w-full text-xs h-9 justify-center bg-indigo-600 hover:bg-indigo-700 text-white"
                                        >
                                            Submit to QC
                                        </Button>
                                        <Button
                                            variant="outline"
                                            onClick={() => handleTransition('cancelled')}
                                            className="w-full text-xs h-9 justify-center border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20"
                                        >
                                            Cancel Order
                                        </Button>
                                    </>
                                )}

                                {isQualityControl && (
                                    <>
                                        <Button
                                            onClick={() => handleTransition('completed')}
                                            className="w-full text-xs h-9 justify-center bg-green-600 hover:bg-green-700 text-white dark:bg-green-700 dark:hover:bg-green-800"
                                        >
                                            QC Passed & Complete
                                        </Button>
                                        <Button
                                            onClick={() => handleTransition('in_production')}
                                            className="w-full text-xs h-9 justify-center bg-orange-600 hover:bg-orange-700 text-white"
                                        >
                                            QC Failed (Rework)
                                        </Button>
                                        <Button
                                            variant="outline"
                                            onClick={() => handleTransition('cancelled')}
                                            className="w-full text-xs h-9 justify-center border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20"
                                        >
                                            Cancel Order
                                        </Button>
                                    </>
                                )}

                                {['completed', 'cancelled'].includes(productionOrder.status) && (
                                    <p className="text-xs text-neutral-500 text-center py-2 bg-white dark:bg-neutral-950/30 rounded border border-dashed border-neutral-200 dark:border-neutral-800">
                                        This production order is {productionOrder.status}.
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        {/* Audit Details */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold">Audit Details</CardTitle>
                            </CardHeader>
                            <CardContent className="p-4 flex flex-col gap-3 text-xs text-neutral-600 dark:text-neutral-400">
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Created By</span>
                                    <span>{productionOrder.creator?.name ?? 'System'}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Created At</span>
                                    <span>{formatDateTime(productionOrder.created_at)}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Last Updated By</span>
                                    <span>{productionOrder.updater?.name ?? 'System'}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Last Updated At</span>
                                    <span>{formatDateTime(productionOrder.updated_at)}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

ProductionOrderShow.layout = {
    breadcrumbs: [
        {
            title: 'Production Orders',
            href: indexProductionOrdersRoute(),
        },
        {
            title: 'Details',
        },
    ],
};
