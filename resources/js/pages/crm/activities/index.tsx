import { Head, Link, useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { Activity, Plus, Search, Eye, Phone, Mail, Calendar, MapPin, FileText, MessageCircle, Monitor, Presentation, Handshake, CheckSquare, Clock, Filter } from 'lucide-react';
import { useState } from 'react';

interface ActivityItem {
    id: number;
    activity_type: string;
    subject: string;
    description: string | null;
    start_time: string;
    end_time: string | null;
    status: string;
    outcome: string | null;
    priority: string;
    customer?: { id: number; name: string };
    lead?: { id: number; first_name: string; last_name: string; company_name: string };
    opportunity?: { id: number; title: string };
    assigned_to?: { id: number; name: string };
    creator?: { id: number; name: string };
}

interface Props {
    activities: {
        data: ActivityItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
    };
    filters: Record<string, string>;
    activityTypes: Array<{ value: string; label: string; icon: string }>;
    outcomes: Array<{ value: string; label: string; color: string }>;
    users: Array<{ id: number; name: string }>;
}

export default function ActivitiesIndex({ activities, filters, activityTypes, outcomes, users }: Props) {
    const { can } = usePermission();
    const [searchQuery, setSearchQuery] = useState(filters.search || '');
    const [selectedType, setSelectedType] = useState(filters.activity_type || 'all');
    const [showCreateModal, setShowCreateModal] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        activity_type: 'phone_call',
        subject: '',
        description: '',
        start_time: new Date().toISOString().slice(0, 16),
        priority: 'medium',
        assigned_to: '',
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/crm/activities', { search: searchQuery, activity_type: selectedType === 'all' ? '' : selectedType }, { preserveState: true });
    };

    const handleCreate = (e: React.FormEvent) => {
        e.preventDefault();
        post('/crm/activities', {
            onSuccess: () => {
                setShowCreateModal(false);
                reset();
            },
        });
    };

    const getTypeIcon = (type: string) => {
        switch (type) {
            case 'phone_call': return <Phone className="h-4 w-4 text-sky-600" />;
            case 'email': return <Mail className="h-4 w-4 text-emerald-600" />;
            case 'meeting': return <Calendar className="h-4 w-4 text-amber-600" />;
            case 'site_visit': return <MapPin className="h-4 w-4 text-purple-600" />;
            case 'whatsapp': return <MessageCircle className="h-4 w-4 text-green-600" />;
            case 'demo': return <Monitor className="h-4 w-4 text-indigo-600" />;
            case 'presentation': return <Presentation className="h-4 w-4 text-pink-600" />;
            case 'negotiation': return <Handshake className="h-4 w-4 text-blue-600" />;
            case 'task': return <CheckSquare className="h-4 w-4 text-neutral-600" />;
            default: return <FileText className="h-4 w-4 text-neutral-600" />;
        }
    };

    return (
        <>
            <Head title="Aktivitas CRM (Activities)" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 flex items-center gap-2">
                            <Activity className="h-6 w-6 text-sky-600" />
                            Aktivitas Penjualan (Activities)
                        </h1>
                        <p className="text-sm text-neutral-500">
                            Catatan panggilan telepon, rapat, kunjungan lapangan, dan tindak lanjut prospek.
                        </p>
                    </div>

                    {can('create-activities') && (
                        <Button onClick={() => setShowCreateModal(true)} className="bg-sky-600 hover:bg-sky-700 text-white">
                            <Plus className="mr-2 h-4 w-4" /> Catat Aktivitas Baru
                        </Button>
                    )}
                </div>

                {/* Metric Summary Cards */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <Card className="border-neutral-200 dark:border-neutral-800">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500">Total Aktivitas</p>
                                <p className="text-2xl font-bold text-neutral-900 dark:text-neutral-50">{activities.total}</p>
                            </div>
                            <div className="p-2 bg-sky-50 rounded-lg text-sky-600 dark:bg-sky-950">
                                <Activity className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-neutral-200 dark:border-neutral-800">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500">Pending Follow-up</p>
                                <p className="text-2xl font-bold text-amber-600">{activities.data.filter(a => a.status === 'pending').length}</p>
                            </div>
                            <div className="p-2 bg-amber-50 rounded-lg text-amber-600 dark:bg-amber-950">
                                <Clock className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-neutral-200 dark:border-neutral-800">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500">Selesai (Completed)</p>
                                <p className="text-2xl font-bold text-emerald-600">{activities.data.filter(a => a.status === 'completed').length}</p>
                            </div>
                            <div className="p-2 bg-emerald-50 rounded-lg text-emerald-600 dark:bg-emerald-950">
                                <CheckSquare className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter & Search Bar */}
                <Card className="border-neutral-200 dark:border-neutral-800">
                    <CardContent className="p-4">
                        <form onSubmit={handleSearch} className="flex flex-col gap-4 sm:flex-row sm:items-center justify-between">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" />
                                <input
                                    type="text"
                                    placeholder="Cari subjek atau catatan aktivitas..."
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    className="w-full pl-9 pr-4 py-2 text-sm border rounded-lg border-neutral-200 dark:border-neutral-800 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                />
                            </div>
                            <div className="flex items-center gap-2">
                                <Filter className="h-4 w-4 text-neutral-400" />
                                <select
                                    value={selectedType}
                                    onChange={(e) => {
                                        setSelectedType(e.target.value);
                                        router.get('/crm/activities', { search: searchQuery, activity_type: e.target.value === 'all' ? '' : e.target.value }, { preserveState: true });
                                    }}
                                    className="px-3 py-2 text-sm border rounded-lg border-neutral-200 dark:border-neutral-800 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                >
                                    <option value="all">Semua Jenis Aktivitas</option>
                                    {activityTypes.map((t) => (
                                        <option key={t.value} value={t.value}>{t.label}</option>
                                    ))}
                                </select>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Data Table */}
                <Card className="border-neutral-200 dark:border-neutral-800">
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 text-xs font-semibold uppercase text-neutral-500">
                                    <tr>
                                        <th className="px-4 py-3">Jenis</th>
                                        <th className="px-4 py-3">Subjek</th>
                                        <th className="px-4 py-3">Terkait</th>
                                        <th className="px-4 py-3">Waktu</th>
                                        <th className="px-4 py-3">Penanggung Jawab</th>
                                        <th className="px-4 py-3">Status</th>
                                        <th className="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {activities.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-4 py-8 text-center text-neutral-500">
                                                Belum ada catatan aktivitas. Klik tombol "Catat Aktivitas Baru" untuk menambahkan.
                                            </td>
                                        </tr>
                                    ) : (
                                        activities.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/50">
                                                <td className="px-4 py-3">
                                                    <div className="flex items-center gap-2 font-medium capitalize text-neutral-900 dark:text-neutral-100">
                                                        {getTypeIcon(item.activity_type)}
                                                        <span>{item.activity_type.replace('_', ' ')}</span>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 font-semibold text-neutral-900 dark:text-neutral-100">
                                                    {item.subject}
                                                </td>
                                                <td className="px-4 py-3 text-neutral-600 dark:text-neutral-400">
                                                    {item.customer ? (
                                                        <span className="text-sky-600 font-medium">{item.customer.name}</span>
                                                    ) : item.lead ? (
                                                        <span className="text-emerald-600 font-medium">{item.lead.company_name || `${item.lead.first_name} ${item.lead.last_name}`}</span>
                                                    ) : item.opportunity ? (
                                                        <span className="text-amber-600 font-medium">{item.opportunity.title}</span>
                                                    ) : (
                                                        <span className="text-neutral-400">-</span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-neutral-500 whitespace-nowrap">
                                                    {new Date(item.start_time).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' })}
                                                </td>
                                                <td className="px-4 py-3 text-neutral-700 dark:text-neutral-300">
                                                    {item.assigned_to?.name || '-'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium capitalize ${
                                                        item.status === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                                                        item.status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-neutral-100 text-neutral-800'
                                                    }`}>
                                                        {item.status}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <Link href={`/crm/activities/${item.id}`} className="text-sky-600 hover:text-sky-800 font-medium inline-flex items-center gap-1">
                                                        <Eye className="h-4 w-4" /> Detail
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {/* Create Modal */}
                {showCreateModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <div className="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-neutral-900">
                            <h2 className="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4">Catat Aktivitas Baru</h2>
                            <form onSubmit={handleCreate} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Jenis Aktivitas</label>
                                    <select
                                        value={data.activity_type}
                                        onChange={(e) => setData('activity_type', e.target.value)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    >
                                        {activityTypes.map((t) => (
                                            <option key={t.value} value={t.value}>{t.label}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Subjek</label>
                                    <input
                                        type="text"
                                        required
                                        placeholder="Contoh: Telepon Follow up Penawaran Kayu Jati"
                                        value={data.subject}
                                        onChange={(e) => setData('subject', e.target.value)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                    {errors.subject && <span className="text-xs text-rose-500">{errors.subject}</span>}
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Catatan / Detail</label>
                                    <textarea
                                        rows={3}
                                        placeholder="Tuliskan ringkasan percakapan atau hasil rapat..."
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Waktu Mulai</label>
                                        <input
                                            type="datetime-local"
                                            required
                                            value={data.start_time}
                                            onChange={(e) => setData('start_time', e.target.value)}
                                            className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Penanggung Jawab</label>
                                        <select
                                            value={data.assigned_to}
                                            onChange={(e) => setData('assigned_to', e.target.value)}
                                            className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                        >
                                            <option value="">Pilih Penanggung Jawab</option>
                                            {users.map((u) => (
                                                <option key={u.id} value={u.id}>{u.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>
                                <div className="flex justify-end gap-2 pt-4">
                                    <Button type="button" variant="outline" onClick={() => setShowCreateModal(false)}>
                                        Batal
                                    </Button>
                                    <Button type="submit" disabled={processing} className="bg-sky-600 hover:bg-sky-700 text-white">
                                        Simpan Aktivitas
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}
