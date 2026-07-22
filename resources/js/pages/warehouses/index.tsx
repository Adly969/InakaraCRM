import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { usePermission } from '@/hooks/use-permission';
import { index as indexWarehousesRoute, create as createWarehouseRoute, edit as editWarehouseRoute, destroy as destroyWarehouseRoute } from '@/routes/warehouses';
import type { Warehouse } from '@/types';
import { Plus, Pencil, Trash2, Shield, Warehouse as WhIcon } from 'lucide-react';

interface Props {
    warehouses: {
        data: Warehouse[];
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

export default function WarehousesIndex({ warehouses, filters }: Props) {
    const { can } = usePermission();

    const handleDelete = (warehouse: Warehouse) => {
        if (confirm(`Are you sure you want to delete warehouse ${warehouse.name}?`)) {
            router.delete(destroyWarehouseRoute(warehouse.id).url);
        }
    };

    const handleSearch = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);
        const search = formData.get('search') as string;
        
        router.get(indexWarehousesRoute().url, { search }, { preserveState: true });
    };

    return (
        <>
            <Head title="Warehouses" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex flex-col gap-1">
                        <div className="flex items-center gap-2">
                            <WhIcon className="h-6 w-6 text-sky-600 dark:text-sky-400" />
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Warehouses
                            </h1>
                        </div>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Manage physical storage locations, types, and inventory distribution.
                        </p>
                    </div>
                    {can('create-warehouses') && (
                        <Button asChild className="bg-neutral-900 hover:bg-neutral-800 text-white dark:bg-neutral-100 dark:text-neutral-900 font-bold rounded-xl shadow-xs">
                            <Link href={createWarehouseRoute().url}>
                                <Plus className="mr-2 h-4 w-4 text-emerald-500" />
                                Create Warehouse
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
                            placeholder="Search code, name..."
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
                                            Code
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Name
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Type
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Manager
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Default
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
                                    {warehouses.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="h-24 text-center text-neutral-500 dark:text-neutral-400 font-medium">
                                                No warehouses found.
                                            </td>
                                        </tr>
                                    ) : (
                                        warehouses.data.map((wh) => (
                                            <tr key={wh.id} className="hover:bg-neutral-100/30 dark:hover:bg-neutral-800/30 transition-colors">
                                                <td className="p-4 align-middle font-semibold text-neutral-900 dark:text-neutral-50">
                                                    {wh.code}
                                                </td>
                                                <td className="p-4 align-middle font-medium text-neutral-700 dark:text-neutral-300">
                                                    {wh.name}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${
                                                        wh.type === 'central'
                                                            ? 'bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-400'
                                                            : wh.type === 'transit'
                                                            ? 'bg-amber-50 text-amber-700 ring-amber-700/10 dark:bg-amber-900/30 dark:text-amber-400'
                                                            : 'bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-900/30 dark:text-red-400'
                                                    }`}>
                                                        {wh.type}
                                                    </span>
                                                </td>
                                                <td className="p-4 align-middle text-neutral-600 dark:text-neutral-400">
                                                    {wh.manager?.name ?? '-'}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    {wh.is_default ? (
                                                        <Badge variant="default" className="bg-sky-600 text-white dark:bg-sky-500">
                                                            Default
                                                        </Badge>
                                                    ) : (
                                                        <span className="text-xs text-neutral-400 dark:text-neutral-500">No</span>
                                                    )}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    <span className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold ring-1 ring-inset ${
                                                        wh.status === 'active' 
                                                            ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' 
                                                            : 'bg-neutral-50 text-neutral-650 ring-neutral-500/20 dark:bg-neutral-800 dark:text-neutral-400'
                                                    }`}>
                                                        {wh.status}
                                                    </span>
                                                </td>
                                                <td className="p-4 align-middle text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        {can('edit-warehouses') && (
                                                            <Button variant="ghost" size="icon" asChild>
                                                                <Link href={editWarehouseRoute(wh.id).url}>
                                                                    <Pencil className="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                                                                </Link>
                                                            </Button>
                                                        )}
                                                        {can('delete-warehouses') && !wh.is_default && (
                                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(wh)} className="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30">
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                        
                        {warehouses.links && warehouses.links.length > 3 && (
                            <div className="flex items-center justify-between border-t border-neutral-200 p-4 dark:border-neutral-800">
                                <div className="text-xs text-neutral-500 dark:text-neutral-400">
                                    Showing page {warehouses.current_page} of {warehouses.last_page} ({warehouses.total} total warehouses)
                                </div>
                                <div className="flex items-center gap-1">
                                    {warehouses.links.map((link, idx) => {
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

WarehousesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Warehouses',
            href: indexWarehousesRoute().url,
        },
    ],
};
