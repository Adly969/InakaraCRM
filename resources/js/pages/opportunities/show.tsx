import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexOppRoute, edit as editOppRoute } from '@/routes/opportunities';
import { router } from '@inertiajs/react';
import type { Opportunity, CrmPipelineStage, CrmLossReason } from '@/types';
import { ArrowLeft, Pencil, TrendingUp, CheckCircle, XCircle, Calendar, Trophy, AlertTriangle } from 'lucide-react';
import { useState } from 'react';

interface Props {
    opportunity: Opportunity;
    stages: CrmPipelineStage[];
    lossReasons: CrmLossReason[];
}

export default function OpportunityShow({ opportunity, stages, lossReasons }: Props) {
    const { can } = usePermission();
    const [showStageSelect, setShowStageSelect] = useState(false);
    const [showLossReasonSelect, setShowLossReasonSelect] = useState(false);

    const isClosed = opportunity.status === 'won' || opportunity.status === 'lost';

    const handleStageChange = (stageId: number) => {
        router.post(route('opportunities.stage.update', opportunity.id), {
            pipeline_stage_id: stageId,
        });
        setShowStageSelect(false);
    };

    const handleWin = () => {
        if (confirm('Are you sure you want to close this opportunity as WON?')) {
            router.post(route('opportunities.win', opportunity.id));
        }
    };

    const handleLose = (reasonId: string, notes: string) => {
        router.post(route('opportunities.lose', opportunity.id), {
            loss_reason_id: reasonId,
            loss_notes: notes,
        });
        setShowLossReasonSelect(false);
    };

    const formatDate = (dateString?: string) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('en-US', {
            dateStyle: 'medium',
        });
    };

    return (
        <>
            <Head title={`Opportunity - ${opportunity.title}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={indexOppRoute()}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div className="flex flex-col gap-0.5">
                            <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                {opportunity.title}
                            </h1>
                            <p className="text-xs text-neutral-500">
                                Customer: {opportunity.customer?.name ?? '-'}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        {!isClosed && (
                            <>
                                <Button onClick={handleWin} variant="default" className="bg-emerald-600 hover:bg-emerald-700 text-white">
                                    <Trophy className="mr-2 h-4 w-4" />
                                    Close Won
                                </Button>
                                <Button onClick={() => setShowLossReasonSelect(true)} variant="destructive">
                                    <XCircle className="mr-2 h-4 w-4" />
                                    Close Lost
                                </Button>
                                <Button onClick={() => setShowStageSelect(!showStageSelect)} variant="outline">
                                    <TrendingUp className="mr-2 h-4 w-4" />
                                    Change Stage
                                </Button>
                            </>
                        )}
                        {can('edit-opportunities') && (
                            <Button asChild variant="outline">
                                <Link href={editOppRoute(opportunity.id)}>
                                    <Pencil className="mr-2 h-4 w-4" />
                                    Edit
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>

                {showStageSelect && (
                    <Card className="border-neutral-200 bg-neutral-100 p-4 dark:border-neutral-800 dark:bg-neutral-800">
                        <h3 className="text-sm font-semibold mb-2">Select Pipeline Stage</h3>
                        <div className="flex flex-wrap gap-2">
                            {stages.map((st) => (
                                <Button
                                    key={st.id}
                                    variant={opportunity.pipeline_stage_id === st.id ? 'default' : 'outline'}
                                    onClick={() => handleStageChange(st.id)}
                                    size="sm"
                                    className="text-xs"
                                >
                                    {st.name} ({st.probability}%)
                                </Button>
                            ))}
                        </div>
                    </Card>
                )}

                {showLossReasonSelect && (
                    <Card className="border-neutral-200 bg-neutral-100 p-4 dark:border-neutral-800 dark:bg-neutral-800">
                        <h3 className="text-sm font-semibold mb-2">Select Loss Reason</h3>
                        <form onSubmit={(e) => {
                            e.preventDefault();
                            const form = e.currentTarget;
                            const reasonId = (form.elements.namedItem('loss_reason_id') as HTMLSelectElement).value;
                            const notes = (form.elements.namedItem('loss_notes') as HTMLTextAreaElement).value;
                            if (!reasonId) {
                                alert('Please select a loss reason');
                                return;
                            }
                            handleLose(reasonId, notes);
                        }} className="space-y-3">
                            <div>
                                <select
                                    name="loss_reason_id"
                                    required
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring dark:bg-neutral-900"
                                >
                                    <option value="">-- Select Loss Reason --</option>
                                    {lossReasons.map((lr) => (
                                        <option key={lr.id} value={lr.id}>
                                            {lr.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <textarea
                                    name="loss_notes"
                                    placeholder="Loss notes/details..."
                                    className="border-input flex min-h-[60px] w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring dark:bg-neutral-900"
                                />
                            </div>
                            <div className="flex justify-end gap-2">
                                <Button type="button" variant="ghost" onClick={() => setShowLossReasonSelect(false)}>Cancel</Button>
                                <Button type="submit" variant="destructive">Confirm Loss</Button>
                            </div>
                        </form>
                    </Card>
                )}

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="md:col-span-2 space-y-6">
                        <Card className="border-neutral-200 bg-neutral-50/50 dark:border-neutral-800 dark:bg-neutral-900/50">
                            <CardHeader className="border-b pb-4">
                                <CardTitle className="text-base font-semibold">Deal Details</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Deal Value</span>
                                    <span className="text-lg font-bold text-neutral-900 dark:text-neutral-50">
                                        IDR {Number(opportunity.deal_value).toLocaleString('id-ID')}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Expected Revenue (Weighted)</span>
                                    <span className="text-lg font-bold text-neutral-900 dark:text-neutral-50">
                                        IDR {Number(opportunity.expected_revenue).toLocaleString('id-ID')}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Pipeline Stage</span>
                                    <span className="text-sm font-semibold text-indigo-600 dark:text-indigo-400 capitalize">
                                        {opportunity.stage?.name ?? '-'} ({opportunity.stage?.probability ?? 0}%)
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Status</span>
                                    <div>
                                        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${
                                            opportunity.status === 'won' ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' :
                                            opportunity.status === 'lost' ? 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/30 dark:text-red-400' :
                                            'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400'
                                        }`}>
                                            {opportunity.status}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Expected Close Date</span>
                                    <span className="text-sm font-medium">{formatDate(opportunity.expected_close_date)}</span>
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs text-neutral-500">Sales Assignee</span>
                                    <span className="text-sm font-medium">{opportunity.assigned_to_user?.name ?? '-'}</span>
                                </div>

                                {opportunity.lead && (
                                    <div className="sm:col-span-2 border-t pt-4 mt-2 flex flex-col gap-1">
                                        <span className="text-xs text-neutral-500">Converted From Lead</span>
                                        <Link href={`/leads/${opportunity.lead.id}`} className="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                            {opportunity.lead.name} {opportunity.lead.company_name ? `(${opportunity.lead.company_name})` : ''}
                                        </Link>
                                    </div>
                                )}

                                {opportunity.status === 'lost' && (
                                    <div className="sm:col-span-2 border-l-4 border-red-500 pl-4 py-2 mt-2 bg-red-50/50 dark:bg-red-950/20">
                                        <div className="text-xs font-semibold text-red-700 dark:text-red-400">Closed Lost Reason</div>
                                        <div className="text-sm font-semibold">{opportunity.loss_reason?.name ?? '-'}</div>
                                        {opportunity.loss_notes && <div className="text-xs mt-1 text-neutral-600 dark:text-neutral-400">{opportunity.loss_notes}</div>}
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Stage History */}
                        <Card className="border-neutral-200 bg-neutral-50/50 dark:border-neutral-800 dark:bg-neutral-900/50">
                            <CardHeader className="border-b pb-4">
                                <CardTitle className="text-base font-semibold">Stage Transitions History</CardTitle>
                            </CardHeader>
                            <CardContent className="p-6">
                                {opportunity.stage_histories && opportunity.stage_histories.length > 0 ? (
                                    <div className="space-y-4">
                                        {opportunity.stage_histories.map((hist) => (
                                            <div key={hist.id} className="flex justify-between items-start text-sm border-b pb-2 last:border-0 last:pb-0">
                                                <div className="flex flex-col gap-0.5">
                                                    <span className="font-semibold text-neutral-700 dark:text-neutral-300">
                                                        {hist.from_stage?.name} &rarr; {hist.to_stage?.name}
                                                    </span>
                                                    <span className="text-xs text-neutral-500">
                                                        Changed by {hist.changed_by_user?.name}
                                                    </span>
                                                </div>
                                                <div className="flex flex-col items-end gap-1">
                                                    <span className="text-xs font-medium text-neutral-600 dark:text-neutral-400">
                                                        Duration: {Math.round(hist.duration_in_seconds / 60)} mins
                                                    </span>
                                                    <span className="text-xs text-neutral-400">
                                                        {new Date(hist.created_at).toLocaleDateString()}
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-sm text-neutral-500 text-center py-4">No stage transitions logged yet.</div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        {/* Competitors Log */}
                        <Card className="border-neutral-200 bg-neutral-50/50 dark:border-neutral-800 dark:bg-neutral-900/50">
                            <CardHeader className="border-b pb-4">
                                <CardTitle className="text-base font-semibold">Competitors</CardTitle>
                            </CardHeader>
                            <CardContent className="p-4 flex flex-col gap-3">
                                {opportunity.competitors && opportunity.competitors.length > 0 ? (
                                    opportunity.competitors.map((c) => (
                                        <div key={c.id} className="flex flex-col gap-1 border-b pb-2 last:border-0 text-xs">
                                            <span className="font-bold text-neutral-800 dark:text-neutral-200">{c.competitor_name}</span>
                                            {c.strengths && <span><strong className="text-neutral-500">Strengths:</strong> {c.strengths}</span>}
                                            {c.weaknesses && <span><strong className="text-neutral-500">Weaknesses:</strong> {c.weaknesses}</span>}
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-xs text-neutral-500 text-center py-2">No competitors tracked.</div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Audit Details */}
                        <Card className="border-neutral-200 bg-neutral-50/50 dark:border-neutral-800 dark:bg-neutral-900/50">
                            <CardHeader className="border-b pb-4">
                                <CardTitle className="text-base font-semibold">System Details</CardTitle>
                            </CardHeader>
                            <CardContent className="p-4 flex flex-col gap-3 text-xs text-neutral-600 dark:text-neutral-400">
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Created By</span>
                                    <span>{opportunity.creator?.name ?? 'System'}</span>
                                </div>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold">Created At</span>
                                    <span>{formatDate(opportunity.created_at)}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

OpportunityShow.layout = {
    breadcrumbs: [
        {
            title: 'Opportunities',
            href: indexOppRoute(),
        },
    ],
};
