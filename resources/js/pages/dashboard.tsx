import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard } from '@/routes';
import type { Auth } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { useState } from 'react';
import { 
    Users, 
    TrendingUp, 
    TrendingDown,
    DollarSign, 
    ShoppingCart, 
    ArrowRight, 
    Layers, 
    FileText, 
    Activity,
    CheckCircle,
    BarChart3,
    Calendar,
    Sparkles,
    Filter,
    PieChart as PieIcon,
    ArrowUpRight,
    Target,
    Globe,
    ChevronRight,
    SlidersHorizontal,
    Award
} from 'lucide-react';
import { 
    ResponsiveContainer, 
    AreaChart, 
    Area, 
    PieChart,
    Pie,
    Cell,
    XAxis, 
    YAxis, 
    Tooltip, 
    CartesianGrid,
    Legend
} from 'recharts';

// Data Omset Komparatif (2026 vs 2025 YoY)
const revenueComparisonData = [
    { month: 'Jan', rev2026: 420000000, rev2025: 310000000, orders2026: 18, growth: '+35.5%' },
    { month: 'Feb', rev2026: 580000000, rev2025: 410000000, orders2026: 24, growth: '+41.4%' },
    { month: 'Mar', rev2026: 510000000, rev2025: 450000000, orders2026: 22, growth: '+13.3%' },
    { month: 'Apr', rev2026: 740000000, rev2025: 520000000, orders2026: 31, growth: '+42.3%' },
    { month: 'Mei', rev2026: 690000000, rev2025: 580000000, orders2026: 29, growth: '+18.9%' },
    { month: 'Jun', rev2026: 890000000, rev2025: 640000000, orders2026: 38, growth: '+39.0%' },
    { month: 'Jul', rev2026: 1249000000, rev2025: 850000000, orders2026: 48, growth: '+46.9%' },
];

// Mini Sparkline Data untuk 4 Metric Top Cards
const sparklineOmset = [{ v: 380 }, { v: 420 }, { v: 510 }, { v: 490 }, { v: 620 }, { v: 890 }, { v: 1249 }];
const sparklineLead = [{ v: 30 }, { v: 45 }, { v: 52 }, { v: 48 }, { v: 68 }, { v: 79 }, { v: 95 }];
const sparklineOrder = [{ v: 12 }, { v: 18 }, { v: 24 }, { v: 22 }, { v: 31 }, { v: 38 }, { v: 48 }];
const sparklineConversion = [{ v: 50 }, { v: 58 }, { v: 61 }, { v: 59 }, { v: 64 }, { v: 67 }, { v: 72 }];

// Sumber Prospek Lead (Donut Pie Chart)
const leadSourceData = [
    { name: 'Instagram Ads', value: 45, color: '#10b981', percentage: '31.7%' },
    { name: 'WhatsApp Direct', value: 38, color: '#0284c7', percentage: '26.8%' },
    { name: 'Website SEO', value: 29, color: '#f59e0b', percentage: '20.4%' },
    { name: 'Referral Klien', value: 18, color: '#6366f1', percentage: '12.7%' },
    { name: 'Pameran Bali', value: 12, color: '#ec4899', percentage: '8.4%' },
];

