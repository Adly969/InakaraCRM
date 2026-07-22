import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import { index as indexOppRoute, create as createOppRoute, show as showOppRoute, edit as editOppRoute, destroy as destroyOppRoute } from '@/routes/opportunities';
import type { Opportunity, CrmPipelineStage } from '@/types';
import { Plus, Eye, Pencil, Trash2, Kanban, List, ArrowLeftRight, CheckCircle2, XCircle } from 'lucide-react';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface KanbanStageGroup {
    stage_id: number;
    stage_name: string;
    probability: number;
    forecast_category: string;
    total_deal_value: number;
    weighted_revenue: number;
    opportunities_count: number;
    opportunities: Opportunity[];
}

interface Props {
    opportunities: {
        data: Opportunity[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        last_page: number;
        total: number;
    };
    stages: CrmPipelineStage[];
    kanbanData: KanbanStageGroup[];
    filters: {
        status?: string;
        stage_id?: string;
    };
}

export default function OpportunitiesIndex({ opportunities, stages, kanbanData: initialKanbanData, filters }: Props) {
    const { can } = usePermission();
    const [viewMode, setViewMode] = useState<'list' | 'kanban'>('kanban');
    const [kanbanData, setKanbanData] = useState<KanbanStageGroup[]>(initialKanbanData);
    const [isUpdating, setIsUpdating] = useState<number | null>(null);

    const handleDelete = (opp: Opportunity) => {
        if (confirm(`Are you sure you want to delete opportunity "${opp.title}"?`)) {
            router.delete(destroyOppRoute(opp.id).url);
        }
    };

    const handleFilterChange = (key: string, value: string) => {
        const newFilters = { ...filters, [key]: value };
        router.get(indexOppRoute().url, newFilters, { preserveState: true });
    };

    // Native Drag and Drop Implementation with Optimistic Updates
    const handleDragStart = (e: React.DragEvent, opportunityId: number, sourceStageId: number) => {
        e.dataTransfer.setData('text/plain', JSON.stringify({ opportunityId, sourceStageId }));
    };

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
    };

    const handleDrop = async (e: React.DragEvent, targetStageId: number) => {
        e.preventDefault();
        const dataStr = e.dataTransfer.getData('text/plain');
        if (!dataStr) return;

        try {
            const { opportunityId, sourceStageId } = JSON.parse(dataStr) as { opportunityId: number; sourceStageId: number };
            if (sourceStageId === targetStageId) return;

            await moveOpportunityStage(opportunityId, sourceStageId, targetStageId);
        } catch (err) {
            console.error('Failed to parse drag payload:', err);
        }
    };

    const moveOpportunityStage = async (oppId: number, fromStageId: number, toStageId: number) => {
        setIsUpdating(oppId);

        // 1. Find target stage configuration
        const targetStage = stages.find(s => s.id === toStageId);
        if (!targetStage) return;

        // 2. Perform optimistic UI update locally
        const backupData = JSON.parse(JSON.stringify(kanbanData)) as KanbanStageGroup[];
        
        let movingOpp: Opportunity | null = null;
        const updatedGroups = kanbanData.map(group => {
            if (group.stage_id === fromStageId) {
                movingOpp = group.opportunities.find(o => o.id === oppId) || null;
                return {
                    ...group,
                    opportunities: group.opportunities.filter(o => o.id !== oppId),
                    opportunities_count: group.opportunities_count - 1,
                    total_deal_value: group.total_deal_value - (movingOpp ? Number(movingOpp.deal_value) : 0)
                };
            }
            return group;
        });

        if (movingOpp) {
            const finalGroups = updatedGroups.map(group => {
                if (group.stage_id === toStageId && movingOpp) {
                    const updatedOpp = { 
                        ...movingOpp, 
                        pipeline_stage_id: toStageId,
                        stage: targetStage
                    };
                    return {
                        ...group,
                        opportunities: [...group.opportunities, updatedOpp],
                        opportunities_count: group.opportunities_count + 1,
                        total_deal_value: group.total_deal_value + Number(movingOpp.deal_value)
                    };
                }
                return group;
            });
            setKanbanData(finalGroups);
        }

        // 3. Post change to backend
        router.post(`/opportunities/${oppId}/stage`, {
            pipeline_stage_id: toStageId
        }, {
            preserveScroll: true,
            onError: (errors) => {
                // Rollback optimistic updates on validation/permission error
                setKanbanData(backupData);
                alert(errors.pipeline_stage_id || 'Failed to update pipeline stage.');
            },
            onFinish: () => {
                setIsUpdating(null);
            }
        });
    };

