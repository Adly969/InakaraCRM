import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Clock, Layers, Sparkles, ShieldCheck } from 'lucide-react';

interface PlaceholderProps {
    title: string;
    description: string;
    plannedSprint: string;
    status: string;
    moduleGroup: string;
}

export default function PlaceholderPage({
    title,
    description,
    plannedSprint = 'Sprint 21',
    status = 'Coming Soon',
    moduleGroup = 'ERP Module',
}: PlaceholderProps) {
    return (
        <>
            <Head title={`${title} - Coming Soon`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header Banner */}
                <div className="relative overflow-hidden rounded-3xl bg-neutral-900 px-8 py-8 text-white dark:bg-neutral-950 shadow-2xl border border-neutral-800">
                    <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,var(--tw-gradient-stops))] from-neutral-800/40 via-neutral-900 to-neutral-950 opacity-90" />
                    <div className="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <div className="flex items-center gap-3 mb-2">
                                <span className="inline-flex items-center rounded-full bg-sky-500/20 text-sky-400 border border-sky-500/40 px-3 py-1 text-xs font-bold uppercase tracking-wider">
                                    <Layers className="h-3.5 w-3.5 mr-1.5" />
                                    {moduleGroup}
                                </span>
                                <span className="inline-flex items-center rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/40 px-3 py-1 text-xs font-bold uppercase tracking-wider">
                                    <Clock className="h-3.5 w-3.5 mr-1.5" />
                                    {plannedSprint}
                                </span>
                            </div>
                            <h1 className="text-3xl font-black tracking-tight text-white flex items-center gap-3">
                                {title}
                                <Sparkles className="h-6 w-6 text-amber-400" />
                            </h1>
                            <p className="mt-2 text-neutral-400 max-w-2xl text-sm leading-relaxed font-medium">
                                {description}
                            </p>
                        </div>
                        <div className="flex items-center gap-2">
                            <Badge className="bg-neutral-800 text-neutral-300 border-neutral-700 px-4 py-2 text-xs font-mono font-bold">
                                Modul Reserved
                            </Badge>
                        </div>
                    </div>
                </div>

                {/* Coming Soon Card Container */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card className="md:col-span-2 border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/90 shadow-xs rounded-3xl p-8 flex flex-col items-center justify-center text-center min-h-[340px]">
                        <div className="w-16 h-16 rounded-3xl bg-neutral-100 dark:bg-neutral-800/80 text-neutral-600 dark:text-neutral-300 flex items-center justify-center mb-5 border border-neutral-200 dark:border-neutral-700 shadow-inner">
                            <Clock className="h-8 w-8 text-neutral-500" />
                        </div>
                        <h2 className="text-2xl font-black text-neutral-900 dark:text-neutral-100 tracking-tight">
                            Modul {title}
                        </h2>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400 mt-2 max-w-md font-medium leading-relaxed">
                            Rencana pengembangan fitur ini terjadwal pada <strong className="text-neutral-800 dark:text-neutral-200">{plannedSprint}</strong> sesuai dengan Enterprise Roadmap InakaraCRM.
                        </p>
                        <div className="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-neutral-100 dark:bg-neutral-800 text-xs font-extrabold text-neutral-700 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-750">
                            <span className="w-2 h-2 rounded-full bg-amber-500 animate-pulse" />
                            Status: {status}
                        </div>
                    </Card>

                    <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/90 shadow-xs rounded-3xl p-6 flex flex-col justify-between">
                        <div>
                            <CardHeader className="p-0 mb-4">
                                <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
                                    <ShieldCheck className="h-5 w-5 text-emerald-500" /> Spesifikasi Enterprise
                                </CardTitle>
                                <CardDescription className="text-xs text-neutral-500">
                                    Informasi placeholder arsitektur navigasi.
                                </CardDescription>
                            </CardHeader>
                            <div className="space-y-3 text-xs">
                                <div className="p-3 rounded-2xl bg-neutral-50 dark:bg-neutral-850 border border-neutral-200/80 dark:border-neutral-800">
                                    <span className="text-neutral-400 font-medium block uppercase text-[10px] tracking-wider">Modul ERP</span>
                                    <span className="font-bold text-neutral-800 dark:text-neutral-200">{moduleGroup}</span>
                                </div>
                                <div className="p-3 rounded-2xl bg-neutral-50 dark:bg-neutral-850 border border-neutral-200/80 dark:border-neutral-800">
                                    <span className="text-neutral-400 font-medium block uppercase text-[10px] tracking-wider">Target Implementasi</span>
                                    <span className="font-bold text-amber-600 dark:text-amber-400">{plannedSprint}</span>
                                </div>
                                <div className="p-3 rounded-2xl bg-neutral-50 dark:bg-neutral-850 border border-neutral-200/80 dark:border-neutral-800">
                                    <span className="text-neutral-400 font-medium block uppercase text-[10px] tracking-wider">Hak Akses Permukiman</span>
                                    <span className="font-mono text-neutral-600 dark:text-neutral-400">view-{title.toLowerCase().replace(/\s+/g, '-')}</span>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </>
    );
}

PlaceholderPage.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Modul Enterprise', href: '#' },
    ],
};
