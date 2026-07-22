import { useState, useEffect } from 'react';
import { Activity, CheckSquare, Calendar, FileText, Clock, User } from 'lucide-react';

interface TimelineItem {
    id: string;
    type: 'activity' | 'task' | 'event' | 'document';
    title: string;
    description: string | null;
    activity_type?: string;
    priority?: string;
    status?: string;
    event_type?: string;
    document_type?: string;
    file_name?: string;
    user_name: string;
    timestamp: string;
}

interface Props {
    entityType: 'customer' | 'lead' | 'opportunity';
    entityId: number;
}

export function CrmTimeline({ entityType, entityId }: Props) {
    const [items, setItems] = useState<TimelineItem[]>([]);
    const [loading, setLoading] = useState(true);

    const fetchTimeline = async () => {
        setLoading(true);
        try {
            const res = await fetch(`/api/v1/timeline/${entityType}/${entityId}`);
            const data = await res.json();
            setItems(data);
        } catch (err) {
            console.error('Failed to load CRM timeline:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchTimeline();
    }, [entityType, entityId]);

    const getIcon = (item: TimelineItem) => {
        switch (item.type) {
            case 'activity': return <Activity className="h-4 w-4 text-sky-600" />;
            case 'task': return <CheckSquare className="h-4 w-4 text-emerald-600" />;
            case 'event': return <Calendar className="h-4 w-4 text-amber-600" />;
            case 'document': return <FileText className="h-4 w-4 text-purple-600" />;
        }
    };

    if (loading) {
        return <div className="p-4 text-xs text-neutral-400">Memuat riwayat aktivitas...</div>;
    }

    if (items.length === 0) {
        return <div className="p-4 text-xs text-neutral-400">Belum ada riwayat aktivitas untuk entitas ini.</div>;
    }

    return (
        <div className="space-y-4">
            <h3 className="text-sm font-bold text-neutral-900 dark:text-neutral-50 mb-2">Riwayat Timeline CRM</h3>
            <div className="relative border-l border-neutral-200 dark:border-neutral-800 ml-3 space-y-4">
                {items.map((item) => (
                    <div key={item.id} className="relative pl-6">
                        <div className="absolute -left-2.5 top-0.5 h-5 w-5 rounded-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 flex items-center justify-center">
                            {getIcon(item)}
                        </div>
                        <div className="bg-neutral-50 dark:bg-neutral-900/50 p-3 rounded-lg border border-neutral-100 dark:border-neutral-800">
                            <div className="flex items-center justify-between mb-1">
                                <span className="font-semibold text-xs text-neutral-900 dark:text-neutral-100">{item.title}</span>
                                <span className="text-[10px] text-neutral-400">
                                    {new Date(item.timestamp).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' })}
                                </span>
                            </div>
                            {item.description && (
                                <p className="text-xs text-neutral-600 dark:text-neutral-400 mb-1">{item.description}</p>
                            )}
                            <div className="flex items-center gap-2 text-[10px] text-neutral-400">
                                <User className="h-3 w-3" />
                                <span>{item.user_name}</span>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
