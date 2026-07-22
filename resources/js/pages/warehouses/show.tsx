import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Building2, Layers, MapPin, ShieldCheck, ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface Bin {
    id: number;
    bin_code: string;
    aisle: string | null;
    rack: string | null;
    shelf: string | null;
    is_locked: boolean;
}

interface Zone {
    id: number;
    code: string;
    name: string;
    zone_type: string;
    is_temperature_controlled: boolean;
    bins: Bin[];
}

interface Warehouse {
    id: number;
    code: string;
    name: string;
    type: string;
    status: string;
    address: string | null;
    total_capacity_sqm: number | null;
    zones: Zone[];
}

interface Props {
    warehouse: Warehouse;
}

export default function WarehouseShow({ warehouse }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Warehouses', href: '/warehouses' },
        { title: warehouse.code, href: `/warehouses/${warehouse.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Warehouse ${warehouse.code}`} />

            <div className="flex flex-col space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link href="/warehouses">
                            <Button variant="outline" size="icon" className="h-9 w-9">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white flex items-center gap-2">
                                <Building2 className="h-6 w-6 text-sky-600" />
                                {warehouse.name} ({warehouse.code})
                            </h1>
                            <p className="text-sm text-neutral-500">{warehouse.address || 'No physical address specified'}</p>
                        </div>
                    </div>
                </div>

                {/* Warehouse Metrics Overview */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div className="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900 shadow-xs">
                        <div className="flex items-center gap-3 text-neutral-500">
                            <Layers className="h-5 w-5 text-sky-600" />
                            <span className="text-sm font-medium">Storage Zones</span>
                        </div>
                        <p className="mt-2 text-2xl font-bold text-neutral-900 dark:text-white">{warehouse.zones.length}</p>
                    </div>
                    <div className="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900 shadow-xs">
                        <div className="flex items-center gap-3 text-neutral-500">
                            <MapPin className="h-5 w-5 text-emerald-600" />
                            <span className="text-sm font-medium">Total Bins</span>
                        </div>
                        <p className="mt-2 text-2xl font-bold text-neutral-900 dark:text-white">
                            {warehouse.zones.reduce((sum, zone) => sum + (zone.bins?.length || 0), 0)}
                        </p>
                    </div>
                    <div className="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900 shadow-xs">
                        <div className="flex items-center gap-3 text-neutral-500">
                            <ShieldCheck className="h-5 w-5 text-purple-600" />
                            <span className="text-sm font-medium">Warehouse Status</span>
                        </div>
                        <p className="mt-2 text-lg font-bold capitalize text-emerald-600">{warehouse.status}</p>
                    </div>
                </div>

                {/* Zone & Bin Topology */}
                <div className="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <h2 className="text-lg font-bold text-neutral-900 dark:text-white mb-4">Zone & Bin Topology</h2>

                    {warehouse.zones.length === 0 ? (
                        <div className="py-8 text-center text-sm text-neutral-500">
                            No storage zones configured for this warehouse yet.
                        </div>
                    ) : (
                        <div className="space-y-6">
                            {warehouse.zones.map((zone) => (
                                <div key={zone.id} className="rounded-lg border border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-900/50 p-4">
                                    <div className="flex items-center justify-between mb-3">
                                        <div>
                                            <h3 className="font-semibold text-neutral-900 dark:text-white">{zone.name} ({zone.code})</h3>
                                            <span className="text-xs text-neutral-400 capitalize">{zone.zone_type} Zone</span>
                                        </div>
                                        {zone.is_temperature_controlled && (
                                            <span className="px-2 py-0.5 text-xs rounded bg-sky-100 text-sky-700 font-medium">Cold Storage</span>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2">
                                        {zone.bins?.map((bin) => (
                                            <div key={bin.id} className={`p-2.5 rounded-lg border text-center text-xs font-mono transition-colors ${
                                                bin.is_locked 
                                                    ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-950/40 dark:border-rose-900' 
                                                    : 'bg-white border-neutral-200 text-neutral-800 dark:bg-neutral-800 dark:border-neutral-700'
                                            }`}>
                                                <div className="font-bold">{bin.bin_code}</div>
                                                {bin.is_locked && <span className="block text-[10px] text-rose-500 mt-0.5">LOCKED</span>}
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
