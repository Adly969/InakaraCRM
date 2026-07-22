import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { usePermission } from '@/hooks/use-permission';
import { index as indexAdjustmentsRoute, approve as approveAdjustmentRoute, reject as rejectAdjustmentRoute } from '@/routes/stock-adjustments';
import type { StockAdjustment } from '@/types';
import { ArrowLeft, CheckCircle2, XCircle, FileText, Loader2, Plus, Minus } from 'lucide-react';
import { useState } from 'react';

interface Props {
    stockAdjustment: StockAdjustment;
}

export default function StockAdjustmentShow({ stockAdjustment }: Props) {
    const { can } = usePermission();
    const [actioning, setActioning] = useState<'approving' | 'rejecting' | null>(null);
    const [approvalNote, setApprovalNote] = useState('');

    const formatCurrency = (val: number | string) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(Number(val));
    };

    const handleApprove = (e: React.FormEvent) => {
        e.preventDefault();
        if (confirm('Are you sure you want to approve this Stock Adjustment? This will post the mutations and update physical stock balances immediately. Once approved, this document is locked.')) {
            setActioning('approving');
            router.post(approveAdjustmentRoute(stockAdjustment.id).url, { approval_note: approvalNote }, {
                onFinish: () => setActioning(null),
            });
        }
    };

    const handleReject = () => {
        if (confirm('Are you sure you want to reject this Stock Adjustment? This will mark it as rejected and prevent any stock mutations.')) {
            setActioning('rejecting');
            router.post(rejectAdjustmentRoute(stockAdjustment.id).url, {}, {
                onFinish: () => setActioning(null),
            });
        }
    };

    const isDraft = stockAdjustment.status === 'draft';

    return (
        <>
            <Head title={`Stock Adjustment — ${stockAdjustment.reference_no}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-7xl mx-auto w-full">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={indexAdjustmentsRoute().url}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col gap-1">
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Stock Adjustment Detail: {stockAdjustment.reference_no}
                            </h1>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                View penyesuaian status, reason, and line items.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Details Card */}
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm col-span-2">
                        <CardHeader>
                            <CardTitle className="text-lg flex items-center gap-2">
                                <FileText className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                Adjustment Profile
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="space-y-4">
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Reference No</span>
                                    <span className="text-base font-bold text-neutral-950 dark:text-neutral-50">{stockAdjustment.reference_no}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Target Warehouse</span>
                                    <span className="text-base font-semibold text-neutral-800 dark:text-neutral-350">{stockAdjustment.warehouse?.name} ({stockAdjustment.warehouse?.code})</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Status</span>
                                    <div>
                                        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${
                                            stockAdjustment.status === 'approved' 
                                                ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' 
                                                : stockAdjustment.status === 'draft'
                                                ? 'bg-neutral-50 text-neutral-600 ring-neutral-500/20 dark:bg-neutral-800 dark:text-neutral-400'
                                                : 'bg-red-50 text-red-700 ring-red-650/20 dark:bg-red-900/30 dark:text-red-400'
                                        }`}>
                                            {stockAdjustment.status}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Adjustment Date</span>
                                    <span className="text-base font-semibold text-neutral-800 dark:text-neutral-350">{stockAdjustment.adjustment_date}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Reason / Notes</span>
                                    <span className="text-base font-medium text-neutral-700 dark:text-neutral-300">{stockAdjustment.notes}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Action Card */}
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm bg-neutral-50/20 dark:bg-neutral-900/20">
                        <CardHeader>
                            <CardTitle className="text-lg">Approval Actions</CardTitle>
                            <CardDescription>Confirm or reject stock adjustments</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {isDraft ? (
                                can('approve-inventory-adjustment') ? (
                                    <div className="space-y-4">
                                        <form onSubmit={handleApprove} className="space-y-3">
                                            <div className="space-y-2">
                                                <Label htmlFor="approval_note">Approval / Rejection Note</Label>
                                                <textarea
                                                    id="approval_note"
                                                    value={approvalNote}
                                                    onChange={(e) => setApprovalNote(e.target.value)}
                                                    placeholder="Enter details of visual verification, audit sign-off..."
                                                    className="flex w-full min-h-[80px] rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-neutral-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:ring-indigo-400"
                                                />
                                            </div>
                                            <Button type="submit" disabled={actioning !== null} className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm">
                                                {actioning === 'approving' ? (
                                                    <>
                                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                        Approving...
                                                    </>
                                                ) : (
                                                    <>
                                                        <CheckCircle2 className="mr-2 h-4 w-4" />
                                                        Approve & Post Mutations
                                                    </>
                                                )}
                                            </Button>
                                        </form>

                                        <Button 
                                            type="button" 
                                            variant="destructive"
                                            onClick={handleReject}
                                            disabled={actioning !== null}
                                            className="w-full"
                                        >
                                            {actioning === 'rejecting' ? (
                                                <>
                                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                    Rejecting...
                                                </>
                                            ) : (
                                                <>
                                                    <XCircle className="mr-2 h-4 w-4" />
                                                    Reject Adjustment
                                                </>
                                            )}
                                        </Button>
                                    </div>
                                ) : (
                                    <p className="text-sm text-neutral-500">You do not have the required permissions to approve or reject stock adjustments.</p>
                                )
                            ) : (
                                <div className="space-y-3">
                                    <div className={`p-3 rounded-lg text-sm font-semibold flex items-center gap-2 ${
                                        stockAdjustment.status === 'approved' 
                                            ? 'bg-green-50 dark:bg-green-950/20 text-green-800 dark:text-green-400'
                                            : 'bg-red-50 dark:bg-red-950/20 text-red-800 dark:text-red-400'
                                    }`}>
                                        {stockAdjustment.status === 'approved' ? (
                                            <>
                                                <CheckCircle2 className="h-5 w-5" />
                                                Approved & Locked
                                            </>
                                        ) : (
                                            <>
                                                <XCircle className="h-5 w-5" />
                                                Rejected & Closed
                                            </>
                                        )}
                                    </div>
                                    {stockAdjustment.approval_note && (
                                        <div className="text-xs text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-800 p-3 rounded bg-white dark:bg-neutral-950">
                                            <span className="font-semibold block mb-1">Approval Note:</span>
                                            {stockAdjustment.approval_note}
                                        </div>
                                    )}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Items Table */}
                <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm mt-4">
                    <CardHeader className="border-b border-neutral-100 dark:border-neutral-850 pb-4">
                        <CardTitle className="text-lg">Line Items Adjusted</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="relative w-full overflow-auto">
                            <table className="w-full caption-bottom text-sm">
                                <thead className="border-b border-neutral-200 bg-neutral-100/50 dark:border-neutral-800 dark:bg-neutral-800/50">
                                    <tr className="hover:bg-transparent">
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            SKU Code
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Product Name
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Type
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Qty Change
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Unit Cost (HPP)
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Total Cost Effect
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {(stockAdjustment.items || []).map((it) => {
                                        const isAddition = it.type === 'addition';
                                        const qtyAdjusted = Number(it.quantity_adjusted);
                                        const cost = Number(it.unit_cost);
                                        const effect = qtyAdjusted * cost;

                                        return (
                                            <tr key={it.id} className="hover:bg-neutral-100/30 dark:hover:bg-neutral-800/30 transition-colors">
                                                <td className="p-4 align-middle font-semibold text-neutral-900 dark:text-neutral-50">
                                                    {it.inventory_item?.sku}
                                                </td>
                                                <td className="p-4 align-middle font-medium text-neutral-700 dark:text-neutral-300">
                                                    {it.inventory_item?.name}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ring-1 ring-inset ${
                                                        isAddition
                                                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-450'
                                                            : 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-900/30 dark:text-rose-450'
                                                    }`}>
                                                        {it.type}
                                                    </span>
                                                </td>
                                                <td className={`p-4 align-middle text-right font-bold ${isAddition ? 'text-emerald-650' : 'text-rose-650'}`}>
                                                    <span className="inline-flex items-center gap-1 justify-end w-full">
                                                        {isAddition ? <Plus className="h-3.5 w-3.5" /> : <Minus className="h-3.5 w-3.5" />}
                                                        {qtyAdjusted.toLocaleString()}
                                                    </span>
                                                </td>
                                                <td className="p-4 align-middle text-right font-medium text-neutral-800 dark:text-neutral-300">
                                                    {formatCurrency(cost)}
                                                </td>
                                                <td className="p-4 align-middle text-right font-bold text-neutral-900 dark:text-neutral-50">
                                                    {formatCurrency(effect)}
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

StockAdjustmentShow.layout = {
    breadcrumbs: [
        {
            title: 'Stock Adjustments',
            href: indexAdjustmentsRoute().url,
        },
        {
            title: 'Adjustment Detail',
            href: '',
        },
    ],
};
