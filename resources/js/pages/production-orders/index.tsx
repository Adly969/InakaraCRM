import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexProductionOrdersRoute, create as createProductionOrderRoute, show as showProductionOrderRoute, edit as editProductionOrderRoute, destroy as destroyProductionOrderRoute } from '@/routes/production-orders';
import type { ProductionOrder } from '@/types';
import { Plus, Eye, Pencil, Trash2, Search } from 'lucide-react';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
    productionOrders: {
        data: ProductionOrder[];
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
        status?: string;
        priority?: string;
        assigned_to?: string;
    };
}

export default function ProductionOrdersIndex({ productionOrders, filters }: Props) {
    const { can } = usePermission();
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [priority, setPriority] = useState(filters.priority ?? '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(indexProductionOrdersRoute(), { search, status, priority }, { preserveState: true });
    };

    const handleFilterChange = (newStatus: string, newPriority: string) => {
        setStatus(newStatus);
        setPriority(newPriority);
        router.get(indexProductionOrdersRoute(), { search, status: newStatus, priority: newPriority }, { preserveState: true });
    };

    const handleDelete = (po: ProductionOrder) => {
        if (confirm(`Are you sure you want to delete production order ${po.reference_no ?? po.subject}?`)) {
            router.delete(destroyProductionOrderRoute(po.id).url);
        }
    };

    return (
        <>
            <Head title="Production Orders" />
            <div className="flex h-full flex-1 flex-col gap-4 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex flex-col gap-1">
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Production Orders
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Manage internal manufacturing work orders, scheduling, and progress tracking.
                        </p>
                    </div>
                    {can('create-production-orders') && (
                        <Button asChild>
                            <Link href={createProductionOrderRoute()}>
                                <Plus className="mr-2 h-4 w-4" />
                                Create Production Order
                            </Link>
                        </Button>
                    )}
                </div>

                {/* Filter and Search Bar */}
                <form onSubmit={handleSearch} className="flex flex-wrap items-center gap-3">
                    <div className="relative flex-1 min-w-[240px]">
                        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-neutral-500" />
                        <input
                            type="search"
                            placeholder="Search by ref, subject, customer..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="w-full pl-9 pr-3 py-1.5 text-sm rounded-md border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 focus:outline-none focus:ring-1 focus:ring-neutral-400"
                        />
                    </div>
                    <select
                        value={status}
                        onChange={(e) => handleFilterChange(e.target.value, priority)}
                        className="px-3 py-1.5 text-sm rounded-md border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 focus:outline-none focus:ring-1 focus:ring-neutral-400"
                    >
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="in_production">In Production</option>
                        <option value="quality_control">Quality Control</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select
                        value={priority}
                        onChange={(e) => handleFilterChange(status, e.target.value)}
                        className="px-3 py-1.5 text-sm rounded-md border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 focus:outline-none focus:ring-1 focus:ring-neutral-400"
                    >
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    <Button type="submit" variant="secondary" size="sm">
                        Apply
                    </Button>
                </form>

                <Card className="flex-1 overflow-hidden border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                    <CardContent className="p-0">
                        <div className="relative w-full overflow-auto">
                            <table className="w-full caption-bottom text-sm">
                                <thead className="border-b border-neutral-200 bg-neutral-100/50 dark:border-neutral-800 dark:bg-neutral-800/50">
                                    <tr className="hover:bg-transparent">
                                        <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Reference No
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Subject
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Customer
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Status
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Priority
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Target Date
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Total Amount
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {productionOrders.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="h-24 text-center text-neutral-500 dark:text-neutral-400">
                                                No production orders found.
                                            </td>
                                        </tr>
                                    ) : (
                                        productionOrders.data.map((po) => (
                                            <tr key={po.id} className="hover:bg-neutral-100/30 dark:hover:bg-neutral-800/30 transition-colors">
                                                <td className="p-4 align-middle font-medium text-neutral-900 dark:text-neutral-50 whitespace-nowrap">
                                                    {po.reference_no ?? '-'}
                                                </td>
                                                <td className="p-4 align-middle truncate max-w-[200px]">
                                                    {po.subject}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    {po.customer ? (
                                                        <span className="text-neutral-900 dark:text-neutral-50 font-medium">
                                                            {po.customer.name}
                                                        </span>
                                                    ) : '-'}
                                                </td>
                                                <td className="p-4 align-middle">
                                                     <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-neutral-700/60">
                                                         <span className={`h-1.5 w-1.5 rounded-full ${
                                                             po.status === 'completed' ? 'bg-emerald-500' :
                                                             po.status === 'in_production' || po.status === 'quality_control' ? 'bg-sky-500' :
                                                             po.status === 'scheduled' || po.status === 'draft' ? 'bg-amber-500' :
                                                             'bg-rose-500'
                                                         }`} />
                                                         <span className="capitalize">{po.status.replace(/_/g, ' ')}</span>
                                                     </span>
                                                </td>
                                                <td className="p-4 align-middle">
                                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset ${
                                                        po.priority === 'low' ? 'bg-neutral-100 text-neutral-700 ring-neutral-600/10' :
                                                        po.priority === 'normal' ? 'bg-blue-100 text-blue-700 ring-blue-600/10' :
                                                        po.priority === 'high' ? 'bg-orange-100 text-orange-700 ring-orange-600/10' :
                                                        'bg-red-100 text-red-700 ring-red-600/10' // urgent
                                                    }`}>
                                                        {po.priority}
                                                    </span>
                                                </td>
                                                <td className="p-4 align-middle whitespace-nowrap text-neutral-600 dark:text-neutral-300">
                                                    {po.target_completion_date ?? '-'}
                                                </td>
                                                <td className="p-4 align-middle font-mono">
                                                    {po.currency} {Number(po.total_amount).toLocaleString()}
                                                </td>
                                                <td className="p-4 align-middle text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Button variant="ghost" size="icon" asChild>
                                                            <Link href={showProductionOrderRoute(po.id)}>
                                                                 <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        {can('edit-production-orders') && ['draft', 'scheduled'].includes(po.status) && (
                                                            <Button variant="ghost" size="icon" asChild>
                                                                <Link href={editProductionOrderRoute(po.id)}>
                                                                    <Pencil className="h-4 w-4" />
                                                                </Link>
                                                            </Button>
                                                        )}
                                                        {can('delete-production-orders') && (
                                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(po)} className="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30">
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

                        {productionOrders.links && productionOrders.links.length > 3 && (
                            <div className="flex items-center justify-between border-t border-neutral-200 p-4 dark:border-neutral-800">
                                <div className="text-xs text-neutral-500">
                                    Showing page {productionOrders.current_page} of {productionOrders.last_page} ({productionOrders.total} total production orders)
                                </div>
                                <div className="flex items-center gap-1">
                                    {productionOrders.links.map((link, idx) => {
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

ProductionOrdersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Production Orders',
            href: indexProductionOrdersRoute(),
        },
    ],
};

