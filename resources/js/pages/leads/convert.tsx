import { Form, Head, usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import LeadWorkflowController from '@/actions/App/Http/Controllers/LeadWorkflowController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as indexLeadsRoute } from '@/routes/leads';
import type { Lead } from '@/types';

interface CustomerOption {
    id: number;
    name: string;
    company_name: string | null;
}

interface PageProps extends Record<string, any> {
    lead: Lead;
    customers: CustomerOption[];
}

export default function LeadConvert() {
    const { lead, customers } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Convert Lead to Opportunity" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 max-w-xl mx-auto">
                <div className="flex flex-col gap-1">
                    <Heading
                        title="Convert Lead"
                        description={`Create an opportunity deal from lead: ${lead.name}.`}
                    />
                </div>

                <Form
                    {...LeadWorkflowController.convert.form({ lead: lead.id })}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-1">
                                <Label htmlFor="customer_id">Select Customer</Label>
                                <select
                                    id="customer_id"
                                    name="customer_id"
                                    required
                                    defaultValue=""
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    <option value="" disabled>-- Select Linked Customer Account --</option>
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
                                    defaultValue={`${lead.company_name || lead.name} - Deal`}
                                    placeholder="e.g. Acme Corp - Core Software Licensing"
                                    className="mt-1"
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="deal_value">Estimated Deal Value</Label>
                                <Input
                                    id="deal_value"
                                    type="number"
                                    name="deal_value"
                                    required
                                    step="0.01"
                                    min="0"
                                    placeholder="e.g. 50000000.00"
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

                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-800">
                                <Button variant="ghost" asChild>
                                    <Link href={indexLeadsRoute()}>Cancel</Link>
                                </Button>
                                <Button disabled={processing}>Convert to Opportunity</Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

LeadConvert.layout = {
    breadcrumbs: [
        {
            title: 'Leads',
            href: indexLeadsRoute(),
        },
        {
            title: 'Convert',
        },
    ],
};
