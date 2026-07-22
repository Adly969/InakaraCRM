import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import type { DeliveryOrder, Warehouse } from '@/types';
import { Eye, Plus, Truck, Search } from 'lucide-react';
import React from 'react';

interface Props {
    deliveryOrders: {
        data: DeliveryOrder[];
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
        warehouse_id?: string;
    };
    warehouses: Warehouse[];
}

export default function DeliveryOrdersIndex({ deliveryOrders, filters, warehouses }: Props) {
    const { can } = usePermission();

    const handleSearch = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);
        const search = formData.get('search') as string;
        const status = formData.get('status') as string;
        const warehouse_id = formData.get('warehouse_id') as string;

        router.get('/delivery-orders', { search, status, warehouse_id }, { preserveState: true });
    };

    const getStatusBadgeClass = (status: string) => {
        switch (status) {
            case 'draft':
                return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
            case 'approved':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
            case 'partially_shipped':
                return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400';
            case 'shipped':
                return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400';
            case 'partially_delivered':
                return 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-400';
            case 'delivered':
                return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
            case 'cancelled':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            default:
                return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
        }
    };

    const getStatusLabel = (status: string) => {
        return status.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
    };

    return (
        <>
            <Head title="Delivery Orders" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex flex-col gap-1">
                        <div className="flex items-center gap-2">
                            <Truck className="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Delivery Orders (DO)
                            </h1>
                        </div>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Fulfill customer shipments, track outstanding quantities, and dispatch cargo.
                        </p>
                    </div>
                    {can('create-delivery-orders') && (
                        <Button asChild className="bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">
                            <Link href="/delivery-orders/create">
                                <Plus className="mr-2 h-4 w-4" />
                                Create Delivery Order
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="flex items-center justify-between gap-4">
                    <form onSubmit={handleSearch} className="flex w-full flex-wrap items-center gap-3">
                        <div className="relative flex-1 min-w-[200px] max-w-xs">
                            <input
                                type="text"
                                name="search"
                                defaultValue={filters.search}
                                placeholder="Search DO reference..."
                                className="flex h-9 w-full rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-neutral-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950 dark:placeholder:text-neutral-450 dark:focus-visible:ring-indigo-400"
                            />
                        </div>
                        
                        <select
                            name="warehouse_id"
                            defaultValue={filters.warehouse_id}
                            className="flex h-9 w-full max-w-[180px] rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:ring-indigo-400"
                        >
                            <option value="">All Warehouses</option>
                            {warehouses.map((wh) => (
                                <option key={wh.id} value={wh.id}>
                                    {wh.name}
                                </option>
                            ))}
                        </select>

                        <select
                            name="status"
                            defaultValue={filters.status}
                            className="flex h-9 w-full max-w-[180px] rounded-md border border-neutral-200 bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-600 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:ring-indigo-400"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                            <option value="partially_shipped">Partially Shipped</option>
                            <option value="shipped">Shipped</option>
                            <option value="partially_delivered">Partially Delivered</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>

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
                                            Sales Order Ref
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Destination Warehouse
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Customer
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Status
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200/60 dark:divide-neutral-850/60">
                                    {deliveryOrders.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="h-32 text-center text-neutral-500 dark:text-neutral-400">
                                                No Delivery Orders found.
                                            </td>
                                        </tr>
                                    ) : (
                                        deliveryOrders.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-neutral-100/40 dark:hover:bg-neutral-800/40 transition-colors">
                                                <td className="p-4 align-middle font-medium text-neutral-900 dark:text-neutral-100">
                                                    {item.reference_no}
                                                </td>
                                                <td className="p-4 align-middle text-neutral-700 dark:text-neutral-300">
                                                    {item.sales_order?.reference_no}
                                                </td>
                                                <td className="p-4 align-middle text-neutral-700 dark:text-neutral-300">
                                                    {item.warehouse?.name}
                                                </td>
                                                <td className="p-4 align-middle text-neutral-700 dark:text-neutral-300">
                                                    {item.customer?.name}
                                                </td>
                                                 <td className="p-4 align-middle">
                                                     <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-neutral-700/60">
                                                         <span className={`h-1.5 w-1.5 rounded-full ${
                                                             item.status === 'delivered' ? 'bg-emerald-500' :
                                                             item.status === 'shipped' || item.status === 'approved' ? 'bg-sky-500' :
                                                             item.status === 'draft' ? 'bg-amber-500' :
                                                             'bg-rose-500'
                                                         }`} />
                                                         <span className="capitalize">{getStatusLabel(item.status)}</span>
                                                     </span>
                                                 </td>
                                                <td className="p-4 align-middle text-right">
                                                    <Button asChild variant="ghost" size="sm" className="hover:bg-neutral-200 dark:hover:bg-neutral-800">
                                                        <Link href={`/delivery-orders/${item.id}`}>
                                                            <Eye className="h-4 w-4 mr-1 text-neutral-600 dark:text-neutral-400" />
                                                            View
                                                        </Link>
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Simple Pagination */}
                        {deliveryOrders.last_page > 1 && (
                            <div className="flex items-center justify-end space-x-2 border-t border-neutral-200 dark:border-neutral-800 p-4">
                                {deliveryOrders.links.map((link, idx) => {
                                    if (!link.url) return null;
                                    return (
                                        <Button
                                            key={idx}
                                            variant={link.active ? 'default' : 'outline'}
                                            size="sm"
                                            asChild
                                            className={link.active ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : ''}
                                        >
                                            <Link href={link.url} dangerouslySetInnerHTML={{ __html: link.label }} />
                                        </Button>
                                    );
                                })}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
