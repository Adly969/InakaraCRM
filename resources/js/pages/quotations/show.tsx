import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexQuotationsRoute, edit as editQuotationRoute, update as updateQuotationRoute, convert as convertQuotationRoute } from '@/routes/quotations';
import type { Quotation } from '@/types';
import { ArrowLeft, Pencil } from 'lucide-react';
import { router } from '@inertiajs/react';

interface Props {
    quotation: Quotation;
}

export default function QuotationShow({ quotation }: Props) {
    const { can } = usePermission();

    const isDraft = quotation.status === 'draft';
    const isSent = quotation.status === 'sent';
    const isAccepted = quotation.status === 'accepted';

    const handleStatusChange = (newStatus: string) => {
        if (confirm(`Are you sure you want to transition this quotation to ${newStatus}?`)) {
            router.put(updateQuotationRoute(quotation.id).url, {
                // Pass lock-required attributes to pass request validation
                customer_id: quotation.customer_id,
                lead_id: quotation.lead_id,
                subject: quotation.subject,
                valid_until: quotation.valid_until,
                currency: quotation.currency,
                items: (quotation.items ?? []) as any,
                status: newStatus,
            });
        }
    };

    const handleConvertToSalesOrder = () => {
        if (confirm('Are you sure you want to convert this quotation into a Sales Order?')) {
            router.post(convertQuotationRoute(quotation.id).url);
        }
    };

    const formatDate = (dateString?: string) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('en-US', {
            dateStyle: 'medium',
        });
    };

    return (
        <>
            <Head title={`Quotation - ${quotation.reference_no ?? quotation.subject}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={indexQuotationsRoute()}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col gap-0.5">
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {quotation.reference_no ?? 'Reference Pending'}
                            </h1>
                            <p className="text-xs text-neutral-500">
                                {quotation.subject}
                            </p>
                        </div>
                    </div>
                    {can('edit-quotations') && isDraft && (
                        <Button asChild variant="outline">
                            <Link href={editQuotationRoute(quotation.id)}>
                                <Pencil className="mr-2 h-4 w-4" />
                                Edit Quotation
                            </Link>
                        </Button>
                    )}
                </div>

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
                                    <span className="text-xs text-neutral-500">Prospect / Customer</span>
                                    <span className="text-sm font-medium">
                                        {quotation.customer ? (
                                            <span>{quotation.customer.name} (Customer)</span>
                                        ) : quotation.lead ? (
                                            <span>{quotation.lead.name} (Lead)</span>
                                        ) : '-'}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Subject</span>
                                    <span className="text-sm font-medium">{quotation.subject}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Revision</span>
                                    <span className="text-sm font-medium">v{quotation.revision}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Valid Until</span>
                                    <span className="text-sm font-medium">{formatDate(quotation.valid_until)}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Currency</span>
                                    <span className="text-sm font-medium">{quotation.currency}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Assigned To</span>
                                    <span className="text-sm font-medium">
                                        {quotation.assigned_to_user?.name ?? '-'}
                                    </span>
                                </div>
                                {quotation.notes && (
                                    <div className="sm:col-span-2 flex flex-col gap-1">
                                        <span className="text-xs text-neutral-500">Notes</span>
                                        <span className="text-sm text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-950 p-3 rounded border border-neutral-200 dark:border-neutral-800">
                                            {quotation.notes}
                                        </span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Line Items Table */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50 overflow-hidden">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold">Quotation Line Items</CardTitle>
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
                                        {quotation.items?.map((item) => (
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
                                        <span className="font-mono">{quotation.currency} {Number(quotation.subtotal).toLocaleString()}</span>
                                    </div>
                                    <div className="flex justify-between w-64 text-sm text-neutral-600 dark:text-neutral-400">
                                        <span>Tax ({Number(quotation.tax_rate)}%):</span>
                                        <span className="font-mono">{quotation.currency} {Number(quotation.tax_amount).toLocaleString()}</span>
                                    </div>
                                    <div className="flex justify-between w-64 text-base font-bold text-neutral-900 dark:text-neutral-50 pt-2 border-t border-neutral-200 dark:border-neutral-800">
                                        <span>Total:</span>
                                        <span className="font-mono">{quotation.currency} {Number(quotation.total_amount).toLocaleString()}</span>
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
                                    <span className="text-sm font-semibold capitalize text-neutral-900 dark:text-neutral-50">{quotation.status}</span>
                                </div>

                                {isDraft && (
                                    <Button
                                        onClick={() => handleStatusChange('sent')}
                                        className="w-full text-xs h-9 justify-center"
                                    >
                                        Mark as Sent
                                    </Button>
                                )}

                                {(isDraft || isSent) && (
                                    <>
                                        <Button
                                            variant="secondary"
                                            onClick={() => handleStatusChange('accepted')}
                                            className="w-full text-xs h-9 justify-center bg-green-600 hover:bg-green-700 text-white dark:bg-green-700 dark:hover:bg-green-800"
                                        >
                                            Accept Quotation
                                        </Button>
                                        <Button
                                            variant="outline"
                                            onClick={() => handleStatusChange('rejected')}
                                            className="w-full text-xs h-9 justify-center border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20"
                                        >
                                            Reject Quotation
                                        </Button>
                                    </>
                                )}

                                {isAccepted && (
                                    <Button
                                        onClick={handleConvertToSalesOrder}
                                        className="w-full text-xs h-9 justify-center bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-700 dark:hover:bg-blue-800"
                                    >
                                        Convert to Sales Order
                                    </Button>
                                )}

                                {!isDraft && !isSent && !isAccepted && (
                                    <p className="text-xs text-neutral-500 text-center py-2 bg-white dark:bg-neutral-950/30 rounded border border-dashed border-neutral-200 dark:border-neutral-800">
                                        This quotation is closed in a terminal status.
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
                                    <span>{quotation.creator?.name ?? 'System'}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Created At</span>
                                    <span>{formatDate(quotation.created_at)}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Last Updated By</span>
                                    <span>{quotation.updater?.name ?? 'System'}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Last Updated At</span>
                                    <span>{formatDate(quotation.updated_at)}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

QuotationShow.layout = {
    breadcrumbs: [
        {
            title: 'Quotations',
            href: indexQuotationsRoute(),
        },
        {
            title: 'Details',
        },
    ],
};
