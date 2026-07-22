import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexInventoryRoute, rebuild as rebuildInventoryRoute } from '@/routes/inventory';
import type { InventoryItem, InventoryTransaction } from '@/types';
import { ArrowLeft, RefreshCw, Layers, ArrowUpRight, ArrowDownLeft, ShieldAlert } from 'lucide-react';
import { useState } from 'react';

interface Props {
    item: InventoryItem;
    transactions: {
        data: InventoryTransaction[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        total: number;
    };
}

export default function InventoryShow({ item, transactions }: Props) {
    const { can } = usePermission();
    const [rebuilding, setRebuilding] = useState(false);

    const formatCurrency = (val: number | string) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(Number(val));
    };

    const handleRebuild = () => {
        if (confirm('Are you sure you want to rebuild this item\'s stock projections from the ledger? This will recalculate the current and reserved cache based on transaction history.')) {
            setRebuilding(true);
            router.post(rebuildInventoryRoute(item.id).url, {}, {
                onFinish: () => setRebuilding(false),
            });
        }
    };

    const available = Number(item.quantity_current) - Number(item.quantity_reserved);

    return (
        <>
            <Head title={`Inventory — ${item.sku}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-7xl mx-auto w-full">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={indexInventoryRoute().url}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col gap-1">
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Stock Ledger: {item.sku}
                            </h1>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Audit history, costing details, and immutable ledger transactions.
                            </p>
                        </div>
                    </div>

                    {can('approve-inventory-adjustment') && (
                        <Button 
                            onClick={handleRebuild} 
                            disabled={rebuilding}
                            variant="outline" 
                            className="border-neutral-250 dark:border-neutral-850 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            <RefreshCw className={`mr-2 h-4 w-4 ${rebuilding ? 'animate-spin' : ''}`} />
                            Rebuild Projections
                        </Button>
                    )}
                </div>

                {/* Profile Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm col-span-2">
                        <CardHeader>
                            <CardTitle className="text-lg flex items-center gap-2">
                                <Layers className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                Product Specifications
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex justify-between items-center text-sm border-b border-neutral-100 dark:border-neutral-850 pb-2">
                                <span className="text-neutral-500 dark:text-neutral-400 font-medium">SKU Code</span>
                                <span className="font-bold text-neutral-900 dark:text-neutral-50">{item.sku}</span>
                            </div>
                            <div className="flex justify-between items-center text-sm border-b border-neutral-100 dark:border-neutral-850 pb-2">
                                <span className="text-neutral-500 dark:text-neutral-400 font-medium">Name</span>
                                <span className="font-semibold text-neutral-900 dark:text-neutral-50">{item.name}</span>
                            </div>
                            <div className="flex justify-between items-center text-sm border-b border-neutral-100 dark:border-neutral-850 pb-2">
                                <span className="text-neutral-500 dark:text-neutral-400 font-medium">Warehouse Facility</span>
                                <span className="font-semibold text-indigo-600 dark:text-indigo-400">{item.warehouse?.name}</span>
                            </div>
                            <div className="flex justify-between items-center text-sm">
                                <span className="text-neutral-500 dark:text-neutral-400 font-medium">Measurement Unit</span>
                                <span className="font-medium text-neutral-800 dark:text-neutral-300">{item.unit}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm bg-neutral-50/30 dark:bg-neutral-900/30">
                        <CardHeader>
                            <CardTitle className="text-lg">Valuation & Cost</CardTitle>
                            <CardDescription>Average unit cost moving calculations</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <div className="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                Moving Average Price
                            </div>
                            <div className="text-2xl font-bold text-indigo-950 dark:text-indigo-50">
                                {formatCurrency(item.avg_cost_price)}
                            </div>
                            <div className="text-xs text-neutral-500 mt-2">
                                Valued Stock: <span className="font-semibold text-neutral-800 dark:text-neutral-200">{formatCurrency(Number(item.avg_cost_price) * Number(item.quantity_current))}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-lg">Projected Balance</CardTitle>
                            <CardDescription>Available to sell allocations</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <div className="flex justify-between items-center text-sm border-b border-neutral-100 dark:border-neutral-850 pb-2">
                                <span className="text-neutral-500 font-medium">Physical In-Stock</span>
                                <span className="font-bold text-neutral-950 dark:text-neutral-50">{Number(item.quantity_current).toLocaleString()}</span>
                            </div>
                            <div className="flex justify-between items-center text-sm border-b border-neutral-100 dark:border-neutral-850 pb-2">
                                <span className="text-neutral-500 font-medium">Reserved Commitments</span>
                                <span className="font-bold text-amber-600 dark:text-amber-450">{Number(item.quantity_reserved).toLocaleString()}</span>
                            </div>
                            <div className="flex justify-between items-center text-sm">
                                <span className="text-neutral-500 font-semibold">Available Quantity</span>
                                <span className="font-extrabold text-emerald-600 dark:text-emerald-450">{available.toLocaleString()}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Ledger Audit Trail */}
                <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                    <CardHeader className="border-b border-neutral-100 dark:border-neutral-850 pb-4">
                        <CardTitle className="text-lg">Ledger Transactions</CardTitle>
                        <CardDescription>Immutable record of all historical stock changes</CardDescription>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="relative w-full overflow-auto">
                            <table className="w-full caption-bottom text-sm">
                                <thead className="border-b border-neutral-200 bg-neutral-100/50 dark:border-neutral-800 dark:bg-neutral-800/50">
                                    <tr className="hover:bg-transparent">
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Timestamp
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Reference Document
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Type
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Stock Mutation
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Qty After
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Transaction Cost
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350 pl-6">
                                            Notes / Operator
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {transactions.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="h-24 text-center text-neutral-500 dark:text-neutral-400 font-medium">
                                                No transactions logged in the ledger.
                                            </td>
                                        </tr>
                                    ) : (
                                        transactions.data.map((tx) => {
                                            const qtyChange = Number(tx.quantity_change);
                                            const isPositive = qtyChange > 0;

                                            return (
                                                <tr key={tx.id} className="hover:bg-neutral-100/30 dark:hover:bg-neutral-800/30 transition-colors">
                                                    <td className="p-4 align-middle text-neutral-550 dark:text-neutral-450 font-medium whitespace-nowrap">
                                                        {tx.created_at ? new Date(tx.created_at).toLocaleString('id-ID') : '-'}
                                                    </td>
                                                    <td className="p-4 align-middle font-semibold text-indigo-650 dark:text-indigo-400">
                                                        {tx.reference_type.split('\\').pop() ?? tx.reference_type} #{tx.reference_id}
                                                    </td>
                                                    <td className="p-4 align-middle">
                                                        <span className="capitalize font-medium text-neutral-700 dark:text-neutral-300">
                                                            {tx.transaction_type.replace('_', ' ')}
                                                        </span>
                                                    </td>
                                                    <td className={`p-4 align-middle text-right font-bold ${isPositive ? 'text-emerald-600' : 'text-rose-600'}`}>
                                                        <span className="inline-flex items-center gap-1">
                                                            {isPositive ? (
                                                                <ArrowDownLeft className="h-3.5 w-3.5" />
                                                            ) : (
                                                                <ArrowUpRight className="h-3.5 w-3.5" />
                                                            )}
                                                            {isPositive ? '+' : ''}{qtyChange.toLocaleString()}
                                                        </span>
                                                    </td>
                                                    <td className="p-4 align-middle text-right font-semibold text-neutral-800 dark:text-neutral-100">
                                                        {Number(tx.quantity_after).toLocaleString()}
                                                    </td>
                                                    <td className="p-4 align-middle text-right font-semibold text-neutral-800 dark:text-neutral-200">
                                                        {qtyChange !== 0 ? formatCurrency(tx.cost_price) : '-'}
                                                    </td>
                                                    <td className="p-4 align-middle text-neutral-550 dark:text-neutral-450 pl-6">
                                                        <div className="flex flex-col gap-0.5">
                                                            <span>{tx.notes || 'No description'}</span>
                                                            <span className="text-[10px] text-neutral-400">By {tx.creator?.name ?? 'System'}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            );
                                        })
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {transactions.links && transactions.links.length > 3 && (
                            <div className="flex items-center justify-between border-t border-neutral-200 p-4 dark:border-neutral-800">
                                <div className="text-xs text-neutral-500 dark:text-neutral-400">
                                    Showing page {transactions.current_page} of {transactions.last_page} ({transactions.total} total mutations)
                                </div>
                                <div className="flex items-center gap-1">
                                    {transactions.links.map((link, idx) => {
                                        const cleanLabel = link.label
                                            .replace('&laquo;', '‹')
                                            .replace('&raquo;', '›')
                                            .replace('Previous', '‹')
                                            .replace('Next', '›');

                                        if (!link.url) {
                                            return (
                                                <Button key={idx} variant="outline" size="sm" disabled className="px-2 text-xs">
                                                    {cleanLabel}
                                                </Button>
                                            );
                                        }

                                        return (
                                            <Button key={idx} variant={link.active ? 'default' : 'outline'} size="sm" asChild className="px-2 text-xs">
                                                <Link href={link.url}>
                                                    {cleanLabel}
                                                </Link>
                                            </Button>
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

InventoryShow.layout = {
    breadcrumbs: [
        {
            title: 'Inventory Stock',
            href: indexInventoryRoute().url,
        },
        {
            title: 'Audit Ledger',
            href: '',
        },
    ],
};
