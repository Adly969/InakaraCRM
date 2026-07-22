import { Form, Head, usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import LeadController from '@/actions/App/Http/Controllers/LeadController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as indexLeadsRoute } from '@/routes/leads';

interface UserOption {
    id: number;
    name: string;
}

interface PageProps extends Record<string, any> {
    users: UserOption[];
}

export default function LeadCreate() {
    const { users } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Create Lead" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex flex-col gap-1">
                    <Heading
                        title="Create Lead"
                        description="Add a new customer lead to the pipeline."
                    />
                </div>

                <Form
                    {...LeadController.store.form()}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-1">
                                <Label htmlFor="name">Lead Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    placeholder="e.g. John Doe"
                                    className="mt-1"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="company_name">Company Name</Label>
                                <Input
                                    id="company_name"
                                    name="company_name"
                                    placeholder="e.g. Acme Corp"
                                    className="mt-1"
                                />
                                <InputError message={errors.company_name} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="job_title">Job Title</Label>
                                <Input
                                    id="job_title"
                                    name="job_title"
                                    placeholder="e.g. Sales Manager"
                                    className="mt-1"
                                />
                                <InputError message={errors.job_title} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="email">Email Address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    placeholder="john.doe@example.com"
                                    className="mt-1"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="phone">Phone Number</Label>
                                <Input
                                    id="phone"
                                    name="phone"
                                    placeholder="+62812345678"
                                    className="mt-1"
                                />
                                <InputError message={errors.phone} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="website">Website</Label>
                                <Input
                                    id="website"
                                    name="website"
                                    placeholder="https://example.com"
                                    className="mt-1"
                                />
                                <InputError message={errors.website} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="source">Lead Source</Label>
                                <select
                                    id="source"
                                    name="source"
                                    required
                                    defaultValue="marketing"
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    <option value="referral">Referral</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="walk_in">Walk-in</option>
                                    <option value="phone">Phone</option>
                                    <option value="digital">Digital</option>
                                    <option value="event">Event</option>
                                </select>
                                <InputError message={errors.source} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="campaign_source">Campaign Source</Label>
                                <Input
                                    id="campaign_source"
                                    name="campaign_source"
                                    placeholder="e.g. Summer Promo 2026"
                                    className="mt-1"
                                />
                                <InputError message={errors.campaign_source} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="priority">Priority</Label>
                                <select
                                    id="priority"
                                    name="priority"
                                    defaultValue="medium"
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                                <InputError message={errors.priority} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="heat_score">Heat Score</Label>
                                <select
                                    id="heat_score"
                                    name="heat_score"
                                    defaultValue="cold"
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    <option value="cold">Cold</option>
                                    <option value="warm">Warm</option>
                                    <option value="hot">Hot</option>
                                </select>
                                <InputError message={errors.heat_score} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="status">Initial Status</Label>
                                <select
                                    id="status"
                                    name="status"
                                    required
                                    defaultValue="new"
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    <option value="new">New</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="contacted">Contacted</option>
                                    <option value="qualified">Qualified</option>
                                    <option value="converted">Converted</option>
                                    <option value="disqualified">Disqualified</option>
                                </select>
                                <InputError message={errors.status} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="assigned_to">Assignee</Label>
                                <select
                                    id="assigned_to"
                                    name="assigned_to"
                                    defaultValue=""
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    <option value="">Unassigned</option>
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
                                    <Link href={indexLeadsRoute()}>Cancel</Link>
                                </Button>
                                <Button disabled={processing}>Save Lead</Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

LeadCreate.layout = {
    breadcrumbs: [
        {
            title: 'Leads',
            href: indexLeadsRoute(),
        },
        {
            title: 'Create',
        },
    ],
};
