import { Head, useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { Calendar as CalendarIcon, Plus, ChevronLeft, ChevronRight, MapPin, Clock, User, Filter } from 'lucide-react';
import { useState, useEffect } from 'react';

interface CalendarEventItem {
    id: number;
    title: string;
    description: string | null;
    event_type: string;
    start_at: string;
    end_at: string;
    location: string | null;
    color: string | null;
    status: string;
    organizer?: { id: number; name: string };
    customer?: { id: number; name: string };
}

interface Props {
    eventTypes: Array<{ value: string; label: string; color: string }>;
    users: Array<{ id: number; name: string }>;
}

export default function CalendarIndex({ eventTypes, users }: Props) {
    const { can } = usePermission();
    const [currentDate, setCurrentDate] = useState(new Date());
    const [events, setEvents] = useState<CalendarEventItem[]>([]);
    const [loading, setLoading] = useState(false);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [selectedDateStr, setSelectedDateStr] = useState('');

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const fetchEvents = async () => {
        setLoading(true);
        const start = new Date(year, month, 1).toISOString();
        const end = new Date(year, month + 1, 0).toISOString();

        try {
            const res = await fetch(`/crm/calendar/events?start=${start}&end=${end}`);
            const data = await res.json();
            setEvents(data);
        } catch (e) {
            console.error('Failed to load events:', e);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchEvents();
    }, [year, month]);

    const { data, setData, post, processing, reset } = useForm({
        title: '',
        description: '',
        event_type: 'meeting',
        start_at: '',
        end_at: '',
        location: '',
        color: '#0284c7',
    });

    const handleCreate = (e: React.FormEvent) => {
        e.preventDefault();
        post('/crm/calendar', {
            onSuccess: () => {
                setShowCreateModal(false);
                reset();
                fetchEvents();
            },
        });
    };

    const nextMonth = () => setCurrentDate(new Date(year, month + 1, 1));
    const prevMonth = () => setCurrentDate(new Date(year, month - 1, 1));

    // Generate month grid
    const firstDayOfMonth = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysArray = [];

    for (let i = 0; i < firstDayOfMonth; i++) {
        daysArray.push(null);
    }
    for (let d = 1; d <= daysInMonth; d++) {
        daysArray.push(d);
    }

    const getEventsForDay = (day: number) => {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        return events.filter(e => e.start_at.startsWith(dateStr));
    };

    const handleCellClick = (day: number) => {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        setSelectedDateStr(dateStr);
        setData({
            ...data,
            start_at: `${dateStr}T09:00`,
            end_at: `${dateStr}T10:00`,
        });
        setShowCreateModal(true);
    };

    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    return (
        <>
            <Head title="Kalender Sales (Calendar)" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 flex items-center gap-2">
                            <CalendarIcon className="h-6 w-6 text-sky-600" />
                            Kalender Penjualan & Rapat
                        </h1>
                        <p className="text-sm text-neutral-500">
                            Jadwal pertemuan pelanggan, kunjungan lapangan, dan batas waktu pengerjaan.
                        </p>
                    </div>

                    {can('create-calendar-events') && (
                        <Button onClick={() => setShowCreateModal(true)} className="bg-sky-600 hover:bg-sky-700 text-white">
                            <Plus className="mr-2 h-4 w-4" /> Tambah Agenda
                        </Button>
                    )}
                </div>

                {/* Calendar Controls */}
                <Card className="border-neutral-200 dark:border-neutral-800">
                    <CardContent className="p-4 flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <h2 className="text-xl font-bold text-neutral-900 dark:text-neutral-50">
                                {monthNames[month]} {year}
                            </h2>
                            <div className="flex items-center gap-1">
                                <Button variant="outline" size="sm" onClick={prevMonth}>
                                    <ChevronLeft className="h-4 w-4" />
                                </Button>
                                <Button variant="outline" size="sm" onClick={nextMonth}>
                                    <ChevronRight className="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            {eventTypes.map(t => (
                                <div key={t.value} className="flex items-center gap-1 text-xs">
                                    <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: t.color }}></span>
                                    <span className="text-neutral-600 dark:text-neutral-400">{t.label}</span>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Monthly Calendar Grid */}
                <Card className="border-neutral-200 dark:border-neutral-800 flex-1">
                    <CardContent className="p-4">
                        <div className="grid grid-cols-7 gap-1 text-center font-bold text-xs text-neutral-500 mb-2">
                            <div>Minggu</div>
                            <div>Senin</div>
                            <div>Selasa</div>
                            <div>Rabu</div>
                            <div>Kamis</div>
                            <div>Jumat</div>
                            <div>Sabtu</div>
                        </div>

                        <div className="grid grid-cols-7 gap-1 auto-rows-fr">
                            {daysArray.map((day, idx) => {
                                if (day === null) {
                                    return <div key={`empty-${idx}`} className="h-28 bg-neutral-50/50 dark:bg-neutral-900/30 rounded-lg"></div>;
                                }

                                const dayEvents = getEventsForDay(day);
                                const isToday = day === new Date().getDate() && month === new Date().getMonth() && year === new Date().getFullYear();

                                return (
                                    <div
                                        key={`day-${day}`}
                                        onClick={() => handleCellClick(day)}
                                        className={`h-28 p-1.5 border rounded-lg overflow-y-auto cursor-pointer transition hover:border-sky-500 ${
                                            isToday ? 'bg-sky-50/40 border-sky-400 dark:bg-sky-950/20' : 'border-neutral-200 dark:border-neutral-800'
                                        }`}
                                    >
                                        <div className="flex justify-between items-center mb-1">
                                            <span className={`text-xs font-bold ${isToday ? 'bg-sky-600 text-white h-5 w-5 rounded-full flex items-center justify-center' : 'text-neutral-700 dark:text-neutral-300'}`}>
                                                {day}
                                            </span>
                                        </div>

                                        <div className="space-y-1">
                                            {dayEvents.map(e => (
                                                <div
                                                    key={e.id}
                                                    className="px-1.5 py-0.5 text-[11px] font-medium rounded truncate text-white"
                                                    style={{ backgroundColor: e.color || '#0284c7' }}
                                                    title={e.title}
                                                >
                                                    {e.title}
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </CardContent>
                </Card>

                {/* Create Modal */}
                {showCreateModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <div className="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-neutral-900">
                            <h2 className="text-lg font-bold text-neutral-900 dark:text-neutral-50 mb-4">Tambah Agenda Kalender</h2>
                            <form onSubmit={handleCreate} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Judul Agenda</label>
                                    <input
                                        type="text"
                                        required
                                        placeholder="Contoh: Rapat Presentasi Sampel Kayu Jati"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Kategori Agenda</label>
                                        <select
                                            value={data.event_type}
                                            onChange={(e) => setData('event_type', e.target.value)}
                                            className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                        >
                                            {eventTypes.map(t => (
                                                <option key={t.value} value={t.value}>{t.label}</option>
                                            ))}
                                        </select>
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Lokasi</label>
                                        <input
                                            type="text"
                                            placeholder="Gedung / Zoom Link"
                                            value={data.location}
                                            onChange={(e) => setData('location', e.target.value)}
                                            className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                        />
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Waktu Mulai</label>
                                        <input
                                            type="datetime-local"
                                            required
                                            value={data.start_at}
                                            onChange={(e) => setData('start_at', e.target.value)}
                                            className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Waktu Selesai</label>
                                        <input
                                            type="datetime-local"
                                            required
                                            value={data.end_at}
                                            onChange={(e) => setData('end_at', e.target.value)}
                                            className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">Deskripsi / Agenda</label>
                                    <textarea
                                        rows={2}
                                        placeholder="Poin bahasan rapat..."
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        className="w-full border rounded-lg p-2 text-sm border-neutral-200 dark:border-neutral-800"
                                    />
                                </div>
                                <div className="flex justify-end gap-2 pt-4">
                                    <Button type="button" variant="outline" onClick={() => setShowCreateModal(false)}>
                                        Batal
                                    </Button>
                                    <Button type="submit" disabled={processing} className="bg-sky-600 hover:bg-sky-700 text-white">
                                        Simpan Agenda
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
