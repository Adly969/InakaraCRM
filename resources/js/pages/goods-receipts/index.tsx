import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexReceiptsRoute, create as createReceiptRoute, show as showReceiptRoute } from '@/routes/goods-receipts';
import type { GoodsReceipt } from '@/types';
import { Eye, Plus, ArrowDownToLine, Search } from 'lucide-react';

interface Props {
    goodsReceipts: {
        data: GoodsReceipt[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
    };
}

export default function GoodsReceiptsIndex({ goodsReceipts, filters }: Props) {
    const { can } = usePermission();

    const handleSearch = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);
        const search = formData.get('search') as string;

        router.get(indexReceiptsRoute().url, { search }, { preserveState: true });
    };

    return (
        <>
            <Head title="Goods Receipts" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex flex-col gap-1">
                        <div className="flex items-center gap-2">
                            <ArrowDownToLine className="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Goods Receipts (GR)
                            </h1>
                        </div>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Log production order completions and physical warehousing stock entry receipts.
                        </p>
                    </div>
                    {can('create-goods-receipts') && (
                        <Button asChild className="bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">
                            <Link href={createReceiptRoute().url}>
                                <Plus className="mr-2 h-4 w-4" />
                                Create Goods Receipt
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="flex items-center justify-between gap-4">
                    <form onSubmit={handleSearch} className="flex w-full max-w-sm items-center gap-2">
                        <input
                            type="text"
                            name="search"
                            defaultValue={filters.search}
                            placeholder="Search reference number..."
                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-neutral-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950 dark:placeholder:text-neutral-450 dark:focus-visible:ring-indigo-400"
                        />
                        <Button type="submit" size="sm" variant="secondary">
                            Search
                        </Button>
                    </form>
                </div>

                <Card className="flex-1 overflow-hidden border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50 shadow-sm">
                    <CardContent className="p-0">
                        <div className="relative w-full overflow-auto">
                            <table className="w-full caption-bottom text-sm">
                                <thead className="border-b border-neutral-200 bg-neutral-100/50 dark:border-neutral-800 dark:bg-neutral-800/50">
                                    <tr className="hover:bg-transparent">
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Reference No
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Production Order Reference
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Destination Warehouse
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Date Received
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Status
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {goodsReceipts.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="h-24 text-center text-neutral-500 dark:text-neutral-400 font-medium">
                                                No Goods Receipts found.
                                            </td>
                                        </tr>
                                    ) : (
                                        goodsReceipts.data.map((gr) => (
                                            <tr key={gr.id} className="hover:bg-neutral-100/30 dark:hover:bg-neutral-800/30 transition-colors">
                                                <td className="p-4 align-middle font-bold text-neutral-900 dark:text-neutral-50">
                                                    {gr.reference_no}
                                                </td>
                                                <td className="p-4 align-middle font-medium text-neutral-600 dark:text-neutral-400">
                                                    {gr.production_order?.reference_no ?? 'Manual / Ad-hoc'}
                                                </td>
                                                <td className="p-4 align-middle font-medium text-indigo-650 dark:text-indigo-400">
                                                    {gr.warehouse?.name}
                                                </td>
                                                <td className="p-4 align-middle text-neutral-700 dark:text-neutral-300 font-medium">
                                                    {gr.received_date}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    <span className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold ring-1 ring-inset ${
                                                        gr.status === 'received' 
                                                            ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' 
                                                            : gr.status === 'draft'
                                                            ? 'bg-neutral-50 text-neutral-600 ring-neutral-500/20 dark:bg-neutral-800 dark:text-neutral-400'
                                                            : 'bg-red-50 text-red-700 ring-red-650/20 dark:bg-red-900/30 dark:text-red-400'
                                                    }`}>
                                                        {gr.status}
                                                    </span>
                                                </td>
                                                <td className="p-4 align-middle text-right">
                                                    <Button variant="ghost" size="icon" asChild>
                                                        <Link href={showReceiptRoute(gr.id).url}>
                                                            <Eye className="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                                                        </Link>
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {goodsReceipts.links && goodsReceipts.links.length > 3 && (
                            <div className="flex items-center justify-between border-t border-neutral-200 p-4 dark:border-neutral-800">
                                <div className="text-xs text-neutral-500 dark:text-neutral-400">
                                    Showing page {goodsReceipts.current_page} of {goodsReceipts.last_page} ({goodsReceipts.total} total receipts)
                                </div>
                                <div className="flex items-center gap-1">
                                    {goodsReceipts.links.map((link, idx) => {
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

GoodsReceiptsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Goods Receipts',
            href: indexReceiptsRoute().url,
        },
    ],
};
