import { Head, Link, router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import type { Invoice } from '@/types';
import { ArrowLeft, Check, X, FileText, Calendar, ShieldAlert } from 'lucide-react';
import React from 'react';

interface Props {
    invoice: Invoice;
}

export default function ShowInvoice({ invoice }: Props) {
    const { can } = usePermission();

    const handleApprove = () => {
        if (confirm('Approve this Invoice? This will freeze calculations.')) {
            router.post(`/invoices/${invoice.id}/approve`);
        }
    };

    const handleIssue = () => {
        if (confirm('Issue this Invoice? This will generate a reference code.')) {
            router.post(`/invoices/${invoice.id}/issue`);
        }
    };

    const handleVoid = () => {
        const reason = prompt('Reason for voiding:');
        if (reason !== null && reason.trim() !== '') {
            router.post(`/invoices/${invoice.id}/void`, { reason });
        } else if (reason !== null) {
            alert('Void reason is required.');
        }
    };

    const getStatusBadgeClass = (status: string) => {
        switch (status) {
            case 'draft':
                return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
            case 'approved':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
            case 'issued':
                return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400';
            case 'overdue':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            case 'void':
                return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
            default:
                return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
        }
    };

    const getStatusLabel = (status: string) => {
        return status.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
    };

    const formatCurrency = (val: number | string) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(val));
    };

    return (
        <>
            <Head title={`Invoice Details: ${invoice.reference_no || 'Draft'}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button asChild variant="outline" size="icon" className="h-8 w-8">
                            <Link href="/invoices">
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col">
                            <h1 className="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {invoice.reference_no || 'Invoice Draft'}
                            </h1>
                            <p className="text-xs text-neutral-500">
                                Customer: {invoice.customer?.name}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <span className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${getStatusBadgeClass(invoice.status)}`}>
                            {getStatusLabel(invoice.status)}
                        </span>
                    </div>
                </div>

                {/* Workflow Transitions Panel */}
                <Card className="border-indigo-100 dark:border-indigo-950 bg-indigo-50/50 dark:bg-indigo-950/10 shadow-none">
                    <CardContent className="flex flex-wrap items-center justify-between gap-4 p-4">
                        <div className="flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                            <FileText className="h-5 w-5 text-indigo-500" />
                            {invoice.status === 'draft' && <span>Invoice calculations are pending verification.</span>}
                            {invoice.status === 'approved' && <span>Invoice calculations are verified. Reference number can be issued.</span>}
                            {invoice.status === 'issued' && <span>Invoice is officially issued and sent. Void can be triggered if cancelled.</span>}
                            {invoice.status === 'overdue' && <span className="text-red-500 font-medium">Invoice payment is overdue. void can be triggered.</span>}
                            {invoice.status === 'void' && <span>This invoice has been voided.</span>}
                        </div>

                        <div className="flex gap-2">
                            {invoice.status === 'draft' && can('approve-invoices') && (
                                <Button onClick={handleApprove} className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold">
                                    <Check className="mr-1.5 h-4 w-4" />
                                    Approve Invoice
                                </Button>
                            )}
                            {invoice.status === 'approved' && can('issue-invoices') && (
                                <Button onClick={handleIssue} className="bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                                    <Check className="mr-1.5 h-4 w-4" />
                                    Issue Invoice Reference
                                </Button>
                            )}
                            {['issued', 'overdue'].includes(invoice.status) && can('void-invoices') && (
                                <Button onClick={handleVoid} variant="outline" className="text-red-600 border-red-200 hover:bg-red-50 font-bold">
                                    <X className="mr-1.5 h-4 w-4" />
                                    Void Invoice
                                </Button>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {invoice.status === 'void' && invoice.void_reason && (
                    <Card className="border-red-150 bg-red-50/50 dark:bg-red-950/10 shadow-none">
                        <CardContent className="flex gap-3 p-4 items-start">
                            <ShieldAlert className="h-5 w-5 text-red-500 shrink-0 mt-0.5" />
                            <div className="flex flex-col text-sm text-red-700 dark:text-red-400">
                                <span className="font-bold">Void Reason:</span>
                                <span>{invoice.void_reason}</span>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm md:col-span-2">
                        <CardHeader>
                            <CardTitle className="text-base font-semibold">Invoice Details</CardTitle>
                        </CardHeader>
                        <CardContent className="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                            <div>
                                <div className="text-xs font-semibold text-neutral-400">Invoice Date</div>
                                <div className="font-medium text-neutral-900 dark:text-neutral-100 flex items-center gap-1.5 mt-0.5">
                                    <Calendar className="h-4 w-4 text-neutral-400" />
                                    {invoice.invoice_date}
                                </div>
                            </div>
                            <div>
                                <div className="text-xs font-semibold text-neutral-400">Due Date</div>
                                <div className="font-medium text-neutral-900 dark:text-neutral-100 flex items-center gap-1.5 mt-0.5">
                                    <Calendar className="h-4 w-4 text-neutral-400" />
                                    {invoice.due_date}
                                </div>
                            </div>
                            <div>
                                <div className="text-xs font-semibold text-neutral-400">Payment Term</div>
                                <div className="font-medium text-neutral-900 dark:text-neutral-100 mt-0.5">
                                    {invoice.payment_term_code}
                                </div>
                            </div>
                            <div>
                                <div className="text-xs font-semibold text-neutral-400">Reference Source</div>
                                <div className="font-medium text-neutral-900 dark:text-neutral-100 mt-0.5">
                                    {invoice.delivery_order && (
                                        <Link href={`/delivery-orders/${invoice.delivery_order_id}`} className="text-indigo-600 hover:underline">
                                            DO: {invoice.delivery_order.reference_no}
                                        </Link>
                                    )}
                                    {!invoice.delivery_order && invoice.sales_order && (
                                        <Link href={`/sales-orders/${invoice.sales_order_id}`} className="text-indigo-600 hover:underline">
                                            SO: {invoice.sales_order.reference_no}
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base font-semibold">Address Snapshots</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4 text-xs">
                            <div>
                                <div className="font-semibold text-neutral-400">Billing Address:</div>
                                <div className="mt-1 font-medium">{invoice.billing_address_snapshot.address}</div>
                            </div>
                            <div>
                                <div className="font-semibold text-neutral-400">Shipping Address:</div>
                                <div className="mt-1 font-medium">{invoice.shipping_address_snapshot.address}</div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm overflow-hidden">
                    <CardHeader>
                        <CardTitle className="text-base font-semibold">Billed Items</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm text-left">
                                <thead className="bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                    <tr>
                                        <th className="px-6 py-3">SKU</th>
                                        <th className="px-6 py-3">Description</th>
                                        <th className="px-6 py-3 text-right">Qty</th>
                                        <th className="px-6 py-3 text-right">Unit Price</th>
                                        <th className="px-6 py-3 text-right">Discount</th>
                                        <th className="px-6 py-3 text-right">VAT 11%</th>
                                        <th className="px-6 py-3 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {invoice.items?.map((item) => (
                                        <tr key={item.id}>
                                            <td className="px-6 py-4 font-semibold">{item.sku}</td>
                                            <td className="px-6 py-4">{item.description}</td>
                                            <td className="px-6 py-4 text-right">{item.quantity} {item.unit}</td>
                                            <td className="px-6 py-4 text-right">{formatCurrency(item.unit_price)}</td>
                                            <td className="px-6 py-4 text-right text-red-500">
                                                {Number(item.discount_amount) > 0 && `-${formatCurrency(item.discount_amount)} (${item.discount_percentage}%)`}
                                                {Number(item.discount_amount) === 0 && '-'}
                                            </td>
                                            <td className="px-6 py-4 text-right">{formatCurrency(item.tax_amount)}</td>
                                            <td className="px-6 py-4 text-right font-semibold">{formatCurrency(item.total_amount)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {invoice.adjustments && invoice.adjustments.length > 0 && (
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base font-semibold">Billing Adjustments</CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            <table className="w-full text-sm text-left">
                                <thead className="bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                    <tr>
                                        <th className="px-6 py-3">Adjustment Type</th>
                                        <th className="px-6 py-3">Description</th>
                                        <th className="px-6 py-3 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {invoice.adjustments.map((adj) => (
                                        <tr key={adj.id}>
                                            <td className="px-6 py-4 font-semibold uppercase text-xs text-neutral-500">{adj.type.replace(/_/g, ' ')}</td>
                                            <td className="px-6 py-4">{adj.description}</td>
                                            <td className="px-6 py-4 text-right font-semibold">{formatCurrency(adj.amount)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>
                )}

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {/* Events timeline log */}
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm md:col-span-2">
                        <CardHeader>
                            <CardTitle className="text-base font-semibold">Billing Event logs</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            {invoice.events?.map((ev) => (
                                <div key={ev.id} className="flex gap-4 text-sm border-l-2 border-indigo-200 dark:border-indigo-800 pl-4 py-1">
                                    <div className="min-w-[120px] text-neutral-400 text-xs">{ev.created_at}</div>
                                    <div className="flex-1">
                                        <span className="font-semibold uppercase text-2xs tracking-wider mr-2 bg-indigo-50 dark:bg-indigo-900/25 px-1.5 py-0.5 rounded text-indigo-700 dark:text-indigo-400">{ev.event_type}</span>
                                        <span className="text-neutral-600 dark:text-neutral-450">{ev.creator?.name && `By ${ev.creator.name}`}</span>
                                        {ev.ip_address && <span className="text-2xs text-neutral-400 ml-2">({ev.ip_address})</span>}
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base font-semibold">Financial Summary</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3 text-sm font-medium text-neutral-600 dark:text-neutral-400">
                            <div className="flex justify-between">
                                <span>Subtotal:</span>
                                <span>{formatCurrency(invoice.subtotal)}</span>
                            </div>
                            <div className="flex justify-between text-red-500">
                                <span>Line Discounts:</span>
                                <span>-{formatCurrency(invoice.discount_amount)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span>VAT 11%:</span>
                                <span>{formatCurrency(invoice.tax_amount)}</span>
                            </div>
                            {Number(invoice.adjustment_amount) !== 0 && (
                                <div className="flex justify-between text-indigo-500">
                                    <span>Adjustments:</span>
                                    <span>{formatCurrency(invoice.adjustment_amount)}</span>
                                </div>
                            )}
                            <div className="flex justify-between text-base font-bold text-neutral-900 dark:text-neutral-50 border-t border-neutral-200 dark:border-neutral-800 pt-2">
                                <span>Total Amount:</span>
                                <span>{formatCurrency(invoice.total_amount)}</span>
                            </div>
                            <div className="flex justify-between text-base font-bold text-indigo-600 dark:text-indigo-400 border-t border-neutral-200 dark:border-neutral-800 pt-2">
                                <span>Outstanding Balance:</span>
                                <span>{formatCurrency(invoice.outstanding_balance)}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
