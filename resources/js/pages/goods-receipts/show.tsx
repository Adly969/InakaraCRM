import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { usePermission } from '@/hooks/use-permission';
import { index as indexReceiptsRoute, receive as receiveReceiptRoute } from '@/routes/goods-receipts';
import type { GoodsReceipt } from '@/types';
import { ArrowLeft, CheckCircle2, FileText, Loader2 } from 'lucide-react';
import { useState } from 'react';

interface Props {
    goodsReceipt: GoodsReceipt;
}

export default function GoodsReceiptShow({ goodsReceipt }: Props) {
    const { can } = usePermission();
    const [posting, setPosting] = useState(false);
    const [remark, setRemark] = useState('');

    const formatCurrency = (val: number | string) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(Number(val));
    };

    const handlePost = (e: React.FormEvent) => {
        e.preventDefault();
        if (confirm('Are you sure you want to post this Goods Receipt? Once posted, inventory stock quantities will be modified and this document becomes locked and immutable.')) {
            setPosting(true);
            router.post(receiveReceiptRoute(goodsReceipt.id).url, { remark }, {
                onFinish: () => setPosting(false),
            });
        }
    };

    const isDraft = goodsReceipt.status === 'draft';
    const totalReceiptValue = (goodsReceipt.items || []).reduce((acc, it) => {
        return acc + (Number(it.quantity_received) * Number(it.unit_cost));
    }, 0);

    return (
        <>
            <Head title={`Goods Receipt — ${goodsReceipt.reference_no}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-7xl mx-auto w-full">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={indexReceiptsRoute().url}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col gap-1">
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Goods Receipt Detail: {goodsReceipt.reference_no}
                            </h1>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                View receipt status, associated production order source, and line items.
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
                                Goods Receipt Profile
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="space-y-4">
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Reference No</span>
                                    <span className="text-base font-bold text-neutral-950 dark:text-neutral-50">{goodsReceipt.reference_no}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Destination Warehouse</span>
                                    <span className="text-base font-semibold text-neutral-800 dark:text-neutral-350">{goodsReceipt.warehouse?.name} ({goodsReceipt.warehouse?.code})</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Status</span>
                                    <div>
                                        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${
                                            goodsReceipt.status === 'received' 
                                                ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' 
                                                : goodsReceipt.status === 'draft'
                                                ? 'bg-neutral-50 text-neutral-600 ring-neutral-500/20 dark:bg-neutral-800 dark:text-neutral-400'
                                                : 'bg-red-50 text-red-700 ring-red-650/20 dark:bg-red-900/30 dark:text-red-400'
                                        }`}>
                                            {goodsReceipt.status}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Production Order Ref</span>
                                    <span className="text-base font-semibold text-neutral-800 dark:text-neutral-300">
                                        {goodsReceipt.production_order ? (
                                            <Link href={`/production-orders/${goodsReceipt.production_order_id}`} className="text-indigo-600 dark:text-indigo-400 hover:underline">
                                                {goodsReceipt.production_order.reference_no}
                                            </Link>
                                        ) : (
                                            'Manual / Ad-hoc'
                                        )}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Date Received</span>
                                    <span className="text-base font-semibold text-neutral-800 dark:text-neutral-350">{goodsReceipt.received_date}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Total Value</span>
                                    <span className="text-base font-bold text-neutral-900 dark:text-neutral-50">{formatCurrency(totalReceiptValue)}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Posting Card */}
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm bg-neutral-50/20 dark:bg-neutral-900/20">
                        <CardHeader>
                            <CardTitle className="text-lg">Document Actions</CardTitle>
                            <CardDescription>Confirm and post physical stock receipt</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {isDraft ? (
                                can('approve-goods-receipts') ? (
                                    <form onSubmit={handlePost} className="space-y-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="remark">Posting Remark / Note</Label>
                                            <textarea
                                                id="remark"
                                                value={remark}
                                                onChange={(e) => setRemark(e.target.value)}
                                                placeholder="Enter physical condition, vehicle info, or checks conducted..."
                                                className="flex w-full min-h-[80px] rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-neutral-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:ring-indigo-400"
                                            />
                                        </div>
                                        <Button type="submit" disabled={posting} className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm">
                                            {posting ? (
                                                <>
                                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                    Posting Receipt...
                                                </>
                                            ) : (
                                                <>
                                                    <CheckCircle2 className="mr-2 h-4 w-4" />
                                                    Post & Update Stock
                                                </>
                                            )}
                                        </Button>
                                    </form>
                                ) : (
                                    <p className="text-sm text-neutral-500">You do not have the required permissions to post and approve Goods Receipts.</p>
                                )
                            ) : (
                                <div className="space-y-3">
                                    <div className="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-lg text-emerald-800 dark:text-emerald-400 text-sm font-semibold flex items-center gap-2">
                                        <CheckCircle2 className="h-5 w-5" />
                                        Document Posted & Locked
                                    </div>
                                    {goodsReceipt.remark && (
                                        <div className="text-xs text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-800 p-3 rounded bg-white dark:bg-neutral-950">
                                            <span className="font-semibold block mb-1">Receipt Remark:</span>
                                            {goodsReceipt.remark}
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
                        <CardTitle className="text-lg">Line Items Received</CardTitle>
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
                                            Qty Received
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Unit Price (HPP)
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Subtotal
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {(goodsReceipt.items || []).map((it) => {
                                        const subtotal = Number(it.quantity_received) * Number(it.unit_cost);

                                        return (
                                            <tr key={it.id} className="hover:bg-neutral-100/30 dark:hover:bg-neutral-800/30 transition-colors">
                                                <td className="p-4 align-middle font-semibold text-neutral-900 dark:text-neutral-50">
                                                    {it.sku}
                                                </td>
                                                <td className="p-4 align-middle font-medium text-neutral-700 dark:text-neutral-300">
                                                    {it.description}
                                                </td>
                                                <td className="p-4 align-middle text-right font-bold text-neutral-900 dark:text-neutral-50">
                                                    {Number(it.quantity_received).toLocaleString()} {it.unit}
                                                </td>
                                                <td className="p-4 align-middle text-right font-medium text-neutral-800 dark:text-neutral-350">
                                                    {formatCurrency(it.unit_cost)}
                                                </td>
                                                <td className="p-4 align-middle text-right font-bold text-neutral-950 dark:text-neutral-50">
                                                    {formatCurrency(subtotal)}
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

GoodsReceiptShow.layout = {
    breadcrumbs: [
        {
            title: 'Goods Receipts',
            href: indexReceiptsRoute().url,
        },
        {
            title: 'Receipt Detail',
            href: '',
        },
    ],
};
