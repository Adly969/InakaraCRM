import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { ClipboardList, Plus, Search, CheckCircle2, Clock, AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface TaskItem {
    id: number;
    product: { sku: string; name: string };
    quantity_target: number;
}

interface Task {
    id: number;
    task_number: string;
    task_type: string;
    status: string;
    priority: string;
    warehouse: { name: string };
    operator: { name: string } | null;
    items: TaskItem[];
}

interface Props {
    tasks: {
        data: Task[];
        links: any[];
    };
    operators: { id: number; name: string }[];
    warehouses: { id: number; name: string }[];
    filters: {
        search?: string;
        status?: string;
    };
}

export default function TaskIndex({ tasks, operators, warehouses, filters }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Warehouse', href: '#' },
        { title: 'WMS Tasks', href: '/inventory/tasks' },
    ];

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'completed':
                return <span className="px-2 py-0.5 text-xs font-semibold rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 flex items-center gap-1"><CheckCircle2 className="h-3 w-3" /> Completed</span>;
            case 'in_progress':
                return <span className="px-2 py-0.5 text-xs font-semibold rounded bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 flex items-center gap-1"><Clock className="h-3 w-3" /> In Progress</span>;
            case 'assigned':
                return <span className="px-2 py-0.5 text-xs font-semibold rounded bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Assigned</span>;
            default:
                return <span className="px-2 py-0.5 text-xs font-semibold rounded bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-300">Draft</span>;
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="WMS Warehouse Task Management" />

            <div className="flex flex-col space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white flex items-center gap-2">
                            <ClipboardList className="h-6 w-6 text-sky-600" />
                            WMS Task Execution Board
                        </h1>
                        <p className="text-sm text-neutral-500">Assign picking, put-away, and receiving tasks to warehouse mobile operators.</p>
                    </div>
                </div>

                <div className="rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900 overflow-hidden shadow-xs">
                    <table className="w-full text-left text-sm text-neutral-600 dark:text-neutral-300">
                        <thead className="bg-neutral-50 dark:bg-neutral-950 border-b border-neutral-200 dark:border-neutral-800 text-xs font-semibold uppercase text-neutral-500">
                            <tr>
                                <th className="px-4 py-3">Task #</th>
                                <th className="px-4 py-3">Task Type</th>
                                <th className="px-4 py-3">Warehouse</th>
                                <th className="px-4 py-3">Assigned Operator</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                            {tasks.data.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-neutral-400">
                                        No WMS tasks currently active.
                                    </td>
                                </tr>
                            ) : (
                                tasks.data.map((task) => (
                                    <tr key={task.id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/50">
                                        <td className="px-4 py-3 font-mono font-semibold text-neutral-900 dark:text-white">
                                            {task.task_number}
                                        </td>
                                        <td className="px-4 py-3 font-medium capitalize">
                                            {task.task_type.replace('_', ' ')}
                                        </td>
                                        <td className="px-4 py-3">{task.warehouse?.name}</td>
                                        <td className="px-4 py-3">{task.operator?.name || 'Unassigned'}</td>
                                        <td className="px-4 py-3">{getStatusBadge(task.status)}</td>
                                        <td className="px-4 py-3 text-right">
                                            {task.status !== 'completed' && (
                                                <Link href={`/inventory/tasks/${task.id}/complete`} method="post" as="button">
                                                    <Button size="sm" variant="outline" className="text-emerald-600 border-emerald-200 hover:bg-emerald-50">
                                                        Complete Task
                                                    </Button>
                                                </Link>
                                            )}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
