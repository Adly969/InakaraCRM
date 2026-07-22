import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Package, Search, Filter, Warehouse as WarehouseIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface Warehouse {
    id: number;
    code: string;
    name: string;
}

interface Balance {
    id: number;
    warehouse: Warehouse;
    bin: { bin_code: string } | null;
    product: { sku: string; name: string; primaryUom?: { code: string } };
    batch_number: string | null;
    quantity_on_hand: number;
    quantity_reserved: number;
    quantity_available: number;
}

interface Props {
    balances: {
        data: Balance[];
        links: any[];
    };
    warehouses: Warehouse[];
    filters: {
        search?: string;
        warehouse_id?: string;
    };
}

export default function InventoryIndex({ balances, warehouses, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Warehouse', href: '#' },
        { title: 'Inventory Balances', href: '/inventory' },
    ];

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/inventory', { search }, { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Real-Time Stock Balances" />

            <div className="flex flex-col space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white flex items-center gap-2">
                        <Package className="h-6 w-6 text-sky-600" />
                        Real-Time Stock Balances
                    </h1>
                    <p className="text-sm text-neutral-500">Live stock quantities across warehouses, zones, and bins.</p>
                </div>

                <div className="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
                    <form onSubmit={handleSearch} className="flex gap-3">
                        <div className="relative flex-1">
                            <Search className="absolute left-3 top-2.5 h-4 w-4 text-neutral-400" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Filter by product name or SKU..."
                                className="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 focus:outline-none focus:ring-2 focus:ring-sky-500"
                            />
                        </div>
                        <Button type="submit" variant="secondary">Filter</Button>
                    </form>
                </div>

                <div className="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900 overflow-hidden shadow-xs">
                    <table className="w-full text-left text-sm text-neutral-600 dark:text-neutral-300">
                        <thead className="bg-neutral-50 dark:bg-neutral-950 border-b border-neutral-200 dark:border-neutral-800 text-xs font-semibold uppercase text-neutral-500">
                            <tr>
                                <th className="px-4 py-3">Warehouse / Bin</th>
                                <th className="px-4 py-3">Product SKU</th>
                                <th className="px-4 py-3">Product Name</th>
                                <th className="px-4 py-3 text-right">On Hand</th>
                                <th className="px-4 py-3 text-right">Reserved</th>
                                <th className="px-4 py-3 text-right">Available</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                            {balances.data.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-neutral-400">
                                        No inventory balances found.
                                    </td>
                                </tr>
                            ) : (
                                balances.data.map((b) => (
                                    <tr key={b.id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/50">
                                        <td className="px-4 py-3 font-medium text-neutral-900 dark:text-white">
                                            {b.warehouse?.name}
                                            {b.bin && <span className="block text-xs font-mono text-neutral-400">Bin: {b.bin.bin_code}</span>}
                                        </td>
                                        <td className="px-4 py-3 font-mono font-semibold text-neutral-900 dark:text-white">
                                            {b.product?.sku}
                                        </td>
                                        <td className="px-4 py-3 font-medium text-neutral-900 dark:text-white">
                                            {b.product?.name}
                                        </td>
                                        <td className="px-4 py-3 text-right font-mono font-semibold text-neutral-900 dark:text-white">
                                            {b.quantity_on_hand}
                                        </td>
                                        <td className="px-4 py-3 text-right font-mono text-amber-600">
                                            {b.quantity_reserved}
                                        </td>
                                        <td className="px-4 py-3 text-right font-mono font-bold text-emerald-600">
                                            {b.quantity_available}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
