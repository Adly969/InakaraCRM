import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Activity, Box, Database, ShieldAlert, ArrowLeft, TrendingUp, RefreshCw, BarChart2 } from 'lucide-react';
import { ResponsiveContainer, AreaChart, Area, XAxis, YAxis, Tooltip, CartesianGrid, BarChart, Bar } from 'recharts';

interface Warehouse {
    id: number;
    code: string;
    name: string;
    type: string;
    status: string;
    locations_count: number;
}

interface Props {
    warehouses: {
        data: Warehouse[];
    };
    stats: {
        total_tasks: number;
        total_locations: number;
    };
}

const wmsMovementData = [
    { day: 'Sen', inbound: 42, outbound: 38 },
    { day: 'Sel', inbound: 55, outbound: 49 },
    { day: 'Rab', inbound: 68, outbound: 62 },
    { day: 'Kam', inbound: 60, outbound: 71 },
    { day: 'Jum', inbound: 85, outbound: 78 },
    { day: 'Sab', inbound: 40, outbound: 35 },
    { day: 'Min', inbound: 25, outbound: 20 },
];

export default function WmsDashboard({ warehouses, stats }: Props) {
    return (
        <>
            <Head title="WMS Platform Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header Banner */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-neutral-200 dark:border-neutral-800 pb-5">
                    <div className="flex items-center gap-3">
                        <Button variant="outline" size="icon" asChild className="h-9 w-9 border-neutral-300 dark:border-neutral-700 rounded-xl">
                            <Link href="/">
                                <ArrowLeft className="h-4 w-4 text-neutral-600 dark:text-neutral-300" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Telemetri & Dashboard Pergudangan (WMS Workspace)
                            </h1>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Monitoring tata letak rak fisik gudang, alokasi FIFO/FEFO, dan pergerakan perputaran stok barang.
                            </p>
                        </div>
                    </div>
                    <Button variant="outline" className="border-neutral-300 dark:border-neutral-700 font-semibold rounded-xl">
                        <RefreshCw className="mr-2 h-4 w-4 text-sky-500" /> Refresh Telemetri
                    </Button>
                </div>

                {/* Metrics Cards with Trend Badges */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xs rounded-2xl">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 pt-5 px-6">
                            <CardTitle className="text-xs font-bold text-neutral-500 uppercase tracking-wider">Pabrik & Gudang Aktif</CardTitle>
                            <div className="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/60">
                                <Box className="h-4 w-4" />
                            </div>
                        </CardHeader>
                        <CardContent className="px-6 pb-5">
                            <div className="text-3xl font-black text-neutral-900 dark:text-neutral-50">{warehouses.data.length} Plant</div>
                            <span className="text-xs text-emerald-600 dark:text-emerald-400 font-bold inline-flex items-center mt-2">
                                <TrendingUp className="mr-1 h-3 w-3" /> +1 Plant Baru
                            </span>
                        </CardContent>
                    </Card>

                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xs rounded-2xl">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 pt-5 px-6">
                            <CardTitle className="text-xs font-bold text-neutral-500 uppercase tracking-wider">Total Slot Rak (Bin Nodes)</CardTitle>
                            <div className="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60">
                                <Database className="h-4 w-4" />
                            </div>
                        </CardHeader>
                        <CardContent className="px-6 pb-5">
                            <div className="text-3xl font-black text-neutral-900 dark:text-neutral-50">{stats.total_locations} Bins</div>
                            <span className="text-xs text-emerald-600 dark:text-emerald-400 font-bold inline-flex items-center mt-2">
                                <TrendingUp className="mr-1 h-3 w-3" /> 92% Kapasitas Terisi
                            </span>
                        </CardContent>
                    </Card>

                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xs rounded-2xl">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 pt-5 px-6">
                            <CardTitle className="text-xs font-bold text-neutral-500 uppercase tracking-wider">Tugas Wave Picking (Pending)</CardTitle>
                            <div className="p-2.5 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800/60">
                                <Activity className="h-4 w-4" />
                            </div>
                        </CardHeader>
                        <CardContent className="px-6 pb-5">
                            <div className="text-3xl font-black text-neutral-900 dark:text-neutral-50">{stats.total_tasks} Tugas</div>
                            <span className="text-xs text-sky-600 dark:text-sky-400 font-bold inline-flex items-center mt-2">
                                Dalam Proses Pengambilan
                            </span>
                        </CardContent>
                    </Card>

                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xs rounded-2xl">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 pt-5 px-6">
                            <CardTitle className="text-xs font-bold text-neutral-500 uppercase tracking-wider">Status Outbox SAGA</CardTitle>
                            <div className="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/60">
                                <ShieldAlert className="h-4 w-4" />
                            </div>
                        </CardHeader>
                        <CardContent className="px-6 pb-5">
                            <div className="text-3xl font-black text-emerald-600 dark:text-emerald-400">0 Failure</div>
                            <span className="text-xs text-emerald-600 dark:text-emerald-400 font-bold inline-flex items-center mt-2">
                                Antrean Outbox Bersih & Normal
                            </span>
                        </CardContent>
                    </Card>
                </div>

                {/* GRAPH SECTION: Movement Trend Chart */}
                <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xs rounded-2xl overflow-hidden">
                    <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 py-4 px-6 flex flex-row items-center justify-between">
                        <div>
                            <div className="flex items-center gap-2">
                                <BarChart2 className="h-5 w-5 text-sky-600 dark:text-sky-400" />
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">
                                    Grafik Pergerakan Barang Masuk vs Keluar (Mingguan)
                                </CardTitle>
                            </div>
                            <CardDescription className="text-xs text-neutral-500 mt-0.5">
                                Perbandingan volume Inbound Goods Receipt vs Outbound Goods Issue.
                            </CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent className="p-6">
                        <div className="h-72 w-full">
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart data={wmsMovementData} margin={{ top: 10, right: 10, left: 10, bottom: 0 }}>
                                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e5e7eb" opacity={0.5} />
                                    <XAxis dataKey="day" tickLine={false} axisLine={false} tick={{ fill: '#6b7280', fontSize: 12, fontWeight: 600 }} />
                                    <YAxis tickLine={false} axisLine={false} tick={{ fill: '#6b7280', fontSize: 11 }} />
                                    <Tooltip 
                                        contentStyle={{ 
                                            backgroundColor: '#171717', 
                                            borderColor: '#262626', 
                                            borderRadius: '12px',
                                            color: '#fff' 
                                        }}
                                    />
                                    <Bar dataKey="inbound" name="Barang Masuk (Inbound)" fill="#10b981" radius={[6, 6, 0, 0]} />
                                    <Bar dataKey="outbound" name="Barang Keluar (Outbound)" fill="#0284c7" radius={[6, 6, 0, 0]} />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </CardContent>
                </Card>

                {/* Warehouse Table */}
                <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xs rounded-2xl overflow-hidden">
                    <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 p-5">
                        <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">Jaringan Topologi Gudang Terdaftar</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <table className="w-full text-sm">
                            <thead className="border-b border-neutral-200 bg-neutral-100/70 dark:border-neutral-800 dark:bg-neutral-800/70 text-neutral-700 dark:text-neutral-300 font-bold uppercase text-[11px]">
                                <tr>
                                    <th className="h-10 px-6 text-left align-middle font-bold">Kode</th>
                                    <th className="h-10 px-6 text-left align-middle font-bold">Nama Gudang</th>
                                    <th className="h-10 px-6 text-left align-middle font-bold">Tipe Gudang</th>
                                    <th className="h-10 px-6 text-right align-middle font-bold">Total Slot Rak</th>
                                    <th className="h-10 px-6 text-right align-middle font-bold">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                {warehouses.data.map((wh) => (
                                    <tr key={wh.id} className="hover:bg-neutral-50/80 dark:hover:bg-neutral-850/50">
                                        <td className="px-6 py-3 font-mono font-bold text-neutral-900 dark:text-neutral-100">{wh.code}</td>
                                        <td className="px-6 py-3 font-medium text-neutral-800 dark:text-neutral-200">{wh.name}</td>
                                        <td className="px-6 py-3 uppercase text-xs font-bold text-neutral-500">{wh.type}</td>
                                        <td className="px-6 py-3 text-right font-mono font-bold">{wh.locations_count} Bins</td>
                                        <td className="px-6 py-3 text-right">
                                            <span className="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-950 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                {wh.status}
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

WmsDashboard.layout = {
    breadcrumbs: [
        {
            title: 'WMS Dashboard',
            href: '/wms/dashboard',
        },
    ],
};
