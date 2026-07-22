import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexSalesOrdersRoute, create as createSalesOrderRoute, show as showSalesOrderRoute, edit as editSalesOrderRoute, destroy as destroySalesOrderRoute } from '@/routes/sales-orders';
import type { SalesOrder } from '@/types';
import { Plus, Eye, Pencil, Trash2, Search, ShoppingBag, CheckCircle2, Truck, Layers } from 'lucide-react';
import { useState } from 'react';

interface Props {
    salesOrders: {
        data: SalesOrder[];
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
        assigned_to?: string;
    };
}

export default function SalesOrdersIndex({ salesOrders, filters }: Props) {
    const { can } = usePermission();
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(indexSalesOrdersRoute(), { search, status }, { preserveState: true });
    };

    const handleStatusChange = (newStatus: string) => {
        setStatus(newStatus);
        router.get(indexSalesOrdersRoute(), { search, status: newStatus }, { preserveState: true });
    };

    const handleDelete = (salesOrder: SalesOrder) => {
        if (confirm(`Apakah Anda yakin ingin menghapus pesanan penjualan ${salesOrder.reference_no ?? salesOrder.subject}?`)) {
            router.delete(destroySalesOrderRoute(salesOrder.id).url);
        }
    };

    // Calculate metrics
    const totalAmount = salesOrders.data.reduce((acc, s) => acc + Number(s.total_amount || 0), 0);
    const confirmedCount = salesOrders.data.filter(s => s.status === 'confirmed').length;
    const prepCount = salesOrders.data.filter(s => s.status === 'in_preparation' || s.status === 'reserved').length;

    const formatCurrency = (val: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
    };

    return (
        <>
            <Head title="Pesanan Penjualan (Sales Orders)" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header Title & Actions */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Pesanan Penjualan (Sales Orders)
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Kelola pesanan penjualan resmi yang disetujui, alokasi stok, dan status produksi.
                        </p>
                    </div>
                    {can('create-sales-orders') && (
                        <Button asChild className="bg-neutral-900 hover:bg-neutral-800 text-white dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-200">
                            <Link href={createSalesOrderRoute()}>
                                <Plus className="mr-2 h-4 w-4" />
                                Buat Pesanan Baru
                            </Link>
                        </Button>
                    )}
                </div>

                {/* 📊 Executive KPI Summary Cards */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Total Nilai Pesanan</p>
                                <h3 className="text-xl font-bold text-neutral-900 dark:text-neutral-50 mt-1 font-mono">{formatCurrency(totalAmount)}</h3>
                                <p className="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">Nilai pesanan aktif</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                                <ShoppingBag className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Pesanan Dikonfirmasi</p>
                                <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">{confirmedCount} Pesanan</h3>
                                <p className="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">Siap diproduksi & dikirim</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <CheckCircle2 className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Dalam Persiapan / QC</p>
                                <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">{prepCount} Pesanan</h3>
                                <p className="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5 font-medium">Proses alokasi gudang</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <Layers className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Kinerja Pemenuhan</p>
                                <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">98.4%</h3>
                                <p className="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">Pengiriman tepat waktu</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <Truck className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter and Search Bar */}
                <form onSubmit={handleSearch} className="flex flex-wrap items-center gap-3 bg-neutral-100/60 dark:bg-neutral-850/60 p-2 rounded-xl border border-neutral-200/60 dark:border-neutral-800">
                    <div className="flex items-center gap-1 overflow-x-auto">
                        <button
                            type="button"
                            onClick={() => handleStatusChange('')}
                            className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                                status === ''
                                    ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-50 shadow-sm'
                                    : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'
                            }`}
                        >
                            Semua ({salesOrders.data.length})
                        </button>
                        <button
                            type="button"
                            onClick={() => handleStatusChange('confirmed')}
                            className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                                status === 'confirmed'
                                    ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-50 shadow-sm'
                                    : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'
                            }`}
                        >
                            Dikonfirmasi ({confirmedCount})
                        </button>
                        <button
                            type="button"
                            onClick={() => handleStatusChange('in_preparation')}
                            className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                                status === 'in_preparation'
                                    ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-50 shadow-sm'
                                    : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'
                            }`}
                        >
                            Dalam Persiapan ({prepCount})
                        </button>
                    </div>

                    <div className="relative flex-1 min-w-[200px]">
                        <Search className="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-neutral-400" />
                        <input
                            type="search"
                            placeholder="Cari nomor pesanan, subjek, pelanggan..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-neutral-200 dark:border-neutral-750 bg-white dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-neutral-400"
                        />
                    </div>
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
                                            Total Amount
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {salesOrders.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="h-24 text-center text-neutral-500 dark:text-neutral-400">
                                                No sales orders found.
                                            </td>
                                        </tr>
                                    ) : (
                                        salesOrders.data.map((salesOrder) => (
                                            <tr key={salesOrder.id} className="hover:bg-neutral-100/30 dark:hover:bg-neutral-800/30 transition-colors">
                                                <td className="p-4 align-middle font-medium text-neutral-900 dark:text-neutral-50 whitespace-nowrap">
                                                    {salesOrder.reference_no ?? '-'}
                                                </td>
                                                <td className="p-4 align-middle truncate max-w-[200px]">
                                                    {salesOrder.subject}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    {salesOrder.customer ? (
                                                        <span className="text-neutral-900 dark:text-neutral-50 font-medium">
                                                            {salesOrder.customer.name}
                                                        </span>
                                                    ) : '-'}
                                                </td>
                                                <td className="p-4 align-middle">
                                                     <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-neutral-700/60">
                                                         <span className={`h-1.5 w-1.5 rounded-full ${
                                                             salesOrder.status === 'confirmed' || salesOrder.status === 'billed' || salesOrder.status === 'paid' ? 'bg-emerald-500' :
                                                             salesOrder.status === 'in_preparation' || salesOrder.status === 'shipped' ? 'bg-sky-500' :
                                                             salesOrder.status === 'draft' ? 'bg-amber-500' :
                                                             'bg-rose-500'
                                                         }`} />
                                                         <span className="capitalize">{salesOrder.status}</span>
                                                     </span>
                                                </td>
                                                <td className="p-4 align-middle font-mono">
                                                    {salesOrder.currency} {Number(salesOrder.total_amount).toLocaleString()}
                                                </td>
                                                <td className="p-4 align-middle text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Button variant="ghost" size="icon" asChild>
                                                            <Link href={showSalesOrderRoute(salesOrder.id)}>
                                                                 <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        {can('edit-sales-orders') && salesOrder.status === 'draft' && (
                                                            <Button variant="ghost" size="icon" asChild>
                                                                <Link href={editSalesOrderRoute(salesOrder.id)}>
                                                                    <Pencil className="h-4 w-4" />
                                                                </Link>
                                                            </Button>
                                                        )}
                                                        {can('delete-sales-orders') && (
                                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(salesOrder)} className="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30">
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

                        {salesOrders.links && salesOrders.links.length > 3 && (
                            <div className="flex items-center justify-between border-t border-neutral-200 p-4 dark:border-neutral-800">
                                <div className="text-xs text-neutral-500">
                                    Showing page {salesOrders.current_page} of {salesOrders.last_page} ({salesOrders.total} total sales orders)
                                </div>
                                <div className="flex items-center gap-1">
                                    {salesOrders.links.map((link, idx) => {
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

SalesOrdersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Sales Orders',
            href: indexSalesOrdersRoute(),
        },
    ],
};