    return (
        <>
            <Head title="Opportunities" />
            <div className="flex h-full flex-1 flex-col gap-4 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex flex-col gap-1">
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Opportunities Sales Pipeline
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Track expected revenue, pipeline stages, and close deals.
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <div className="inline-flex rounded-md shadow-xs bg-neutral-100 dark:bg-neutral-800 p-1">
                            <Button 
                                variant={viewMode === 'kanban' ? 'default' : 'ghost'} 
                                size="sm" 
                                onClick={() => setViewMode('kanban')}
                                className="h-8 px-3 text-xs"
                            >
                                <Kanban className="mr-1.5 h-3.5 w-3.5" />
                                Kanban
                            </Button>
                            <Button 
                                variant={viewMode === 'list' ? 'default' : 'ghost'} 
                                size="sm" 
                                onClick={() => setViewMode('list')}
                                className="h-8 px-3 text-xs"
                            >
                                <List className="mr-1.5 h-3.5 w-3.5" />
                                List
                            </Button>
                        </div>
                        {can('create-opportunities') && (
                            <Button asChild>
                                <Link href={createOppRoute()}>
                                    <Plus className="mr-2 h-4 w-4" />
                                    Create Opportunity
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>

                {/* Filters */}
                <div className="flex items-center gap-3">
                    <select
                        value={filters.status ?? ''}
                        onChange={(e) => handleFilterChange('status', e.target.value)}
                        className="border-input flex h-9 w-48 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200"
                    >
                        <option value="">All Statuses</option>
                        <option value="qualification">Qualification</option>
                        <option value="discovery">Discovery</option>
                        <option value="proposal">Proposal</option>
                        <option value="negotiation">Negotiation</option>
                        <option value="verbal_commit">Verbal Commit</option>
                        <option value="won">Won</option>
                        <option value="lost">Lost</option>
                    </select>

                    <select
                        value={filters.stage_id ?? ''}
                        onChange={(e) => handleFilterChange('stage_id', e.target.value)}
                        className="border-input flex h-9 w-48 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200"
                    >
                        <option value="">All Stages</option>
                        {stages.map((stage) => (
                            <option key={stage.id} value={stage.id}>
                                {stage.name} ({stage.probability}%)
                            </option>
                        ))}
                    </select>
                </div>

                {viewMode === 'kanban' ? (
                    // Enterprise Kanban View Board
                    <div className="flex flex-1 gap-4 overflow-x-auto pb-4">
                        {kanbanData.map((group) => (
                            <div 
                                key={group.stage_id} 
                                onDragOver={handleDragOver}
                                onDrop={(e) => handleDrop(e, group.stage_id)}
                                className="flex w-80 shrink-0 flex-col rounded-lg bg-neutral-100/80 p-3 dark:bg-neutral-900/60 border border-neutral-200/50 dark:border-neutral-800/50"
                            >
                                {/* Column Header */}
                                <div className="mb-3">
                                    <div className="flex items-center justify-between">
                                        <h3 className="font-semibold text-neutral-900 dark:text-neutral-50 flex items-center gap-1.5 text-sm">
                                            {group.stage_name}
                                            <span className="rounded-full bg-neutral-200 dark:bg-neutral-800 px-2 py-0.5 text-xs text-neutral-600 dark:text-neutral-400">
                                                {group.opportunities_count}
                                            </span>
                                        </h3>
                                        <span className="text-xs text-neutral-500">
                                            {group.probability}%
                                        </span>
                                    </div>
                                    <div className="mt-1 flex flex-col gap-0.5 border-t border-neutral-200/50 pt-1.5 dark:border-neutral-800/50">
                                        <span className="text-[11px] text-neutral-500">
                                            Total: <strong className="text-neutral-700 dark:text-neutral-300">IDR {group.total_deal_value.toLocaleString('id-ID')}</strong>
                                        </span>
                                        <span className="text-[11px] text-neutral-500">
                                            Weighted: <strong className="text-neutral-700 dark:text-neutral-300">IDR {group.weighted_revenue.toLocaleString('id-ID')}</strong>
                                        </span>
                                    </div>
                                </div>

                                {/* Cards List */}
                                <div className="flex flex-1 flex-col gap-2 overflow-y-auto min-h-[400px]">
                                    {group.opportunities.length === 0 ? (
                                        <div className="flex flex-1 items-center justify-center rounded-md border border-dashed border-neutral-300 dark:border-neutral-800 p-4 text-center text-xs text-neutral-400">
                                            Drag deals here
                                        </div>
                                    ) : (
                                        group.opportunities.map((opp) => (
                                            <div
                                                key={opp.id}
                                                draggable
                                                onDragStart={(e) => handleDragStart(e, opp.id, group.stage_id)}
                                                className={`relative cursor-grab rounded-md border border-neutral-200 bg-white p-3 shadow-xs hover:border-neutral-400 hover:shadow-sm dark:border-neutral-800 dark:bg-neutral-950 transition-all ${
                                                    isUpdating === opp.id ? 'opacity-50 pointer-events-none' : ''
                                                }`}
                                            >
                                                <div className="flex flex-col gap-1">
                                                    <div className="flex items-start justify-between gap-1">
                                                        <Link 
                                                            href={showOppRoute(opp.id)}
                                                            className="text-xs font-semibold text-neutral-900 hover:text-blue-600 dark:text-neutral-50 dark:hover:text-blue-400"
                                                        >
                                                            {opp.title}
                                                        </Link>
                                                        <span className={`inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-medium capitalize ${
                                                            opp.status === 'won' ? 'bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300' :
                                                            opp.status === 'lost' ? 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300' :
                                                            'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                                                        }`}>
                                                            {opp.status}
                                                        </span>
                                                    </div>
                                                    <span className="text-[11px] text-neutral-500">
                                                        {opp.customer?.name ?? 'No customer'}
                                                    </span>
                                                    <div className="mt-2 flex items-center justify-between text-[11px] text-neutral-600 dark:text-neutral-400">
                                                        <span className="font-semibold text-neutral-800 dark:text-neutral-300">
                                                            IDR {Number(opp.deal_value).toLocaleString('id-ID')}
                                                        </span>
                                                        <span>
                                                            {new Date(opp.expected_close_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    // Fallback List Table View
                    <Card className="flex-1 overflow-hidden border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/50 dark:bg-neutral-900/50">
                        <CardContent className="p-0">
                            <div className="relative w-full overflow-auto">
                                <table className="w-full caption-bottom text-sm">
                                    <thead className="border-b border-neutral-200 bg-neutral-100/50 dark:border-neutral-800 dark:bg-neutral-800/50">
                                        <tr className="hover:bg-transparent">
                                            <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                                Title
                                            </th>
                                            <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                                Customer
                                            </th>
                                            <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                                Stage
                                            </th>
                                            <th className="h-10 px-4 text-right align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                                Deal Value
                                            </th>
                                            <th className="h-10 px-4 text-right align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                                Expected Close
                                            </th>
                                            <th className="h-10 px-4 text-left align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                                Assignee
                                            </th>
                                            <th className="h-10 px-4 text-right align-middle font-medium text-neutral-500 dark:text-neutral-400">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800">
                                        {opportunities.data.length === 0 ? (
                                            <tr>
                                                <td colSpan={7} className="h-24 text-center text-neutral-500 dark:text-neutral-400">
                                                    No opportunities found.
                                                </td>
                                            </tr>
                                        ) : (
                                            opportunities.data.map((opp) => (
                                                <tr key={opp.id} className="hover:bg-neutral-100/30 dark:hover:bg-neutral-800/30 transition-colors">
                                                    <td className="p-4 align-middle font-medium text-neutral-900 dark:text-neutral-50">
                                                        {opp.title}
                                                    </td>
                                                    <td className="p-4 align-middle">
                                                        {opp.customer?.name ?? '-'}
                                                    </td>
                                                    <td className="p-4 align-middle capitalize">
                                                         <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-neutral-700/60">
                                                             <span className={`h-1.5 w-1.5 rounded-full ${
                                                                 opp.status === 'won' ? 'bg-emerald-500' :
                                                                 opp.status === 'lost' ? 'bg-rose-500' :
                                                                 'bg-amber-500'
                                                             }`} />
                                                             <span className="capitalize">{opp.stage?.name ?? opp.status}</span>
                                                         </span>
                                                    </td>
                                                    <td className="p-4 align-middle text-right font-semibold">
                                                        IDR {Number(opp.deal_value).toLocaleString('id-ID')}
                                                    </td>
                                                    <td className="p-4 align-middle text-right">
                                                        {new Date(opp.expected_close_date).toLocaleDateString('en-US', { dateStyle: 'medium' })}
                                                    </td>
                                                    <td className="p-4 align-middle">
                                                        {opp.assigned_to_user?.name ?? '-'}
                                                    </td>
                                                    <td className="p-4 align-middle text-right">
                                                        <div className="flex items-center justify-end gap-2">
                                                            <Button variant="ghost" size="icon" asChild>
                                                                <Link href={showOppRoute(opp.id)}>
                                                                     <Eye className="h-4 w-4" />
                                                                </Link>
                                                            </Button>
                                                            {can('edit-opportunities') && (
                                                                <Button variant="ghost" size="icon" asChild>
                                                                    <Link href={editOppRoute(opp.id)}>
                                                                        <Pencil className="h-4 w-4" />
                                                                    </Link>
                                                                </Button>
                                                            )}
                                                            {can('delete-opportunities') && (
                                                                <Button variant="ghost" size="icon" onClick={() => handleDelete(opp)} className="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30">
                                                                    <Trash2 className="h-4 w-4" />
                                                                </Button>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {opportunities.links && opportunities.links.length > 3 && (
                                <div className="flex items-center justify-between border-t border-neutral-200 p-4 dark:border-neutral-800">
                                    <div className="text-xs text-neutral-500">
                                        Showing page {opportunities.current_page} of {opportunities.last_page} ({opportunities.total} total)
                                    </div>
                                    <div className="flex items-center gap-1">
                                        {opportunities.links.map((link, idx) => {
                                            const cleanLabel = link.label
                                                .replace('&laquo;', '‹')
                                                .replace('&raquo;', '›')
                                                .replace('Previous', '‹')
                                                .replace('Next', '›');

                                            if (!link.url) {
                                                return (
                                                    <Button key={idx} variant="outline" size="sm" disabled className="px-2 text-xs">
                                                        {cleanLabel}
                                                    </Button>
                                                );
                                            }

                                            return (
                                                <Button key={idx} variant={link.active ? 'default' : 'outline'} size="sm" asChild className="px-2 text-xs">
                                                    <Link href={link.url}>
                                                        {cleanLabel}
                                                    </Link>
                                                </Button>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}

OpportunitiesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Opportunities',
            href: indexOppRoute(),
        },
    ],
};
