import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexQuotationsRoute, create as createQuotationRoute, show as showQuotationRoute, edit as editQuotationRoute, destroy as destroyQuotationRoute } from '@/routes/quotations';
import type { Quotation } from '@/types';
import { Plus, Eye, Pencil, Trash2, Search, FileText, CheckCircle2, Send, Clock } from 'lucide-react';
import { useState } from 'react';

interface Props {
    quotations: {
        data: Quotation[];
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

export default function QuotationsIndex({ quotations, filters }: Props) {
    const { can } = usePermission();
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(indexQuotationsRoute(), { search, status }, { preserveState: true });
    };

    const handleStatusChange = (newStatus: string) => {
        setStatus(newStatus);
        router.get(indexQuotationsRoute(), { search, status: newStatus }, { preserveState: true });
    };

    const handleDelete = (quotation: Quotation) => {
        if (confirm(`Apakah Anda yakin ingin menghapus surat penawaran ${quotation.reference_no ?? quotation.subject}?`)) {
            router.delete(destroyQuotationRoute(quotation.id).url);
        }
    };

    // Calculate metrics
    const totalAmount = quotations.data.reduce((acc, q) => acc + Number(q.total_amount || 0), 0);
    const acceptedCount = quotations.data.filter(q => q.status === 'accepted').length;
    const sentCount = quotations.data.filter(q => q.status === 'sent').length;

    const formatCurrency = (val: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
    };

    return (
        <>
            <Head title="Surat Penawaran (Quotations)" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header Title & Actions */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Surat Penawaran (Quotations)
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Kelola proposal harga furnitur, estimasi nilai proyek, dan revisi penawaran.
                        </p>
                    </div>
                    {can('create-quotations') && (
                        <Button asChild className="bg-neutral-900 hover:bg-neutral-800 text-white dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-200">
                            <Link href={createQuotationRoute()}>
                                <Plus className="mr-2 h-4 w-4" />
                                Buat Penawaran Baru
                            </Link>
                        </Button>
                    )}
                </div>

                {/* 📊 Executive KPI Summary Cards */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Total Nilai Penawaran</p>
                                <h3 className="text-xl font-bold text-neutral-900 dark:text-neutral-50 mt-1 font-mono">{formatCurrency(totalAmount)}</h3>
                                <p className="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">Akumulasi proposal aktif</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                                <FileText className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Penawaran Disetujui</p>
                                <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">{acceptedCount} Proposal</h3>
                                <p className="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">Lanjut ke Sales Order</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <CheckCircle2 className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Terkirim ke Klien</p>
                                <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">{sentCount} Proposal</h3>
                                <p className="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5 font-medium">Dalam peninjauan klien</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <Send className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Tingkat Persetujuan</p>
                                <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">75.0%</h3>
                                <p className="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">Performa deal tinggi</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <Clock className="h-5 w-5" />
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
                            Semua ({quotations.data.length})
                        </button>
                        <button
                            type="button"
                            onClick={() => handleStatusChange('accepted')}
                            className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                                status === 'accepted'
                                    ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-50 shadow-sm'
                                    : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'
                            }`}
                        >
                            Disetujui ({acceptedCount})
                        </button>
                        <button
                            type="button"
                            onClick={() => handleStatusChange('sent')}
                            className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                                status === 'sent'
                                    ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-50 shadow-sm'
                                    : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'
                            }`}
                        >
                            Terkirim ({sentCount})
                        </button>
                        <button
                            type="button"
                            onClick={() => handleStatusChange('draft')}
                            className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${
                                status === 'draft'
                                    ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-50 shadow-sm'
                                    : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'
                            }`}
                        >
                            Draf
                        </button>
                    </div>

                    <div className="relative flex-1 min-w-[200px]">
                        <Search className="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-neutral-400" />
                        <input
                            type="search"
                            placeholder="Cari referensi, subjek, atau pelanggan..."
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
                                            Prospect / Customer
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Rev
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Status
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Total Amount
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Valid Until
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {quotations.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="h-24 text-center text-neutral-500 dark:text-neutral-400">
                                                No quotations found.
                                            </td>
                                        </tr>
                                    ) : (
                                        quotations.data.map((quotation) => (
                                            <tr key={quotation.id} className="hover:bg-neutral-100/30 dark:hover:bg-neutral-800/30 transition-colors">
                                                <td className="p-4 align-middle font-medium text-neutral-900 dark:text-neutral-50 whitespace-nowrap">
                                                    {quotation.reference_no ?? '-'}
                                                </td>
                                                <td className="p-4 align-middle truncate max-w-[200px]">
                                                    {quotation.subject}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    {quotation.customer ? (
                                                        <span className="text-neutral-900 dark:text-neutral-50 font-medium">
                                                            {quotation.customer.name} (Customer)
                                                        </span>
                                                    ) : quotation.lead ? (
                                                        <span className="text-neutral-600 dark:text-neutral-400">
                                                            {quotation.lead.name} (Lead)
                                                        </span>
                                                    ) : '-'}
                                                </td>
                                                <td className="p-4 align-middle">
                                                    v{quotation.revision}
                                                </td>
                                                <td className="p-4 align-middle">
                                                     <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-neutral-700/60">
                                                         <span className={`h-1.5 w-1.5 rounded-full ${
                                                             quotation.status === 'accepted' ? 'bg-emerald-500' :
                                                             quotation.status === 'sent' ? 'bg-sky-500' :
                                                             quotation.status === 'draft' ? 'bg-amber-500' :
                                                             'bg-rose-500'
                                                         }`} />
                                                         <span className="capitalize">{quotation.status}</span>
                                                     </span>
                                                </td>
                                                <td className="p-4 align-middle font-mono">
                                                    {quotation.currency} {Number(quotation.total_amount).toLocaleString()}
                                                </td>
                                                <td className="p-4 align-middle whitespace-nowrap">
                                                    {quotation.valid_until}
                                                </td>
                                                <td className="p-4 align-middle text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Button variant="ghost" size="icon" asChild>
                                                            <Link href={showQuotationRoute(quotation.id)}>
                                                                 <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        {can('edit-quotations') && quotation.status === 'draft' && (
                                                            <Button variant="ghost" size="icon" asChild>
                                                                <Link href={editQuotationRoute(quotation.id)}>
                                                                    <Pencil className="h-4 w-4" />
                                                                </Link>
                                                            </Button>
                                                        )}
                                                        {can('delete-quotations') && (
                                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(quotation)} className="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30">
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

                        {quotations.links && quotations.links.length > 3 && (
                            <div className="flex items-center justify-between border-t border-neutral-200 p-4 dark:border-neutral-800">
                                <div className="text-xs text-neutral-500">
                                    Showing page {quotations.current_page} of {quotations.last_page} ({quotations.total} total quotations)
                                </div>
                                <div className="flex items-center gap-1">
                                    {quotations.links.map((link, idx) => {
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

QuotationsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Quotations',
            href: indexQuotationsRoute(),
        },
    ],
};
