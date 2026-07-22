import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexLeadsRoute, create as createLeadRoute, show as showLeadRoute, edit as editLeadRoute, destroy as destroyLeadRoute } from '@/routes/leads';
import type { Lead } from '@/types';
import { Plus, Eye, Pencil, Trash2, Users, UserCheck, Flame, Clock, Search } from 'lucide-react';
import { useState } from 'react';

interface Props {
    leads: {
        data: Lead[];
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

export default function LeadsIndex({ leads }: Props) {
    const { can } = usePermission();
    const [selectedTab, setSelectedTab] = useState<string>('all');
    const [searchQuery, setSearchQuery] = useState<string>('');

    const handleDelete = (lead: Lead) => {
        if (confirm(`Apakah Anda yakin ingin menghapus prospek ${lead.name}?`)) {
            router.delete(destroyLeadRoute(lead.id).url);
        }
    };

    const totalLeads = leads.total || leads.data.length;
    const qualifiedLeads = leads.data.filter(l => l.status === 'qualified').length;
    const newLeads = leads.data.filter(l => l.status === 'new').length;

    const filteredLeads = leads.data.filter((lead) => {
        const matchesTab =
            selectedTab === 'all' ? true :
            selectedTab === 'qualified' ? lead.status === 'qualified' :
            selectedTab === 'new' ? lead.status === 'new' :
            selectedTab === 'contacted' ? lead.status === 'contacted' : true;

        const matchesSearch = searchQuery === '' ||
            lead.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            (lead.company_name && lead.company_name.toLowerCase().includes(searchQuery.toLowerCase()));

        return matchesTab && matchesSearch;
    });

    return (
        <>
            <Head title="Prospek Sales (Leads)" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Prospek Penjualan (Leads)
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Kelola calon pembeli furnitur hotel/villa dan alur kualifikasi sales.
                        </p>
                    </div>
                    {can('create-leads') && (
                        <Button asChild className="bg-neutral-900 hover:bg-neutral-800 text-white dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-200">
                            <Link href={createLeadRoute()}>
                                <Plus className="mr-2 h-4 w-4" />
                                Buat Prospek Baru
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Total Prospek Aktif</p>
                                <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">{totalLeads}</h3>
                                <p className="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">+12.4% dari bulan lalu</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                                <Users className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Prospek Terkualifikasi</p>
                                <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">{qualifiedLeads} Prospek</h3>
                                <p className="text-[11px] text-neutral-500 mt-0.5">Siap untuk penawaran</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <UserCheck className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Prospek Baru</p>
                                <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">{newLeads} Prospek</h3>
                                <p className="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5 font-medium">Perlu diawali kontak</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <Flame className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Rata-rata Respon</p>
                                <h3 className="text-2xl font-bold text-neutral-900 dark:text-neutral-50 mt-1">&lt; 4 Jam</h3>
                                <p className="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">Standar SLA terpenuhi</p>
                            </div>
                            <div className="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <Clock className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-neutral-100/60 dark:bg-neutral-850/60 p-2 rounded-xl border border-neutral-200/60 dark:border-neutral-800">
                    <div className="flex items-center gap-1 overflow-x-auto">
                        <button onClick={() => setSelectedTab('all')} className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${selectedTab === 'all' ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-50 shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'}`}>Semua ({leads.data.length})</button>
                        <button onClick={() => setSelectedTab('new')} className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${selectedTab === 'new' ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-50 shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'}`}>Baru ({newLeads})</button>
                        <button onClick={() => setSelectedTab('contacted')} className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${selectedTab === 'contacted' ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-50 shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'}`}>Dihubungi</button>
                        <button onClick={() => setSelectedTab('qualified')} className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all ${selectedTab === 'qualified' ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-50 shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'}`}>Qualified ({qualifiedLeads})</button>
                    </div>
                    <div className="relative min-w-[220px]">
                        <Search className="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-neutral-400" />
                        <input type="text" placeholder="Cari prospek atau proyek..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-neutral-200 dark:border-neutral-750 bg-white dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-neutral-400" />
                    </div>
                </div>

                <Card className="flex-1 overflow-hidden border border-neutral-200 dark:border-neutral-800 shadow-sm rounded-xl bg-white dark:bg-neutral-900">
                    <CardContent className="p-0">
                        <div className="relative w-full overflow-auto">
                            <table className="w-full caption-bottom text-sm border-collapse">
                                <thead className="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-100/90 dark:bg-neutral-800/90">
                                    <tr className="hover:bg-transparent">
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">No. Referensi</th>
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">Kontak Utama</th>
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">Proyek & Perusahaan</th>
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">Sumber</th>
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">Status Prospek</th>
                                        <th className="py-3.5 px-4 text-left align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider border-r border-neutral-200/40 dark:border-neutral-700/40">Sales Rep</th>
                                        <th className="py-3.5 px-4 text-right align-middle text-[11px] font-bold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200/80 dark:divide-neutral-800/80">
                                    {filteredLeads.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="h-24 text-center text-neutral-500 dark:text-neutral-400">
                                                Tidak ada data prospek yang ditemukan.
                                            </td>
                                        </tr>
                                    ) : (
                                        filteredLeads.map((lead) => (
                                            <tr key={lead.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-850/60 transition-colors group">
                                                <td className="py-3.5 px-4 align-middle">
                                                    <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-300/80 dark:border-neutral-700">
                                                        {lead.reference_no ?? `LEAD-${lead.id}`}
                                                    </span>
                                                </td>
                                                <td className="py-3.5 px-4 align-middle">
                                                    <div className="flex items-center gap-3">
                                                        <div className="w-8 h-8 rounded-full bg-neutral-200 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 flex items-center justify-center font-bold text-xs shrink-0 border border-neutral-300/60 dark:border-neutral-700">
                                                            {lead.name.substring(0, 2).toUpperCase()}
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-neutral-900 dark:text-neutral-100 text-xs">{lead.name}</div>
                                                            <div className="text-[11px] text-neutral-500 dark:text-neutral-400">{lead.email || lead.phone || '-'}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="py-3.5 px-4 align-middle">
                                                    <span className="font-medium text-neutral-800 dark:text-neutral-200 text-xs">{lead.company_name ?? '-'}</span>
                                                </td>
                                                <td className="py-3.5 px-4 align-middle">
                                                    <span className="px-2 py-0.5 rounded text-[11px] font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-750 capitalize">
                                                        {lead.source}
                                                    </span>
                                                </td>
                                                <td className="py-3.5 px-4 align-middle">
                                                    <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700">
                                                        <span className={`h-1.5 w-1.5 rounded-full ${
                                                            lead.status === 'qualified' ? 'bg-emerald-500' :
                                                            lead.status === 'contacted' ? 'bg-amber-500' :
                                                            lead.status === 'new' ? 'bg-sky-500' :
                                                            'bg-rose-500'
                                                        }`} />
                                                        <span className="capitalize">{lead.status}</span>
                                                    </span>
                                                </td>
                                                <td className="py-3.5 px-4 align-middle text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                                    {typeof lead.assigned_to === 'object' && lead.assigned_to !== null
                                                        ? (lead.assigned_to as any).name
                                                        : lead.assigned_to_user?.name ?? 'Adly Pratama'}
                                                </td>
                                                <td className="py-3.5 px-4 align-middle text-right">
                                                    <div className="flex items-center justify-end gap-1">
                                                        <Button variant="ghost" size="icon" asChild className="h-8 w-8 text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 hover:bg-neutral-200/60 dark:hover:bg-neutral-800">
                                                            <Link href={showLeadRoute(lead.id)}>
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        {can('edit-leads') && (
                                                            <Button variant="ghost" size="icon" asChild className="h-8 w-8 text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-100 hover:bg-neutral-200/60 dark:hover:bg-neutral-800">
                                                                <Link href={editLeadRoute(lead.id)}>
                                                                    <Pencil className="h-4 w-4" />
                                                                </Link>
                                                            </Button>
                                                        )}
                                                        {can('delete-leads') && (
                                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(lead)} className="h-8 w-8 text-rose-600 hover:text-rose-700 hover:bg-rose-100/60 dark:hover:bg-rose-950/40">
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

                        {leads.links && leads.links.length > 3 && (
                            <div className="flex items-center justify-between border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50/80 dark:bg-neutral-900/80 px-4 py-3">
                                <div className="text-xs font-medium text-neutral-500 dark:text-neutral-400">
                                    Menampilkan {leads.current_page} dari {leads.last_page} ({leads.total} total prospek)
                                </div>
                                <div className="flex items-center gap-1">
                                    {leads.links.map((link, idx) => {
                                        const cleanLabel = link.label
                                            .replace('&laquo;', '‹')
                                            .replace('&raquo;', '›')
                                            .replace('Previous', '‹')
                                            .replace('Next', '›');

                                        if (!link.url) {
                                            return (
                                                <Button key={idx} variant="outline" size="sm" disabled className="px-2 text-xs border-neutral-200 dark:border-neutral-800">
                                                    {cleanLabel}
                                                </Button>
                                            );
                                        }

                                        return (
                                            <Button key={idx} variant={link.active ? 'default' : 'outline'} size="sm" asChild className="px-2 text-xs border-neutral-200 dark:border-neutral-800">
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

LeadsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Leads',
            href: indexLeadsRoute(),
        },
    ],
};
