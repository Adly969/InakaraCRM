import { Form, Head, usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as indexCustomersRoute } from '@/routes/customers';

interface UserOption {
    id: number;
    name: string;
}

interface PageProps extends Record<string, any> {
    users: UserOption[];
}

export default function CustomerCreate() {
    const { users } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Create Customer" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex flex-col gap-1">
                    <Heading
                        title="Create Customer"
                        description="Add a new customer profile manually."
                    />
                </div>

                <Form
                    {...CustomerController.store.form()}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-1">
                                <Label htmlFor="name">Customer Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    placeholder="e.g. Jane Smith"
                                    className="mt-1"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="company_name">Company Name</Label>
                                <Input
                                    id="company_name"
                                    name="company_name"
                                    placeholder="e.g. Acme Inc"
                                    className="mt-1"
                                />
                                <InputError message={errors.company_name} />
                            </div>

                            <div className="grid gap-1">
                                <Label htmlFor="email">Email Address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    placeholder="jane@acme.com"
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
                                <Label htmlFor="type">Customer Type</Label>
                                <select
                                    id="type"
                                    name="type"
                                    required
                                    defaultValue="individual"
                                    className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                >
                                    <option value="individual">Individual</option>
                                    <option value="organization">Organization</option>
                                </select>
                                <InputError message={errors.type} />
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

                            <div className="grid gap-1">
                                <Label htmlFor="notes">Notes / Context</Label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows={4}
                                    placeholder="Add any customer history or context here..."
                                    className="border-input flex w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] mt-1 dark:bg-neutral-950"
                                />
                                <InputError message={errors.notes} />
                            </div>

                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-800">
                                <Button variant="ghost" asChild>
                                    <Link href={indexCustomersRoute()}>Cancel</Link>
                                </Button>
                                <Button disabled={processing}>Save Customer</Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

CustomerCreate.layout = {
    breadcrumbs: [
        {
            title: 'Customers',
            href: indexCustomersRoute(),
        },
        {
            title: 'Create',
        },
    ],
};
