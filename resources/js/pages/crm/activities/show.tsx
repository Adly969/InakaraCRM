import { Head, Link, useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Activity, ArrowLeft, CheckCircle2, MessageSquare, Paperclip, User, Calendar, Clock, MapPin, Tag } from 'lucide-react';
import { useState } from 'react';

interface Comment {
    id: number;
    body: string;
    created_at: string;
    user?: { id: number; name: string };
}

interface Attachment {
    id: number;
    file_name: string;
    file_path: string;
    file_size: number;
    mime_type: string;
    uploader?: { id: number; name: string };
}

interface Props {
    activity: {
        id: number;
        activity_type: string;
        subject: string;
        description: string | null;
        start_time: string;
        end_time: string | null;
        status: string;
        outcome: string | null;
        priority: string;
        location: string | null;
        duration_minutes: number | null;
        customer?: { id: number; name: string };
        lead?: { id: number; first_name: string; last_name: string; company_name: string };
        opportunity?: { id: number; title: string };
        assigned_to?: { id: number; name: string };
        creator?: { id: number; name: string };
        activity_comments: Comment[];
        attachments: Attachment[];
    };
    outcomes: Array<{ value: string; label: string; color: string }>;
}

export default function ActivityShow({ activity, outcomes }: Props) {
    const [commentBody, setCommentBody] = useState('');
    const [selectedOutcome, setSelectedOutcome] = useState(outcomes[0]?.value || 'interested');
    const [completionNotes, setCompletionNotes] = useState('');

    const handleAddComment = (e: React.FormEvent) => {
        e.preventDefault();
        if (!commentBody.trim()) return;

        router.post(`/crm/activities/${activity.id}/comments`, { body: commentBody }, {
            onSuccess: () => setCommentBody(''),
        });
    };

    const handleComplete = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(`/crm/activities/${activity.id}/complete`, {
            outcome: selectedOutcome,
            notes: completionNotes,
        });
    };

    return (
        <>
            <Head title={`Aktivitas: ${activity.subject}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div>
                    <Link href="/crm/activities" className="inline-flex items-center gap-1 text-xs text-sky-600 hover:underline mb-2">
                        <ArrowLeft className="h-3 w-3" /> Kembali ke Daftar Aktivitas
                    </Link>
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 flex items-center gap-2">
                            <Activity className="h-6 w-6 text-sky-600" />
                            {activity.subject}
                        </h1>
                        <span className={`px-3 py-1 rounded-full text-xs font-semibold uppercase ${
                            activity.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                        }`}>
                            {activity.status}
                        </span>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Left 2 columns: Main details, comments, attachments */}
                    <div className="space-y-6 lg:col-span-2">
                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardHeader>
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-50">Ringkasan Aktivitas</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span className="text-xs text-neutral-400 block">Jenis</span>
                                        <span className="font-semibold capitalize text-neutral-900 dark:text-neutral-100">{activity.activity_type.replace('_', ' ')}</span>
                                    </div>
                                    <div>
                                        <span className="text-xs text-neutral-400 block">Prioritas</span>
                                        <span className="font-semibold capitalize text-neutral-900 dark:text-neutral-100">{activity.priority}</span>
                                    </div>
                                    <div>
                                        <span className="text-xs text-neutral-400 block">Waktu Mulai</span>
                                        <span className="text-neutral-800 dark:text-neutral-200">{new Date(activity.start_time).toLocaleString('id-ID')}</span>
                                    </div>
                                    <div>
                                        <span className="text-xs text-neutral-400 block">Lokasi</span>
                                        <span className="text-neutral-800 dark:text-neutral-200">{activity.location || '-'}</span>
                                    </div>
                                </div>

                                {activity.description && (
                                    <div className="pt-4 border-t border-neutral-100 dark:border-neutral-800">
                                        <span className="text-xs text-neutral-400 block mb-1">Catatan / Rincian</span>
                                        <p className="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">{activity.description}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Comments Section */}
                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardHeader>
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-50 flex items-center gap-2">
                                    <MessageSquare className="h-4 w-4 text-sky-600" />
                                    Diskusi & Komentar Tim ({activity.activity_comments.length})
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-3">
                                    {activity.activity_comments.length === 0 ? (
                                        <p className="text-xs text-neutral-400">Belum ada komentar.</p>
                                    ) : (
                                        activity.activity_comments.map((comment) => (
                                            <div key={comment.id} className="p-3 bg-neutral-50 rounded-lg dark:bg-neutral-900 text-sm">
                                                <div className="flex justify-between items-center mb-1">
                                                    <span className="font-semibold text-neutral-900 dark:text-neutral-100">{comment.user?.name || 'Pengguna'}</span>
                                                    <span className="text-xs text-neutral-400">{new Date(comment.created_at).toLocaleString('id-ID')}</span>
                                                </div>
                                                <p className="text-neutral-700 dark:text-neutral-300">{comment.body}</p>
                                            </div>
                                        ))
                                    )}
                                </div>

                                <form onSubmit={handleAddComment} className="flex gap-2 pt-2">
                                    <input
                                        type="text"
                                        placeholder="Tulis komentar..."
                                        value={commentBody}
                                        onChange={(e) => setCommentBody(e.target.value)}
                                        className="flex-1 border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                    <Button type="submit" className="bg-sky-600 hover:bg-sky-700 text-white">
                                        Kirim
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Right column: Actions & Relationships */}
                    <div className="space-y-6">
                        {activity.status !== 'completed' && (
                            <Card className="border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/20">
                                <CardHeader>
                                    <CardTitle className="text-base font-bold text-emerald-900 dark:text-emerald-400 flex items-center gap-2">
                                        <CheckCircle2 className="h-5 w-5 text-emerald-600" />
                                        Tandai Selesai & Hasil (Outcome)
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <form onSubmit={handleComplete} className="space-y-4">
                                        <div>
                                            <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Hasil Aktivitas (Outcome)</label>
                                            <select
                                                value={selectedOutcome}
                                                onChange={(e) => setSelectedOutcome(e.target.value)}
                                                className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                            >
                                                {outcomes.map((o) => (
                                                    <option key={o.value} value={o.value}>{o.label}</option>
                                                ))}
                                            </select>
                                        </div>
                                        <div>
                                            <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Catatan Penyelesaian</label>
                                            <textarea
                                                rows={2}
                                                placeholder="Hasil akhir yang disepakati..."
                                                value={completionNotes}
                                                onChange={(e) => setCompletionNotes(e.target.value)}
                                                className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                            />
                                        </div>
                                        <Button type="submit" className="w-full bg-emerald-600 hover:bg-emerald-700 text-white">
                                            Selesaikan Aktivitas
                                        </Button>
                                    </form>
                                </CardContent>
                            </Card>
                        )}

                        <Card className="border-neutral-200 dark:border-neutral-800">
                            <CardHeader>
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-50">Konteks Terkait</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                {activity.customer && (
                                    <div>
                                        <span className="text-xs text-neutral-400 block">Pelanggan</span>
                                        <Link href={`/customers/${activity.customer.id}`} className="font-semibold text-sky-600 hover:underline">
                                            {activity.customer.name}
                                        </Link>
                                    </div>
                                )}
                                {activity.lead && (
                                    <div>
                                        <span className="text-xs text-neutral-400 block">Prospek (Lead)</span>
                                        <Link href={`/leads/${activity.lead.id}`} className="font-semibold text-emerald-600 hover:underline">
                                            {activity.lead.company_name || `${activity.lead.first_name} ${activity.lead.last_name}`}
                                        </Link>
                                    </div>
                                )}
                                {activity.opportunity && (
                                    <div>
                                        <span className="text-xs text-neutral-400 block">Peluang (Opportunity)</span>
                                        <Link href={`/opportunities/${activity.opportunity.id}`} className="font-semibold text-amber-600 hover:underline">
                                            {activity.opportunity.title}
                                        </Link>
                                    </div>
                                )}
                                <div className="pt-2 border-t border-neutral-100 dark:border-neutral-800">
                                    <span className="text-xs text-neutral-400 block">Penanggung Jawab</span>
                                    <span className="font-semibold text-neutral-800 dark:text-neutral-200">{activity.assigned_to?.name || '-'}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