export default function Dashboard() {
    const { props } = usePage<any>();
    const user = (props.auth as Auth).user;
    const primaryRole = user.roles[0] || 'viewer';

    const dynamicData = props.dynamicData;
    const activeData = dynamicData?.comparisonData || revenueComparisonData;
    const activeLeadSources = dynamicData?.leadSourceData || leadSourceData;
    const currentYearStr = dynamicData?.currentYear || 2026;
    const currentMonthNameStr = dynamicData?.currentMonthName || 'Juli';

    const [granularity, setGranularity] = useState<'monthly' | 'quarterly'>('monthly');
    const [showBaseline, setShowBaseline] = useState<boolean>(true);

    // Format Currency Rupiah Ringkas
    const formatShortRupiah = (value: number) => {
        if (value >= 1000000000) {
            return `Rp ${(value / 1000000000).toFixed(2)}M`;
        }
        if (value >= 1000000) {
            return `Rp ${(value / 1000000).toFixed(0)}Jt`;
        }
        return `Rp ${value.toLocaleString('id-ID')}`;
    };

    // Advanced Glassmorphic Tooltip Komparatif
    const CustomComparativeTooltip = ({ active, payload, label }: any) => {
        if (active && payload && payload.length) {
            const dataPoint = payload[0].payload;
            const diff = dataPoint.rev2026 - dataPoint.rev2025;
            return (
                <div className="rounded-3xl border border-neutral-750 bg-neutral-900/95 p-4 shadow-2xl backdrop-blur-2xl text-white min-w-[260px]">
                    <div className="flex items-center justify-between border-b border-neutral-800 pb-2.5 mb-3">
                        <span className="text-xs font-bold uppercase tracking-wider text-neutral-400">Bulan {label} (YoY Growth)</span>
                        <span className="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">
                            {dataPoint.growth}
                        </span>
                    </div>
                    <div className="space-y-2.5 text-xs">
                        <div className="flex justify-between items-center bg-neutral-800/60 p-2.5 rounded-2xl border border-neutral-750">
                            <span className="flex items-center gap-2 text-neutral-200 font-semibold">
                                <span className="w-3 h-3 rounded-full bg-emerald-500 inline-block shadow-xs" />
                                Omset 2026:
                            </span>
                            <span className="font-mono font-black text-sm text-emerald-400">
                                {formatShortRupiah(dataPoint.rev2026)}
                            </span>
                        </div>
                        {showBaseline && (
                            <div className="flex justify-between items-center px-1">
                                <span className="flex items-center gap-2 text-neutral-400 font-medium">
                                    <span className="w-2.5 h-2.5 rounded-full bg-neutral-500 inline-block" />
                                    Omset 2025 (Baseline):
                                </span>
                                <span className="font-mono font-bold text-neutral-300">
                                    {formatShortRupiah(dataPoint.rev2025)}
                                </span>
                            </div>
                        )}
                        <div className="pt-2 border-t border-neutral-800 flex justify-between items-center text-[11px]">
                            <span className="text-neutral-400">Selisih Kenaikan:</span>
                            <span className="font-mono font-bold text-emerald-400">+ {formatShortRupiah(diff)}</span>
                        </div>
                    </div>
                </div>
            );
        }
        return null;
    };

    const getRoleShortcuts = (role: string) => {
        switch (role) {
            case 'gudang':
                return [
                    { title: 'Warehouse Plants', description: 'Kelola lokasi fisik & tata letak rak gudang.', href: '/warehouses', icon: Layers },
                    { title: 'Penerimaan Barang (Goods Receipts)', description: 'Catat resi barang masuk gudang.', href: '/goods-receipts', icon: ShoppingCart },
                    { title: 'Pengeluaran Barang (Goods Issues)', description: 'Keluarkan stok bahan baku produksi.', href: '/goods-issues', icon: FileText },
                    { title: 'Penyesuaian Stok (Stock Adjustment)', description: 'Catat revaluasi & variansi persediaan.', href: '/stock-adjustments', icon: Activity }
                ];
            case 'produksi':
                return [
                    { title: 'Perintah Produksi (Production Orders)', description: 'Terbitkan tiket kerja pesanan furnitur.', href: '/production-orders', icon: Cpu },
                    { title: 'Resep BOM Furnitur', description: 'Konfigurasi rumus kayu, veneer & finishing.', href: '/wms/dashboard', icon: Layers },
                    { title: 'Alur Routing Stasiun', description: 'Atur tahapan stasiun mesin kayu.', href: '/wms/dashboard', icon: FileText },
                    { title: 'Telemetri Gudang WMS', description: 'Pantau kapasitas rak secara real-time.', href: '/wms/dashboard', icon: Activity }
                ];
            case 'finance':
                return [
                    { title: 'Batch Jurnal Umum (GL)', description: 'Tinjau & posting entitas akuntansi.', href: '/finance/journals', icon: FileText },
                    { title: 'Integrasi Keuangan Outbox', description: 'Simulasi event trigger gateway keuangan.', href: '/finance/integration', icon: Layers },
                    { title: 'Piutang Pelanggan (Receivables)', description: 'Lacak kredit invoice Jatuh Tempo.', href: '/receivables', icon: Activity },
                    { title: 'Invoice Matching (5-Way)', description: 'Verifikasi faktur tagihan vendor.', href: '/invoices', icon: CheckCircle }
                ];
            default:
                return [
                    { title: 'Pipeline Prospek Sales', description: 'Kelola prospek lead dan konversi deal.', href: '/leads', icon: Users },
                    { title: 'Peluang Bisnis (Opportunities)', description: 'Lacak nilai est. proyek furnitur hotel.', href: '/opportunities', icon: Activity },
                    { title: 'Surat Penawaran (Quotations)', description: 'Buat proposal & harga penawaran.', href: '/quotations', icon: FileText },
                    { title: 'Pesanan Penjualan (Sales Orders)', description: 'Terbitkan SO resmi & alokasi PPN.', href: '/sales-orders', icon: ShoppingCart }
                ];
        }
    };

    const shortcuts = getRoleShortcuts(primaryRole);

    return (
        <>
            <Head title="Enterprise Cockpit Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Top Banner Studio */}
                <div className="relative overflow-hidden rounded-3xl bg-neutral-900 px-8 py-8 text-white dark:bg-neutral-950 shadow-2xl border border-neutral-800">
                    <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,var(--tw-gradient-stops))] from-emerald-950/40 via-neutral-900 to-neutral-950 opacity-90" />
                    <div className="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div>
                            <div className="flex items-center gap-3 mb-3">
                                <span className="inline-flex items-center rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 px-3.5 py-1 text-xs font-bold uppercase tracking-wider">
                                    <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse mr-2" />
                                    Live Sync CRM Mode {primaryRole}
                                </span>
                                <span className="text-xs text-neutral-400 font-semibold flex items-center gap-1">
                                    <Globe className="h-3.5 w-3.5 text-neutral-400" /> S4 Enterprise Ledger Active
                                </span>
                            </div>
                            <h1 className="text-3xl font-black tracking-tight text-white flex items-center gap-3">
                                Selamat Datang Kembali, {user.name}!
                                <Sparkles className="h-6 w-6 text-amber-400" />
                            </h1>
                            <p className="mt-2 text-neutral-400 max-w-2xl text-sm leading-relaxed font-medium">
                                Cockpit InakaraCRM: Analisis grafik ombak omset komparatif YoY (2026 vs 2025), akuisisi lead, dan alur transaksi SAGA.
                            </p>
                        </div>
                        <div className="flex items-center gap-3">
                            <Button asChild className="bg-white hover:bg-neutral-100 text-neutral-950 font-extrabold shadow-lg rounded-2xl px-5 py-6">
                                <Link href="/sales-orders/create">
                                    + Buat Pesanan Baru <ArrowUpRight className="ml-1.5 h-4 w-4 text-emerald-600" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>

                {/* 4 TOP METRIC CARDS WITH EMBEDDED MINI SPARKLINE CHARTS */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    {/* Card 1: Omset */}
                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/90 shadow-xs rounded-3xl overflow-hidden hover:shadow-lg transition-all relative">
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between mb-3">
                                <span className="text-xs font-bold text-neutral-500 uppercase tracking-wider">Total Omset Pendapatan</span>
                                <div className="p-2 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60">
                                    <DollarSign className="h-4 w-4" />
                                </div>
                            </div>
                            <div className="text-2xl font-black text-neutral-900 dark:text-neutral-50 tracking-tight">
                                {dynamicData?.totalRevenue ? formatShortRupiah(dynamicData.totalRevenue) : 'Rp 0'}
                            </div>
                            <div className="flex items-center justify-between mt-2">
                                <span className="inline-flex items-center text-xs font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-950/80 px-2 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                    <TrendingUp className="mr-1 h-3 w-3" /> Real Time DB
                                </span>
                                <span className="text-[11px] text-neutral-400 font-medium">Akumulasi SO</span>
                            </div>

                            {/* Mini Sparkline Chart */}
                            <div className="h-12 w-full mt-3">
                                <ResponsiveContainer width="100%" height="100%">
                                    <AreaChart data={sparklineOmset}>
                                        <defs>
                                            <linearGradient id="spkOmset" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stopColor="#10b981" stopOpacity={0.6}/>
                                                <stop offset="100%" stopColor="#10b981" stopOpacity={0.0}/>
                                            </linearGradient>
                                        </defs>
                                        <Area type="monotone" dataKey="v" stroke="#10b981" strokeWidth={2.5} fill="url(#spkOmset)" />
                                    </AreaChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Card 2: Lead */}
                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/90 shadow-xs rounded-3xl overflow-hidden hover:shadow-lg transition-all relative">
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between mb-3">
                                <span className="text-xs font-bold text-neutral-500 uppercase tracking-wider">Prospek Lead Masuk</span>
                                <div className="p-2 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800/60">
                                    <Users className="h-4 w-4" />
                                </div>
                            </div>
                            <div className="text-2xl font-black text-neutral-900 dark:text-neutral-50 tracking-tight">
                                {dynamicData?.totalLeads ?? 0} Prospek
                            </div>
                            <div className="flex items-center justify-between mt-2">
                                <span className="inline-flex items-center text-xs font-extrabold text-sky-600 dark:text-sky-400 bg-sky-100 dark:bg-sky-950/80 px-2 py-0.5 rounded-lg border border-sky-200 dark:border-sky-800">
                                    <TrendingUp className="mr-1 h-3 w-3" /> Real Time DB
                                </span>
                                <span className="text-[11px] text-neutral-400 font-medium">Total Lead</span>
                            </div>

                            {/* Mini Sparkline Chart */}
                            <div className="h-12 w-full mt-3">
                                <ResponsiveContainer width="100%" height="100%">
                                    <AreaChart data={sparklineLead}>
                                        <defs>
                                            <linearGradient id="spkLead" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stopColor="#0284c7" stopOpacity={0.6}/>
                                                <stop offset="100%" stopColor="#0284c7" stopOpacity={0.0}/>
                                            </linearGradient>
                                        </defs>
                                        <Area type="monotone" dataKey="v" stroke="#0284c7" strokeWidth={2.5} fill="url(#spkLead)" />
                                    </AreaChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Card 3: Orders */}
                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/90 shadow-xs rounded-3xl overflow-hidden hover:shadow-lg transition-all relative">
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between mb-3">
                                <span className="text-xs font-bold text-neutral-500 uppercase tracking-wider">Pesanan Penjualan (SO)</span>
                                <div className="p-2 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/60">
                                    <ShoppingCart className="h-4 w-4" />
                                </div>
                            </div>
                            <div className="text-2xl font-black text-neutral-900 dark:text-neutral-50 tracking-tight">
                                {dynamicData?.totalSalesOrders ?? 0} Order Resmi
                            </div>
                            <div className="flex items-center justify-between mt-2">
                                <span className="inline-flex items-center text-xs font-extrabold text-amber-600 dark:text-amber-400 bg-amber-100 dark:bg-amber-950/80 px-2 py-0.5 rounded-lg border border-amber-200 dark:border-amber-800">
                                    <TrendingUp className="mr-1 h-3 w-3" /> Real Time DB
                                </span>
                                <span className="text-[11px] text-neutral-400 font-medium">Total SO</span>
                            </div>

                            {/* Mini Sparkline Chart */}
                            <div className="h-12 w-full mt-3">
                                <ResponsiveContainer width="100%" height="100%">
                                    <AreaChart data={sparklineOrder}>
                                        <defs>
                                            <linearGradient id="spkOrder" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stopColor="#f59e0b" stopOpacity={0.6}/>
                                                <stop offset="100%" stopColor="#f59e0b" stopOpacity={0.0}/>
                                            </linearGradient>
                                        </defs>
                                        <Area type="monotone" dataKey="v" stroke="#f59e0b" strokeWidth={2.5} fill="url(#spkOrder)" />
                                    </AreaChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Card 4: Conversion Rate */}
                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/90 shadow-xs rounded-3xl overflow-hidden hover:shadow-lg transition-all relative">
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between mb-3">
                                <span className="text-xs font-bold text-neutral-500 uppercase tracking-wider">Rasio Konversi Deal</span>
                                <div className="p-2 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/60 dark:border-purple-800/60">
                                    <Target className="h-4 w-4" />
                                </div>
                            </div>
                            <div className="text-2xl font-black text-neutral-900 dark:text-neutral-50 tracking-tight">
                                {dynamicData?.conversionRate ?? '0.0'}%
                            </div>
                            <div className="flex items-center justify-between mt-2">
                                <span className="inline-flex items-center text-xs font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-950/80 px-2 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                    <TrendingUp className="mr-1 h-3 w-3" /> Real Time DB
                                </span>
                                <span className="text-[11px] text-neutral-400 font-medium">Realisasi Lead</span>
                            </div>

                            {/* Mini Sparkline Chart */}
                            <div className="h-12 w-full mt-3">
                                <ResponsiveContainer width="100%" height="100%">
                                    <AreaChart data={sparklineConversion}>
                                        <defs>
                                            <linearGradient id="spkConv" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stopColor="#8b5cf6" stopOpacity={0.6}/>
                                                <stop offset="100%" stopColor="#8b5cf6" stopOpacity={0.0}/>
                                            </linearGradient>
                                        </defs>
                                        <Area type="monotone" dataKey="v" stroke="#8b5cf6" strokeWidth={2.5} fill="url(#spkConv)" />
                                    </AreaChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* GRAPH SUITE: DUAL COMPARATIVE AREA CHART (YOY 2026 VS 2025) */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    {/* MAIN CHART (8 Cols): Dual Curve Comparative Area Chart */}
                    <Card className="lg:col-span-8 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/90 shadow-xs rounded-3xl overflow-hidden flex flex-col">
                        <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 p-6 flex flex-col gap-4">
                            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <div className="flex items-center gap-2.5">
                                        <div className="w-8 h-8 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200/60 dark:border-emerald-800/60">
                                            <BarChart3 className="h-4 w-4" />
                                        </div>
                                        <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">
                                            Grafik Perbandingan Omset Komparatif YoY (2026 vs 2025)
                                        </CardTitle>
                                    </div>
                                    <CardDescription className="text-xs text-neutral-500 mt-1">
                                        Perbandingan pertumbuhan kurva omset pendapatan tahun berjalan vs tahun sebelumnya.
                                    </CardDescription>
                                </div>

                                {/* Controls Toggle */}
                                <div className="flex items-center gap-2">
                                    <button
                                        type="button"
                                        onClick={() => setShowBaseline(!showBaseline)}
                                        className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all border ${
                                            showBaseline 
                                                ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900 border-neutral-800' 
                                                : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700'
                                        }`}
                                    >
                                        {showBaseline ? '✓ Pembanding 2025 Aktif' : '+ Tampilkan 2025'}
                                    </button>
                                </div>
                            </div>

                            {/* KPI Banner Row */}
                            <div className="grid grid-cols-3 gap-3 pt-1">
                                <div className="p-3.5 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 shadow-2xs">
                                    <span className="text-[10px] font-extrabold text-neutral-400 uppercase tracking-wider block">Total Omset 2026</span>
                                    <span className="text-sm font-black font-mono text-emerald-600 dark:text-emerald-400">Rp 5.078.000.000</span>
                                </div>
                                <div className="p-3.5 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 shadow-2xs">
                                    <span className="text-[10px] font-extrabold text-neutral-400 uppercase tracking-wider block">Pertumbuhan YoY Avg</span>
                                    <span className="text-sm font-black text-emerald-600 dark:text-emerald-400">+34.9% (Naik signifikan)</span>
                                </div>
                                <div className="p-3.5 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 shadow-2xs">
                                    <span className="text-[10px] font-extrabold text-neutral-400 uppercase tracking-wider block">Puncak Rekor Juli</span>
                                    <span className="text-sm font-black font-mono text-sky-600 dark:text-sky-400">Rp 1.249.000.000</span>
                                </div>
                            </div>
                        </CardHeader>

                        {/* Chart Body */}
                        <CardContent className="p-6 flex-1">
                            <div className="h-80 w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <AreaChart data={activeData} margin={{ top: 15, right: 15, left: 10, bottom: 0 }}>
                                        <defs>
                                            <linearGradient id="glow2026Gradient" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="5%" stopColor="#10b981" stopOpacity={0.5}/>
                                                <stop offset="95%" stopColor="#10b981" stopOpacity={0.0}/>
                                            </linearGradient>
                                            <linearGradient id="base2025Gradient" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="5%" stopColor="#6b7280" stopOpacity={0.25}/>
                                                <stop offset="95%" stopColor="#6b7280" stopOpacity={0.0}/>
                                            </linearGradient>
                                        </defs>
                                        <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e5e7eb" opacity={0.4} />
                                        <XAxis dataKey="month" tickLine={false} axisLine={false} tick={{ fill: '#6b7280', fontSize: 12, fontWeight: 700 }} />
                                        <YAxis 
                                            tickLine={false} 
                                            axisLine={false} 
                                            tick={{ fill: '#6b7280', fontSize: 11 }}
                                            tickFormatter={(val) => formatShortRupiah(val)}
                                        />
                                        <Tooltip content={<CustomComparativeTooltip />} />
                                        <Legend wrapperStyle={{ paddingTop: '10px', fontSize: '12px', fontWeight: 700 }} />

                                        {/* Baseline 2025 Curve */}
                                        {showBaseline && (
                                            <Area 
                                                type="monotone" 
                                                dataKey="rev2025" 
                                                name="Omset 2025 (Baseline)" 
                                                stroke="#9ca3af" 
                                                strokeWidth={2} 
                                                strokeDasharray="4 4"
                                                fillOpacity={1} 
                                                fill="url(#base2025Gradient)" 
                                            />
                                        )}

                                        {/* Main 2026 Curve */}
                                        <Area 
                                            type="monotone" 
                                            dataKey="rev2026" 
                                            name="Omset Realisasi 2026" 
                                            stroke="#10b981" 
                                            strokeWidth={4} 
                                            fillOpacity={1} 
                                            fill="url(#glow2026Gradient)" 
                                            activeDot={{ r: 9, stroke: '#10b981', strokeWidth: 3, fill: '#ffffff' }}
                                        />
                                    </AreaChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* SECONDARY CHART (4 Cols): Circular Donut Chart (Lead Source Breakdown) */}
                    <Card className="lg:col-span-4 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/90 shadow-xs rounded-3xl overflow-hidden flex flex-col">
                        <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 p-5">
                            <div className="flex items-center gap-2">
                                <div className="w-8 h-8 rounded-2xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200/60 dark:border-amber-800/60">
                                    <PieIcon className="h-4 w-4" />
                                </div>
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">
                                    Asal Kanal Prospek Lead
                                </CardTitle>
                            </div>
                            <CardDescription className="text-xs text-neutral-500 mt-1">
                                Kontribusi kanal pemasaran terhadap akuisisi lead.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="p-6 flex-1 flex flex-col justify-between">
                            {/* Donut Chart */}
                            <div className="h-48 w-full relative flex items-center justify-center">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={activeLeadSources}
                                            innerRadius={55}
                                            outerRadius={80}
                                            paddingAngle={4}
                                            dataKey="value"
                                        >
                                            {leadSourceData.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={entry.color} stroke="none" />
                                            ))}
                                        </Pie>
                                        <Tooltip 
                                            contentStyle={{ 
                                                backgroundColor: '#171717', 
                                                borderColor: '#262626', 
                                                borderRadius: '12px',
                                                color: '#fff' 
                                            }}
                                        />
                                    </PieChart>
                                </ResponsiveContainer>
                                <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <span className="text-2xl font-black text-neutral-900 dark:text-neutral-100">142</span>
                                    <span className="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">Total Lead</span>
                                </div>
                            </div>

                            {/* Legend items list */}
                            <div className="space-y-2 pt-3 border-t border-neutral-200 dark:border-neutral-800">
                                {leadSourceData.map((item, idx) => (
                                    <div key={idx} className="flex items-center justify-between text-xs p-1.5 rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-all">
                                        <div className="flex items-center gap-2">
                                            <span className="w-2.5 h-2.5 rounded-full inline-block" style={{ backgroundColor: item.color }} />
                                            <span className="font-semibold text-neutral-800 dark:text-neutral-200">{item.name}</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="font-mono font-bold text-neutral-900 dark:text-neutral-100">{item.value}</span>
                                            <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400">
                                                {item.percentage}
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Bottom Row: Quick Workspace & System Logs */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Quick Access Area */}
                    <div className="lg:col-span-2 flex flex-col gap-6">
                        <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/90 shadow-xs rounded-3xl flex-1">
                            <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 py-4 px-6">
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">Pusat Navigasi Pintar Modul</CardTitle>
                                <CardDescription className="text-xs text-neutral-500">Akses cepat ke alur kerja utama sesuai hak akses peran Anda.</CardDescription>
                            </CardHeader>
                            <CardContent className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {shortcuts.map((shortcut, idx) => {
                                    const Icon = shortcut.icon;
                                    return (
                                        <Link 
                                            key={idx} 
                                            href={shortcut.href}
                                            className="flex flex-col p-4 rounded-2xl border border-neutral-200/70 dark:border-neutral-800 hover:border-neutral-400 dark:hover:border-neutral-600 bg-neutral-50/50 dark:bg-neutral-850/50 hover:bg-white dark:hover:bg-neutral-800 transition-all group shadow-2xs hover:shadow-xs"
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className="p-2.5 rounded-xl bg-neutral-200/80 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 group-hover:scale-110 transition-transform">
                                                    <Icon className="h-5 w-5" />
                                                </div>
                                                <span className="font-bold text-sm text-neutral-900 dark:text-neutral-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 flex items-center justify-between w-full">
                                                    {shortcut.title}
                                                    <ChevronRight className="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity text-emerald-500" />
                                                </span>
                                            </div>
                                            <p className="text-xs text-neutral-500 dark:text-neutral-400 mt-2.5 leading-relaxed font-medium">
                                                {shortcut.description}
                                            </p>
                                        </Link>
                                    );
                                })}
                            </CardContent>
                        </Card>
                    </div>

                    {/* System Log Timeline */}
                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/90 shadow-xs rounded-3xl">
                        <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 py-4 px-6">
                            <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">Log Aktivitas Outbox Real-Time</CardTitle>
                            <CardDescription className="text-xs text-neutral-500">Jejak transaksi SAGA & integrasi otomatis.</CardDescription>
                        </CardHeader>
                        <CardContent className="p-6 flex flex-col gap-4">
                            {(dynamicData?.recentOrders?.length ? dynamicData.recentOrders : [
                                { title: 'Sales Order SO/2026/001 Diterbitkan', time: '12 menit lalu', desc: 'Item furnitur villa dialokasikan ke gudang.' },
                                { title: 'Verifikasi Invoice 5-Way Pass', time: '45 menit lalu', desc: 'Faktur penawaran disetujui tanpa variansi.' },
                                { title: 'Peluang Prospek Hotel Aman Upgraded', time: '2 jam lalu', desc: 'Nilai deal diperbarui ke Rp 840.000.000.' },
                                { title: 'Posting Jurnal Otomatis Sukses', time: '4 jam lalu', desc: 'Accounting Gateway mencatat transaksi buku besar.' }
                            ]).map((activity: any, idx: number) => (
                                <div key={idx} className="flex gap-4 group">
                                    <div className="flex flex-col items-center">
                                        <div className="h-3 w-3 rounded-full bg-emerald-500 ring-4 ring-emerald-100 dark:ring-emerald-950 z-10" />
                                        {idx !== 3 && <div className="w-0.5 flex-1 bg-neutral-200 dark:bg-neutral-800 my-1" />}
                                    </div>
                                    <div className="flex-1 pb-3">
                                        <div className="flex justify-between items-center gap-2">
                                            <span className="text-xs font-bold text-neutral-900 dark:text-neutral-100 group-hover:text-emerald-600">
                                                {activity.title}
                                            </span>
                                            <span className="text-[10px] text-neutral-400 font-mono">{activity.time}</span>
                                        </div>
                                        <p className="text-xs text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                                            {activity.desc}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
