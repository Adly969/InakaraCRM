import { Form, Head, usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import OpportunityController from '@/actions/App/Http/Controllers/OpportunityController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as indexOppRoute } from '@/routes/opportunities';

interface Option {
    id: number;
    name: string;
    company_name?: string | null;
}

interface PageProps extends Record<string, any> {
    customers: Option[];
    stages: Option[];
    users: Option[];
}

export default function OpportunityCreate() {
    const { customers, stages, users } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Create Opportunity" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-xl mx-auto">
                <div className="flex flex-col gap-1">
                    <Heading
                        title="Create Opportunity"
                        description="Add a new potential deal to the sales pipeline."
                    />
                </div>

                <Form
                    {...OpportunityController.store.form()}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-1">
                                <Label htmlFor="customer_id">Customer</Label>
                                <select
                                    id="customer_id"
                                    name="customer_id"
                                    required
                                    defaultValue=""
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    <option value="" disabled>-- Select Customer --</option>
                                    {customers.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.name} {c.company_name ? `(${c.company_name})` : ''}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.customer_id} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="title">Opportunity Title</Label>
                                <Input
                                    id="title"
                                    name="title"
                                    required
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
                                    defaultValue=""
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    <option value="" disabled>-- Select Pipeline Stage --</option>
                                    {stages.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.pipeline_stage_id} />
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
                                    defaultValue=""
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    <option value="" disabled>-- Select Owner --</option>
                                    {users.map((u) => (
                                        <option key={u.id} value={u.id}>
                                            {u.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.assigned_to} />
                            </div>

                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-800">
                                <Button variant="ghost" asChild>
                                    <Link href={indexOppRoute()}>Cancel</Link>
                                </Button>
                                <Button disabled={processing}>Save Opportunity</Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

OpportunityCreate.layout = {
    breadcrumbs: [
        {
            title: 'Opportunities',
            href: indexOppRoute(),
        },
        {
            title: 'Create',
        },
    ],
};
