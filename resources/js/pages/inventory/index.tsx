import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { usePermission } from '@/hooks/use-permission';
import { index as indexInventoryRoute, show as showInventoryRoute } from '@/routes/inventory';
import type { InventoryItem, Warehouse } from '@/types';
import { Eye, Search, Filter, AlertTriangle, Coins, Home, Package } from 'lucide-react';

interface Props {
    items: {
        data: InventoryItem[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        total: number;
    };
    warehouses: Warehouse[];
    totalValue: number;
    lowStockCount: number;
    filters: {
        search?: string;
        warehouse_id?: string;
    };
}

export default function InventoryIndex({ items, warehouses, totalValue, lowStockCount, filters }: Props) {
    const { can } = usePermission();

    const formatCurrency = (val: number | string) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(Number(val));
    };

    const handleFilter = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);
        const search = formData.get('search') as string;
        const warehouse_id = formData.get('warehouse_id') as string;

        router.get(indexInventoryRoute().url, { search, warehouse_id }, { preserveState: true });
    };

    return (
        <>
            <Head title="Inventory Stock" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex flex-col gap-1">
                        <div className="flex items-center gap-2">
                            <Package className="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Inventory Stock
                            </h1>
                        </div>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Real-time overview of product quantities, valuations, and allocations across all facilities.
                        </p>
                    </div>
                </div>

                {/* Dashboard Stats */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm bg-indigo-50/20 dark:bg-indigo-950/10">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-semibold text-neutral-600 dark:text-neutral-400">
                                Total Inventory Valuation
                            </CardTitle>
                            <Coins className="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-indigo-950 dark:text-indigo-50">
                                {formatCurrency(totalValue)}
                            </div>
                            <p className="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Cumulative average cost valuation.
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm bg-rose-50/20 dark:bg-rose-950/10">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-semibold text-neutral-600 dark:text-neutral-400">
                                Low Stock Alert items
                            </CardTitle>
                            <AlertTriangle className="h-4 w-4 text-rose-600 dark:text-rose-450" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-rose-600 dark:text-rose-450">
                                {lowStockCount}
                            </div>
                            <p className="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Items with total quantity under 5 units.
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-semibold text-neutral-600 dark:text-neutral-400">
                                Storage Facilities
                            </CardTitle>
                            <Home className="h-4 w-4 text-emerald-600 dark:text-emerald-450" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-450">
                                {warehouses.length}
                            </div>
                            <p className="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Total active storage hubs registered.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <form onSubmit={handleFilter} className="flex flex-col sm:flex-row items-center gap-4 bg-neutral-50 dark:bg-neutral-900 p-4 rounded-lg border border-neutral-200/60 dark:border-neutral-800/60">
                    <div className="flex-1 w-full relative">
                        <Search className="absolute left-3 top-2.5 h-4 w-4 text-neutral-450" />
                        <input
                            type="text"
                            name="search"
                            defaultValue={filters.search}
                            placeholder="Filter by SKU or description..."
                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white pl-9 pr-3 py-1 text-sm shadow-sm transition-colors placeholder:text-neutral-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:ring-indigo-400"
                        />
                    </div>

                    <div className="w-full sm:w-60">
                        <select
                            name="warehouse_id"
                            defaultValue={filters.warehouse_id}
                            className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:ring-indigo-400"
                        >
                            <option value="">All Warehouses</option>
                            {warehouses.map((wh) => (
                                <option key={wh.id} value={wh.id.toString()}>
                                    {wh.name} ({wh.code})
                                </option>
                            ))}
                        </select>
                    </div>

                    <Button type="submit" className="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white">
                        <Filter className="mr-2 h-4 w-4" />
                        Apply Filters
                    </Button>
                </form>

                {/* Stock Table */}
                <Card className="flex-1 overflow-hidden border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50 shadow-sm">
                    <CardContent className="p-0">
                        <div className="relative w-full overflow-auto">
                            <table className="w-full caption-bottom text-sm">
                                <thead className="border-b border-neutral-200 bg-neutral-100/50 dark:border-neutral-800 dark:bg-neutral-800/50">
                                    <tr className="hover:bg-transparent">
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            SKU
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Name
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Warehouse
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Physical Stock
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Reserved
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Available
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Avg Cost
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {items.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="h-24 text-center text-neutral-500 dark:text-neutral-400 font-medium">
                                                No inventory stock items found.
                                            </td>
                                        </tr>
                                    ) : (
                                        items.data.map((it) => {
                                            const available = Number(it.quantity_current) - Number(it.quantity_reserved);
                                            const isLowStock = Number(it.quantity_current) < 5;

                                            return (
                                                <tr key={it.id} className="hover:bg-neutral-100/30 dark:hover:bg-neutral-800/30 transition-colors">
                                                    <td className="p-4 align-middle font-semibold text-neutral-900 dark:text-neutral-50">
                                                        {it.sku}
                                                    </td>
                                                    <td className="p-4 align-middle font-medium text-neutral-700 dark:text-neutral-300">
                                                        {it.name}
                                                    </td>
                                                    <td className="p-4 align-middle text-neutral-650 dark:text-neutral-400 font-medium">
                                                        {it.warehouse?.name}
                                                    </td>
                                                    <td className="p-4 align-middle text-right font-semibold">
                                                        <span className={isLowStock ? 'text-rose-600 dark:text-rose-455 font-bold flex items-center justify-end gap-1' : 'text-neutral-900 dark:text-neutral-100'}>
                                                            {isLowStock && <AlertTriangle className="h-3.5 w-3.5" />}
                                                            {Number(it.quantity_current).toLocaleString()} {it.unit}
                                                        </span>
                                                    </td>
                                                    <td className="p-4 align-middle text-right text-amber-600 dark:text-amber-450 font-semibold">
                                                        {Number(it.quantity_reserved).toLocaleString()} {it.unit}
                                                    </td>
                                                    <td className="p-4 align-middle text-right text-emerald-600 dark:text-emerald-450 font-bold">
                                                        {available.toLocaleString()} {it.unit}
                                                    </td>
                                                    <td className="p-4 align-middle text-right font-medium text-neutral-800 dark:text-neutral-300">
                                                        {formatCurrency(it.avg_cost_price)}
                                                    </td>
                                                    <td className="p-4 align-middle text-right">
                                                        <Button variant="ghost" size="icon" asChild>
                                                            <Link href={showInventoryRoute(it.id).url}>
                                                                <Eye className="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                                                            </Link>
                                                        </Button>
                                                    </td>
                                                </tr>
                                            );
                                        })
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {items.links && items.links.length > 3 && (
                            <div className="flex items-center justify-between border-t border-neutral-200 p-4 dark:border-neutral-800">
                                <div className="text-xs text-neutral-500 dark:text-neutral-400">
                                    Showing page {items.current_page} of {items.last_page} ({items.total} total products)
                                </div>
                                <div className="flex items-center gap-1">
                                    {items.links.map((link, idx) => {
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

InventoryIndex.layout = {
    breadcrumbs: [
        {
            title: 'Inventory Stock',
            href: indexInventoryRoute().url,
        },
    ],
};
