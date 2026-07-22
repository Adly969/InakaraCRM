import { Head, Link, useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { CheckSquare, Plus, Search, Eye, Clock, AlertTriangle, CheckCircle, Calendar, User, Filter } from 'lucide-react';
import { useState } from 'react';

interface TaskItem {
    id: number;
    title: string;
    description: string | null;
    status: string;
    priority: string;
    due_date: string;
    due_time: string | null;
    assigned_to?: { id: number; name: string };
    customer?: { id: number; name: string };
    opportunity?: { id: number; title: string };
    checklists: Array<{ id: number; label: string; is_completed: boolean }>;
}

interface Props {
    tasks: {
        data: TaskItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
    };
    filters: Record<string, string>;
    statuses: Array<{ value: string; label: string; color: string }>;
    priorities: Array<{ value: string; label: string; color: string }>;
    users: Array<{ id: number; name: string }>;
}

export default function TasksIndex({ tasks, filters, statuses, priorities, users }: Props) {
    const { can } = usePermission();
    const [searchQuery, setSearchQuery] = useState(filters.search || '');
    const [selectedStatus, setSelectedStatus] = useState(filters.status || 'all');
    const [showCreateModal, setShowCreateModal] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
        description: '',
        due_date: new Date().toISOString().slice(0, 10),
        priority: 'medium',
        assigned_to: '',
        checklists: [''],
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/crm/tasks', { search: searchQuery, status: selectedStatus === 'all' ? '' : selectedStatus }, { preserveState: true });
    };

    const handleCreate = (e: React.FormEvent) => {
        e.preventDefault();
        post('/crm/tasks', {
            onSuccess: () => {
                setShowCreateModal(false);
                reset();
            },
        });
    };

    const addChecklistField = () => {
        setData('checklists', [...data.checklists, '']);
    };

    const updateChecklistField = (index: number, val: string) => {
        const next = [...data.checklists];
        next[index] = val;
        setData('checklists', next);
    };

    return (
        <>
            <Head title="Tugas & Milestone Penjualan (Tasks)" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 flex items-center gap-2">
                            <CheckSquare className="h-6 w-6 text-sky-600" />
                            Manajemen Tugas (Tasks & Checklist)
                        </h1>
                        <p className="text-sm text-neutral-500">
                            Kelola tenggat waktu follow up, penyiapan penawaran, dan penugasan tim tim sales.
                        </p>
                    </div>

                    {can('create-tasks') && (
                        <Button onClick={() => setShowCreateModal(true)} className="bg-sky-600 hover:bg-sky-700 text-white">
                            <Plus className="mr-2 h-4 w-4" /> Buat Tugas Baru
                        </Button>
                    )}
                </div>

                {/* Metrics Cards */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <Card className="border-neutral-200 dark:border-neutral-800">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500">Total Tugas</p>
                                <p className="text-2xl font-bold text-neutral-900 dark:text-neutral-50">{tasks.total}</p>
                            </div>
                            <div className="p-2 bg-sky-50 rounded-lg text-sky-600 dark:bg-sky-950">
                                <CheckSquare className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-neutral-200 dark:border-neutral-800">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500">Pending</p>
                                <p className="text-2xl font-bold text-amber-600">{tasks.data.filter(t => t.status === 'pending').length}</p>
                            </div>
                            <div className="p-2 bg-amber-50 rounded-lg text-amber-600 dark:bg-amber-950">
                                <Clock className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-neutral-200 dark:border-neutral-800">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500">In Progress</p>
                                <p className="text-2xl font-bold text-blue-600">{tasks.data.filter(t => t.status === 'in_progress').length}</p>
                            </div>
                            <div className="p-2 bg-blue-50 rounded-lg text-blue-600 dark:bg-blue-950">
                                <Clock className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>
                    <Card className="border-neutral-200 dark:border-neutral-800">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-medium text-neutral-500">Selesai</p>
                                <p className="text-2xl font-bold text-emerald-600">{tasks.data.filter(t => t.status === 'completed').length}</p>
                            </div>
                            <div className="p-2 bg-emerald-50 rounded-lg text-emerald-600 dark:bg-emerald-950">
                                <CheckCircle className="h-5 w-5" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter Bar */}
                <Card className="border-neutral-200 dark:border-neutral-800">
                    <CardContent className="p-4">
                        <form onSubmit={handleSearch} className="flex flex-col gap-4 sm:flex-row sm:items-center justify-between">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" />
                                <input
                                    type="text"
                                    placeholder="Cari judul tugas..."
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    className="w-full pl-9 pr-4 py-2 text-sm border rounded-lg border-neutral-200 dark:border-neutral-800 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                />
                            </div>
                            <div className="flex items-center gap-2">
                                <Filter className="h-4 w-4 text-neutral-400" />
                                <select
                                    value={selectedStatus}
                                    onChange={(e) => {
                                        setSelectedStatus(e.target.value);
                                        router.get('/crm/tasks', { search: searchQuery, status: e.target.value === 'all' ? '' : e.target.value }, { preserveState: true });
                                    }}
                                    className="px-3 py-2 text-sm border rounded-lg border-neutral-200 dark:border-neutral-800 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                >
                                    <option value="all">Semua Status</option>
                                    {statuses.map((s) => (
                                        <option key={s.value} value={s.value}>{s.label}</option>
                                    ))}
                                </select>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Tasks Table */}
                <Card className="border-neutral-200 dark:border-neutral-800">
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-neutral-50 dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 text-xs font-semibold uppercase text-neutral-500">
                                    <tr>
                                        <th className="px-4 py-3">Judul Tugas</th>
                                        <th className="px-4 py-3">Prioritas</th>
                                        <th className="px-4 py-3">Tenggat Waktu</th>
                                        <th className="px-4 py-3">Penanggung Jawab</th>
                                        <th className="px-4 py-3">Checklist</th>
                                        <th className="px-4 py-3">Status</th>
                                        <th className="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                    {tasks.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="px-4 py-8 text-center text-neutral-500">
                                                Belum ada tugas. Klik tombol "Buat Tugas Baru" untuk menambahkan.
                                            </td>
                                        </tr>
                                    ) : (
                                        tasks.data.map((task) => {
                                            const completedChecklists = task.checklists.filter(c => c.is_completed).length;
                                            return (
                                                <tr key={task.id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/50">
                                                    <td className="px-4 py-3 font-semibold text-neutral-900 dark:text-neutral-100">
                                                        {task.title}
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <span className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium uppercase ${
                                                            task.priority === 'urgent' ? 'bg-rose-100 text-rose-800' :
                                                            task.priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-neutral-100 text-neutral-800'
                                                        }`}>
                                                            {task.priority}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-3 text-neutral-600 dark:text-neutral-400 whitespace-nowrap">
                                                        {new Date(task.due_date).toLocaleDateString('id-ID', { dateStyle: 'medium' })}
                                                    </td>
                                                    <td className="px-4 py-3 text-neutral-700 dark:text-neutral-300">
                                                        {task.assigned_to?.name || '-'}
                                                    </td>
                                                    <td className="px-4 py-3 text-xs text-neutral-500">
                                                        {task.checklists.length > 0 ? (
                                                            <span>{completedChecklists}/{task.checklists.length} selesai</span>
                                                        ) : '-'}
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <span className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium capitalize ${
                                                            task.status === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                                                            task.status === 'in_progress' ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800'
                                                        }`}>
                                                            {task.status.replace('_', ' ')}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-3 text-right">
                                                        <Link href={`/crm/tasks/${task.id}`} className="text-sky-600 hover:text-sky-800 font-medium inline-flex items-center gap-1">
                                                            <Eye className="h-4 w-4" /> Detail
                                                        </Link>
                                                    </td>
                                                </tr>
                                            );
                                        })
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {/* Create Modal */}
                {showCreateModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <div className="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-neutral-900 max-h-[90vh] overflow-y-auto">
                            <h2 className="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4">Buat Tugas Baru</h2>
                            <form onSubmit={handleCreate} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Judul Tugas</label>
                                    <input
                                        type="text"
                                        required
                                        placeholder="Contoh: Menyiapkan Dokumen Penawaran Kayu Jati"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Deskripsi</label>
                                    <textarea
                                        rows={2}
                                        placeholder="Instruksi pengerjaan..."
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Tenggat Waktu (Due Date)</label>
                                        <input
                                            type="date"
                                            required
                                            value={data.due_date}
                                            onChange={(e) => setData('due_date', e.target.value)}
                                            className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Prioritas</label>
                                        <select
                                            value={data.priority}
                                            onChange={(e) => setData('priority', e.target.value)}
                                            className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                        >
                                            {priorities.map((p) => (
                                                <option key={p.value} value={p.value}>{p.label}</option>
                                            ))}
                                        </select>
                                    </div>
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

                                {/* Checklists Builder */}
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Checklist Pengerjaan</label>
                                    <div className="space-y-2">
                                        {data.checklists.map((item, idx) => (
                                            <input
                                                key={idx}
                                                type="text"
                                                placeholder={`Langkah ${idx + 1}...`}
                                                value={item}
                                                onChange={(e) => updateChecklistField(idx, e.target.value)}
                                                className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                            />
                                        ))}
                                    </div>
                                    <button
                                        type="button"
                                        onClick={addChecklistField}
                                        className="mt-2 text-xs font-semibold text-sky-600 hover:underline"
                                    >
                                        + Tambah Item Checklist
                                    </button>
                                </div>

                                <div className="flex justify-end gap-2 pt-4 border-t">
                                    <Button type="button" variant="outline" onClick={() => setShowCreateModal(false)}>
                                        Batal
                                    </Button>
                                    <Button type="submit" disabled={processing} className="bg-sky-600 hover:bg-sky-700 text-white">
                                        Simpan Tugas
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
