import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexLeadsRoute, edit as editLeadRoute, qualify, convert } from '@/routes/leads';
import statusRoute from '@/routes/leads/status';
import type { Lead } from '@/types';
import { ArrowLeft, Pencil, CheckCircle2, RefreshCw } from 'lucide-react';
import { router } from '@inertiajs/react';

interface Props {
    lead: Lead;
}

export default function LeadShow({ lead }: Props) {
    const { can, hasRole } = usePermission();

    const isDisqualified = lead.status === 'disqualified';
    const isSales = hasRole('sales') && !hasRole('owner') && !hasRole('admin') && !hasRole('manager');

    const handleStatusChange = (newStatus: string) => {
        if (isDisqualified && newStatus !== 'disqualified' && isSales) {
            alert('Only managers or owners can reopen a disqualified lead.');
            return;
        }

        let reason: string | null = null;
        if (newStatus === 'disqualified') {
            reason = prompt('Please enter a disqualification reason:');
            if (reason === null) {
                return; // User cancelled
            }
            if (!reason.trim()) {
                alert('A disqualification reason is required.');
                return;
            }
        }

        router.put(statusRoute.update(lead.id).url, {
            status: newStatus,
            disqualification_reason: reason,
        });
    };

    const handleQualify = () => {
        router.post(qualify.post(lead.id).url);
    };

    // Helper to format date
    const formatDate = (dateString?: string) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleString('en-US', {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    };

    return (
        <>
            <Head title={`Lead Details - ${lead.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-4xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={indexLeadsRoute()}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col gap-0.5">
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {lead.name}
                            </h1>
                            <p className="text-xs text-neutral-500">
                                {lead.reference_no ?? 'Reference Pending'}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        {lead.status === 'qualified' && (
                            <Button asChild variant="default" className="bg-emerald-600 hover:bg-emerald-700 text-white">
                                <Link href={convert.url(lead.id)}>
                                    <RefreshCw className="mr-2 h-4 w-4" />
                                    Convert to Opportunity
                                </Link>
                            </Button>
                        )}
                        {lead.status !== 'qualified' && lead.status !== 'converted' && lead.status !== 'disqualified' && (
                            <Button onClick={handleQualify} variant="outline" className="text-emerald-600 hover:text-emerald-700 border-emerald-200">
                                <CheckCircle2 className="mr-2 h-4 w-4" />
                                Qualify Lead
                            </Button>
                        )}
                        {can('edit-leads') && (
                            <Button asChild variant="outline">
                                <Link href={editLeadRoute(lead.id)}>
                                    <Pencil className="mr-2 h-4 w-4" />
                                    Edit Lead
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card className="md:col-span-2 border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                            <CardTitle className="text-base font-semibold">Lead Information</CardTitle>
                        </CardHeader>
                        <CardContent className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Company Name</span>
                                <span className="text-sm font-medium">{lead.company_name ?? '-'}</span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Job Title</span>
                                <span className="text-sm font-medium">{lead.job_title ?? '-'}</span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Email Address</span>
                                <span className="text-sm font-medium">{lead.email ?? '-'}</span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Phone Number</span>
                                <span className="text-sm font-medium">{lead.phone ?? '-'}</span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Website</span>
                                <span className="text-sm font-medium">
                                    {lead.website ? (
                                        <a href={lead.website} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">
                                            {lead.website}
                                        </a>
                                    ) : '-'}
                                </span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Lead Source</span>
                                <span className="text-sm font-medium capitalize">{lead.source}</span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Campaign Source</span>
                                <span className="text-sm font-medium">{lead.campaign_source ?? '-'}</span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Priority</span>
                                <span className="text-sm font-medium capitalize">{lead.priority}</span>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Heat Score</span>
                                <div>
                                    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${
                                        lead.heat_score === 'hot' ? 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/30 dark:text-red-400' :
                                        lead.heat_score === 'warm' ? 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400' :
                                        'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-400'
                                    }`}>
                                        {lead.heat_score}
                                    </span>
                                </div>
                            </div>
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-neutral-500">Current Status</span>
                                <div>
                                     <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-neutral-700/60">
                                         <span className={`h-1.5 w-1.5 rounded-full ${
                                             lead.status === 'qualified' || lead.status === 'converted' ? 'bg-emerald-500' :
                                             lead.status === 'contacted' ? 'bg-amber-500' :
                                             lead.status === 'new' || lead.status === 'assigned' ? 'bg-sky-500' :
                                             'bg-rose-500'
                                         }`} />
                                         <span className="capitalize">{lead.status}</span>
                                     </span>
                                </div>
                            </div>
                            <div className="flex flex-col gap-1 sm:col-span-2">
                                <span className="text-xs text-neutral-500">Assigned To</span>
                                <span className="text-sm font-medium">
                                    {typeof lead.assigned_to === 'object' && lead.assigned_to !== null
                                        ? (lead.assigned_to as any).name
                                        : lead.assigned_to_user?.name ?? '-'}
                                </span>
                            </div>

                            {isDisqualified && (
                                <div className="sm:col-span-2 flex flex-col gap-1 p-3 rounded-lg bg-red-50/50 dark:bg-red-950/20 border border-red-200/30">
                                    <span className="text-xs font-semibold text-red-700 dark:text-red-400">Disqualification Reason</span>
                                    <span className="text-sm text-red-900 dark:text-red-200">{lead.disqualification_reason ?? '-'}</span>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <div className="flex flex-col gap-6">
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold">Change Status</CardTitle>
                            </CardHeader>
                            <CardContent className="p-4 flex flex-col gap-2">
                                {['new', 'assigned', 'contacted', 'qualified', 'converted', 'disqualified'].map((st) => (
                                    <Button
                                        key={st}
                                        variant={lead.status === st ? 'default' : 'outline'}
                                        onClick={() => handleStatusChange(st)}
                                        className="w-full justify-start capitalize text-xs h-9"
                                        disabled={(isDisqualified && st !== 'disqualified' && isSales) || st === 'converted'}
                                    >
                                        {st}
                                    </Button>
                                ))}
                            </CardContent>
                        </Card>

                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                            <CardHeader className="border-b border-neutral-200 dark:border-neutral-800 pb-4">
                                <CardTitle className="text-base font-semibold">Audit Log</CardTitle>
                            </CardHeader>
                            <CardContent className="p-4 flex flex-col gap-3 text-xs text-neutral-600 dark:text-neutral-400">
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Created By</span>
                                    <span>
                                        {typeof lead.created_by === 'object' && lead.created_by !== null
                                            ? (lead.created_by as any).name
                                            : lead.creator?.name ?? 'System'}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Created At</span>
                                    <span>{formatDate(lead.created_at)}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Last Updated By</span>
                                    <span>
                                        {typeof lead.updated_by === 'object' && lead.updated_by !== null
                                            ? (lead.updated_by as any).name
                                            : lead.updater?.name ?? 'System'}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Last Updated At</span>
                                    <span>{formatDate(lead.updated_at)}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

LeadShow.layout = {
    breadcrumbs: [
        {
            title: 'Leads',
            href: indexLeadsRoute(),
        },
    ],
};
