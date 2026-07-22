import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CheckSquare, ArrowLeft, CheckCircle2, Clock, User, Calendar } from 'lucide-react';

interface Checklist {
    id: number;
    label: string;
    is_completed: boolean;
    completed_at: string | null;
    completedBy?: { id: number; name: string };
}

interface Props {
    task: {
        id: number;
        title: string;
        description: string | null;
        status: string;
        priority: string;
        due_date: string;
        assigned_to?: { id: number; name: string };
        creator?: { id: number; name: string };
        customer?: { id: number; name: string };
        opportunity?: { id: number; title: string };
        checklists: Checklist[];
    };
    statuses: Array<{ value: string; label: string }>;
    priorities: Array<{ value: string; label: string }>;
}

export default function TaskShow({ task, statuses, priorities }: Props) {
    const handleToggleChecklist = (checklistId: number) => {
        router.patch(`/crm/tasks/checklists/${checklistId}`);
    };

    const handleStatusChange = (newStatus: string) => {
        router.patch(`/crm/tasks/${task.id}/status`, { status: newStatus });
    };

    return (
        <>
            <Head title={`Tugas: ${task.title}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div>
                    <Link href="/crm/tasks" className="inline-flex items-center gap-1 text-xs text-sky-600 hover:underline mb-2">
                        <ArrowLeft className="h-3 w-3" /> Kembali ke Daftar Tugas
                    </Link>
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 flex items-center gap-2">
                            <CheckSquare className="h-6 w-6 text-sky-600" />
                            {task.title}
                        </h1>
                        <span className={`px-3 py-1 rounded-full text-xs font-semibold uppercase ${
                            task.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                        }`}>
                            {task.status.replace('_', ' ')}
                        </span>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        {/* Task Details */}
                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardHeader>
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-50">Detail Tugas</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {task.description && (
                                    <div>
                                        <span className="text-xs text-neutral-400 block mb-1">Deskripsi</span>
                                        <p className="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{task.description}</p>
                                    </div>
                                )}

                                <div className="grid grid-cols-2 gap-4 text-sm pt-4 border-t border-neutral-100 dark:border-neutral-800">
                                    <div>
                                        <span className="text-xs text-neutral-400 block">Tenggat Waktu</span>
                                        <span className="font-semibold text-neutral-900 dark:text-neutral-100">{new Date(task.due_date).toLocaleDateString('id-ID', { dateStyle: 'full' })}</span>
                                    </div>
                                    <div>
                                        <span className="text-xs text-neutral-400 block">Prioritas</span>
                                        <span className="font-semibold uppercase text-rose-600">{task.priority}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Checklists */}
                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardHeader>
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-50">
                                    Checklist Pengerjaan ({task.checklists.filter(c => c.is_completed).length}/{task.checklists.length})
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {task.checklists.length === 0 ? (
                                    <p className="text-xs text-neutral-400">Tidak ada checklist pengerjaan.</p>
                                ) : (
                                    <div className="space-y-2">
                                        {task.checklists.map((item) => (
                                            <div
                                                key={item.id}
                                                onClick={() => handleToggleChecklist(item.id)}
                                                className="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-900 cursor-pointer"
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={item.is_completed}
                                                    onChange={() => {}}
                                                    className="h-4 w-4 rounded border-neutral-300 text-sky-600 focus:ring-sky-500"
                                                />
                                                <span className={`text-sm ${item.is_completed ? 'line-through text-neutral-400' : 'text-neutral-900 dark:text-neutral-100 font-medium'}`}>
                                                    {item.label}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        {/* Status Toggle Card */}
                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardHeader>
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-50">Ubah Status</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                {statuses.map((s) => (
                                    <Button
                                        key={s.value}
                                        variant={task.status === s.value ? 'default' : 'outline'}
                                        onClick={() => handleStatusChange(s.value)}
                                        className={`w-full justify-start ${task.status === s.value ? 'bg-sky-600 text-white' : ''}`}
                                    >
                                        {s.label}
                                    </Button>
                                ))}
                            </CardContent>
                        </Card>

                        {/* Owner Card */}
                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardHeader>
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-50">Informasi Penugasan</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div>
                                    <span className="text-xs text-neutral-400 block">Penanggung Jawab</span>
                                    <span className="font-semibold text-neutral-900 dark:text-neutral-100">{task.assigned_to?.name || '-'}</span>
                                </div>
                                <div>
                                    <span className="text-xs text-neutral-400 block">Dibuat Oleh</span>
                                    <span className="font-semibold text-neutral-900 dark:text-neutral-100">{task.creator?.name || '-'}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
