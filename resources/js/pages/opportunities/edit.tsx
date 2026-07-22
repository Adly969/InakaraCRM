import { Form, Head, usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { useState } from 'react';
import OpportunityController from '@/actions/App/Http/Controllers/OpportunityController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as indexOppRoute } from '@/routes/opportunities';
import type { Opportunity, OpportunityStatusType } from '@/types';

interface Option {
    id: number;
    name: string;
    company_name?: string | null;
}

interface PageProps extends Record<string, any> {
    opportunity: Opportunity;
    customers: Option[];
    stages: Option[];
    users: Option[];
    lossReasons: Option[];
}

export default function OpportunityEdit() {
    const { opportunity, customers, stages, users, lossReasons } = usePage<PageProps>().props;

    const [status, setStatus] = useState<OpportunityStatusType>(opportunity.status);

    return (
        <>
            <Head title={`Edit Opportunity - ${opportunity.title}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-xl mx-auto">
                <div className="flex flex-col gap-1">
                    <Heading
                        title="Edit Opportunity"
                        description={`Update deal details for ${opportunity.title}.`}
                    />
                </div>

                <Form
                    {...OpportunityController.update.form({ opportunity: opportunity.id })}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-1">
                                <Label>Customer (Read-Only)</Label>
                                <Input
                                    disabled
                                    value={opportunity.customer?.name ?? '-'}
                                    className="mt-1 bg-neutral-100 dark:bg-neutral-800"
                                />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="title">Opportunity Title</Label>
                                <Input
                                    id="title"
                                    name="title"
                                    required
                                    defaultValue={opportunity.title}
                                    placeholder="e.g. Acme Corp - Cloud Services Deal"
                                    className="mt-1"
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="pipeline_stage_id">Pipeline Stage</Label>
                                <select
                                    id="pipeline_stage_id"
                                    name="pipeline_stage_id"
                                    required
                                    defaultValue={opportunity.pipeline_stage_id}
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    {stages.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.pipeline_stage_id} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="status">Deal Status</Label>
                                <select
                                    id="status"
                                    name="status"
                                    required
                                    defaultValue={opportunity.status}
                                    onChange={(e) => setStatus(e.target.value as OpportunityStatusType)}
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    <option value="qualification">Qualification</option>
                                    <option value="discovery">Discovery</option>
                                    <option value="proposal">Proposal</option>
                                    <option value="negotiation">Negotiation</option>
                                    <option value="verbal_commit">Verbal Commit</option>
                                    <option value="won">Won</option>
                                    <option value="lost">Lost</option>
                                </select>
                                <InputError message={errors.status} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="deal_value">Estimated Deal Value (IDR)</Label>
                                <Input
                                    id="deal_value"
                                    type="number"
                                    name="deal_value"
                                    required
                                    step="0.01"
                                    min="0"
                                    defaultValue={opportunity.deal_value}
                                    placeholder="e.g. 150000000.00"
                                    className="mt-1"
                                />
                                <InputError message={errors.deal_value} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="expected_close_date">Expected Close Date</Label>
                                <Input
                                    id="expected_close_date"
                                    type="date"
                                    name="expected_close_date"
                                    required
                                    defaultValue={opportunity.expected_close_date ? opportunity.expected_close_date.split('T')[0] : ''}
                                    className="mt-1"
                                />
                                <InputError message={errors.expected_close_date} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="assigned_to">Assignee (Sales Representative)</Label>
                                <select
                                    id="assigned_to"
                                    name="assigned_to"
                                    required
                                    defaultValue={opportunity.assigned_to}
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    {users.map((u) => (
                                        <option key={u.id} value={u.id}>
                                            {u.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.assigned_to} />
                            </div>

                            {status === 'lost' && (
                                <>
                                    <div className="grid gap-1">
                                        <Label htmlFor="loss_reason_id">Loss Reason</Label>
                                        <select
                                            id="loss_reason_id"
                                            name="loss_reason_id"
                                            required
                                            defaultValue={opportunity.loss_reason_id ?? ''}
                                            className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                        >
                                            <option value="" disabled>-- Select Reason --</option>
                                            {lossReasons.map((lr) => (
                                                <option key={lr.id} value={lr.id}>
                                                    {lr.name}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={errors.loss_reason_id} />
                                    </div>

                                    <div className="grid gap-1">
                                        <Label htmlFor="loss_notes">Loss Notes</Label>
                                        <textarea
                                            id="loss_notes"
                                            name="loss_notes"
                                            defaultValue={opportunity.loss_notes ?? ''}
                                            placeholder="Explain competition or reasons for losing the deal..."
                                            className="border-input flex min-h-[80px] w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                        />
                                        <InputError message={errors.loss_notes} />
                                    </div>
                                </>
                            )}

                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-800">
                                <Button variant="ghost" asChild>
                                    <Link href={indexOppRoute()}>Cancel</Link>
                                </Button>
                                <Button disabled={processing}>Save Changes</Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

OpportunityEdit.layout = {
    breadcrumbs: [
        {
            title: 'Opportunities',
            href: indexOppRoute(),
        },
        {
            title: 'Edit',
        },
    ],
};
