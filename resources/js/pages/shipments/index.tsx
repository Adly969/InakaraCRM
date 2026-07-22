import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import type { Shipment, Carrier, Driver } from '@/types';
import { Eye, Truck, Search } from 'lucide-react';
import React from 'react';

interface Props {
    shipments: {
        data: Shipment[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        total: number;
    };
    carriers: Carrier[];
    drivers: Driver[];
}

export default function ShipmentsIndex({ shipments, carriers, drivers }: Props) {
    const { can } = usePermission();

    const getStatusBadgeClass = (status: string) => {
        switch (status) {
            case 'pending_dispatch':
                return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
            case 'in_transit':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
            case 'delivered':
                return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
            case 'failed_delivery':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            case 'returned':
                return 'bg-slate-100 text-slate-800 dark:bg-slate-900/30 dark:text-slate-400';
            case 'cancelled':
                return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
            default:
                return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
        }
    };

    const getStatusLabel = (status: string) => {
        return status.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
    };

    return (
        <>
            <Head title="Shipments Log" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex flex-col gap-1">
                        <div className="flex items-center gap-2">
                            <Truck className="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                Shipments Log
                            </h1>
                        </div>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Track dispatch vehicles, courier tracking numbers, and transport costs.
                        </p>
                    </div>
                </div>

                <Card className="flex-1 overflow-hidden border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50 shadow-sm">
                    <CardContent className="p-0">
                        <div className="relative w-full overflow-auto">
                            <table className="w-full caption-bottom text-sm">
                                <thead className="border-b border-neutral-200 bg-neutral-100/50 dark:border-neutral-800 dark:bg-neutral-800/50">
                                    <tr className="hover:bg-transparent">
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Reference No
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            DO Reference
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Courier Type
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Driver / Carrier
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Tracking No
                                        </th>
                                        <th className="h-10 px-4 text-left align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Status
                                        </th>
                                        <th className="h-10 px-4 text-right align-middle font-semibold text-neutral-600 dark:text-neutral-350">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200/60 dark:divide-neutral-850/60">
                                    {shipments.data.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="h-32 text-center text-neutral-500 dark:text-neutral-400">
                                                No Shipments found.
                                            </td>
                                        </tr>
                                    ) : (
                                        shipments.data.map((item) => (
                                            <tr key={item.id} className="hover:bg-neutral-100/40 dark:hover:bg-neutral-800/40 transition-colors">
                                                <td className="p-4 align-middle font-medium text-neutral-900 dark:text-neutral-100">
                                                    {item.reference_no}
                                                </td>
                                                <td className="p-4 align-middle text-neutral-700 dark:text-neutral-300">
                                                    {item.delivery_order?.reference_no}
                                                </td>
                                                <td className="p-4 align-middle text-neutral-700 dark:text-neutral-300">
                                                    {getStatusLabel(item.courier_type)}
                                                </td>
                                                <td className="p-4 align-middle text-neutral-700 dark:text-neutral-300">
                                                    {item.driver?.name || item.carrier?.name || 'Customer Pickup'}
                                                </td>
                                                <td className="p-4 align-middle text-neutral-700 dark:text-neutral-300">
                                                    {item.tracking_number || '-'}
                                                </td>
                                                 <td className="p-4 align-middle">
                                                     <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-neutral-700/60">
                                                         <span className={`h-1.5 w-1.5 rounded-full ${
                                                             item.status === 'delivered' ? 'bg-emerald-500' :
                                                             item.status === 'in_transit' ? 'bg-sky-500' :
                                                             item.status === 'pending_dispatch' ? 'bg-amber-500' :
                                                             'bg-rose-500'
                                                         }`} />
                                                         <span className="capitalize">{getStatusLabel(item.status)}</span>
                                                     </span>
                                                 </td>
                                                <td className="p-4 align-middle text-right">
                                                    <Button asChild variant="ghost" size="sm" className="hover:bg-neutral-200 dark:hover:bg-neutral-800">
                                                        <Link href={`/delivery-orders/${item.delivery_order_id}`}>
                                                            <Eye className="h-4 w-4 mr-1 text-neutral-600 dark:text-neutral-400" />
                                                            View DO
                                                        </Link>
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Simple Pagination */}
                        {shipments.last_page > 1 && (
                            <div className="flex items-center justify-end space-x-2 border-t border-neutral-200 dark:border-neutral-800 p-4">
                                {shipments.links.map((link, idx) => {
                                    if (!link.url) return null;
                                    return (
                                        <Button
                                            key={idx}
                                            variant={link.active ? 'default' : 'outline'}
                                            size="sm"
                                            asChild
                                            className={link.active ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : ''}
                                        >
                                            <Link href={link.url} dangerouslySetInnerHTML={{ __html: link.label }} />
                                        </Button>
                                    );
                                })}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
