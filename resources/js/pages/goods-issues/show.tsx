import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { usePermission } from '@/hooks/use-permission';
import { index as indexIssuesRoute, issue as issueConfirmRoute } from '@/routes/goods-issues';
import type { GoodsIssue } from '@/types';
import { ArrowLeft, CheckCircle2, FileText, Loader2 } from 'lucide-react';
import { useState } from 'react';

interface Props {
    goodsIssue: GoodsIssue;
}

export default function GoodsIssueShow({ goodsIssue }: Props) {
    const { can } = usePermission();
    const [confirming, setConfirming] = useState(false);
    const [remark, setRemark] = useState('');

    const handleConfirm = (e: React.FormEvent) => {
        e.preventDefault();
        if (confirm('Are you sure you want to post this Goods Issue? This will reduce the physical stock and release corresponding sales reservations. Once confirmed, this document is locked.')) {
            setConfirming(true);
            router.post(issueConfirmRoute(goodsIssue.id).url, { remark }, {
                onFinish: () => setConfirming(false),
            });
        }
    };

    const isDraft = goodsIssue.status === 'draft';

    return (
        <>
            <Head title={`Goods Issue — ${goodsIssue.reference_no}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-7xl mx-auto w-full">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={indexIssuesRoute().url}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col gap-1">
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Goods Issue Detail: {goodsIssue.reference_no}
                            </h1>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                View issue status, associated sales order source, and delivery items.
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
                                Goods Issue Profile
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="space-y-4">
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Reference No</span>
                                    <span className="text-base font-bold text-neutral-950 dark:text-neutral-50">{goodsIssue.reference_no}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Source Warehouse</span>
                                    <span className="text-base font-semibold text-neutral-800 dark:text-neutral-350">{goodsIssue.warehouse?.name} ({goodsIssue.warehouse?.code})</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Status</span>
                                    <div>
                                        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${
                                            goodsIssue.status === 'issued' 
                                                ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' 
                                                : goodsIssue.status === 'draft'
                                                ? 'bg-neutral-50 text-neutral-600 ring-neutral-500/20 dark:bg-neutral-800 dark:text-neutral-400'
                                                : 'bg-red-50 text-red-700 ring-red-650/20 dark:bg-red-900/30 dark:text-red-400'
                                        }`}>
                                            {goodsIssue.status}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Sales Order Ref</span>
                                    <span className="text-base font-semibold text-neutral-800 dark:text-neutral-350">
                                        {goodsIssue.sales_order ? (
                                            <Link href={`/sales-orders/${goodsIssue.sales_order_id}`} className="text-indigo-600 dark:text-indigo-400 hover:underline">
                                                {goodsIssue.sales_order.reference_no}
                                            </Link>
                                        ) : (
                                            'Manual / Ad-hoc'
                                        )}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Date Issued</span>
                                    <span className="text-base font-semibold text-neutral-800 dark:text-neutral-350">{goodsIssue.issued_date}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Notes</span>
                                    <span className="text-base font-medium text-neutral-700 dark:text-neutral-300">{goodsIssue.notes || '-'}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Action Card */}
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm bg-neutral-50/20 dark:bg-neutral-900/20">
                        <CardHeader>
                            <CardTitle className="text-lg">Document Actions</CardTitle>
                            <CardDescription>Confirm dispatch and post stock deduction</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {isDraft ? (
                                can('approve-goods-issues') ? (
                                    <form onSubmit={handleConfirm} className="space-y-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="remark">Dispatch Remark / Note</Label>
                                            <textarea
                                                id="remark"
                                                value={remark}
                                                onChange={(e) => setRemark(e.target.value)}
                                                placeholder="Enter courier info, truck plate, shipping seals..."
                                                className="flex w-full min-h-[80px] rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-neutral-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:ring-indigo-400"
                                            />
                                        </div>
                                        <Button type="submit" disabled={confirming} className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm">
                                            {confirming ? (
                                                <>
                                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                    Posting Issue...
                                                </>
                                            ) : (
                                                <>
                                                    <CheckCircle2 className="mr-2 h-4 w-4" />
                                                    Confirm Issue & Dispatch
                                                </>
                                            )}
                                        </Button>
                                    </form>
                                ) : (
                                    <p className="text-sm text-neutral-500">You do not have the required permissions to post and approve Goods Issues.</p>
                                )
                            ) : (
                                <div className="space-y-3">
                                    <div className="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-lg text-emerald-800 dark:text-emerald-400 text-sm font-semibold flex items-center gap-2">
                                        <CheckCircle2 className="h-5 w-5" />
                                        Document Posted & Locked
                                    </div>
                                    {goodsIssue.remark && (
                                        <div className="text-xs text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-800 p-3 rounded bg-white dark:bg-neutral-950">
                                            <span className="font-semibold block mb-1">Dispatch Remark:</span>
                                            {goodsIssue.remark}
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
                        <CardTitle className="text-lg">Line Items Issued</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="relative w-full overflow-auto">
                            <table className="w-full caption-bottom text-sm">
                                <thead className="border-b border-neutral-200 bg-neutral-100/50 dark:border-neutral-800 dark:bg-neutral-800/50">
                                    <tr className="hover:bg-transparent">
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            SKU
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Description
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Qty Dispatched
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350 pl-6">
                                            Unit
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {(goodsIssue.items || []).map((it) => (
                                        <tr key={it.id} className="hover:bg-neutral-100/30 dark:hover:bg-neutral-800/30 transition-colors">
                                            <td className="p-4 align-middle font-semibold text-neutral-900 dark:text-neutral-50">
                                                {it.sku}
                                            </td>
                                            <td className="p-4 align-middle font-medium text-neutral-700 dark:text-neutral-300">
                                                {it.description}
                                            </td>
                                            <td className="p-4 align-middle text-right font-bold text-neutral-900 dark:text-neutral-50">
                                                {Number(it.quantity_issued).toLocaleString()}
                                            </td>
                                            <td className="p-4 align-middle text-left text-neutral-600 dark:text-neutral-450 pl-6">
                                                {it.unit}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

GoodsIssueShow.layout = {
    breadcrumbs: [
        {
            title: 'Goods Issues',
            href: indexIssuesRoute().url,
        },
        {
            title: 'Issue Detail',
            href: '',
        },
    ],
};
