import { Form, Head, usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index as indexCustomersRoute } from '@/routes/customers';
import { UserCheck, Building2, Phone, Mail, FileText, User } from 'lucide-react';
import type { Customer } from '@/types';

interface UserOption {
    id: number;
    name: string;
}

interface PageProps extends Record<string, any> {
    customer: Customer;
    users: UserOption[];
}

export default function CustomerEdit() {
    const { customer, users } = usePage<PageProps>().props;

    // Resolve initial assignee ID
    const initialAssignee = typeof customer.assigned_to === 'object' && customer.assigned_to !== null
        ? (customer.assigned_to as any).id
        : (customer.assigned_to ?? '');

    const selectClassName = "w-full rounded-xl border border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800 pl-3.5 pr-10 py-2.5 text-sm font-medium text-neutral-900 dark:text-neutral-100 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-size-[1.25rem_1.25rem] bg-no-repeat bg-position-[right_0.85rem_center] focus:outline-none focus:ring-2 focus:ring-neutral-400 cursor-pointer shadow-xs";

    return (
        <>
            <Head title={`Edit Pelanggan - ${customer.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between border-b border-neutral-200 dark:border-neutral-800 pb-3">
                    <div className="flex items-center gap-2">
                        <span className="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-sky-100 dark:bg-sky-950/80 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                            CUST #{customer.id}
                        </span>
                        <Heading
                            title="Edit Profile Pelanggan (Customer)"
                            description={`Perbarui informasi kontak, kategori entitas, & catatan riwayat pelanggan ${customer.name}.`}
                        />
                    </div>
                    <Button variant="outline" asChild className="border-neutral-300 dark:border-neutral-700 font-semibold rounded-xl">
                        <Link href={indexCustomersRoute()}>Kembali</Link>
                    </Button>
                </div>

                <Form
                    {...CustomerController.update.form({ customer: customer.id })}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <div className="space-y-6">
                            {/* CARD 1: Profil & Identitas Pelanggan */}
                            <Card className="border border-neutral-200 dark:border-neutral-800 shadow-sm rounded-2xl bg-white dark:bg-neutral-900 overflow-hidden">
                                <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 py-4 px-6">
                                    <div className="flex items-center gap-2.5">
                                        <div className="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center border border-sky-200/60 dark:border-sky-800/60">
                                            <UserCheck className="h-4 w-4" />
                                        </div>
                                        <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">
                                            Informasi Profil & Kontak Pelanggan
                                        </CardTitle>
                                    </div>
                                </CardHeader>
                                <CardContent className="p-6 space-y-5">
                                    <div className="grid gap-5 sm:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="name" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                                Nama Lengkap Pelanggan / PIC <span className="text-rose-500">*</span>
                                            </Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                required
                                                defaultValue={customer.name}
                                                placeholder="Contoh: Ibu Siska Melati"
                                                className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800"
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="company_name" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                                Nama Perusahaan / Resort Hotel
                                            </Label>
                                            <Input
                                                id="company_name"
                                                name="company_name"
                                                defaultValue={customer.company_name ?? ''}
                                                placeholder="Contoh: PT Mulia Resort Nusa Dua"
                                                className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800"
                                            />
                                            <InputError message={errors.company_name} />
                                        </div>
                                    </div>

                                    <div className="grid gap-5 sm:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="email" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Alamat Email</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                name="email"
                                                defaultValue={customer.email ?? ''}
                                                placeholder="siska@muliaresort.com"
                                                className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800"
                                            />
                                            <InputError message={errors.email} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="phone" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">No. WhatsApp / Telepon</Label>
                                            <Input
                                                id="phone"
                                                name="phone"
                                                defaultValue={customer.phone ?? ''}
                                                placeholder="+628198765432"
                                                className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800"
                                            />
                                            <InputError message={errors.phone} />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* CARD 2: Kategori & Status Akun */}
                            <Card className="border border-neutral-200 dark:border-neutral-800 shadow-sm rounded-2xl bg-white dark:bg-neutral-900 overflow-hidden">
                                <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 py-4 px-6">
                                    <div className="flex items-center gap-2.5">
                                        <div className="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200/60 dark:border-emerald-800/60">
                                            <Building2 className="h-4 w-4" />
                                        </div>
                                        <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">
                                            Klasifikasi & Penugasan Sales
                                        </CardTitle>
                                    </div>
                                </CardHeader>
                                <CardContent className="p-6 space-y-5">
                                    <div className="grid gap-5 sm:grid-cols-3">
                                        <div className="space-y-2">
                                            <Label htmlFor="type" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Tipe Pelanggan <span className="text-rose-500">*</span></Label>
                                            <select
                                                id="type"
                                                name="type"
                                                required
                                                defaultValue={customer.type}
                                                className={selectClassName}
                                            >
                                                <option value="individual">Perorangan (Individual)</option>
                                                <option value="organization">Perusahaan / Badan Hukum (Organization)</option>
                                            </select>
                                            <InputError message={errors.type} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="status" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Status Akun <span className="text-rose-500">*</span></Label>
                                            <select
                                                id="status"
                                                name="status"
                                                required
                                                defaultValue={customer.status}
                                                className={selectClassName}
                                            >
                                                <option value="active">Aktif (Active)</option>
                                                <option value="inactive">Non-Aktif (Inactive)</option>
                                                <option value="blacklisted">Blacklisted (Bermasalah)</option>
                                            </select>
                                            <InputError message={errors.status} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="assigned_to" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Account Executive / PIC</Label>
                                            <select
                                                id="assigned_to"
                                                name="assigned_to"
                                                defaultValue={initialAssignee}
                                                className={selectClassName}
                                            >
                                                <option value="">Belum Ditugaskan</option>
                                                {users.map((u) => (
                                                    <option key={u.id} value={u.id}>
                                                        {u.name}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError message={errors.assigned_to} />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="notes" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Catatan Khusus Pelanggan</Label>
                                        <textarea
                                            id="notes"
                                            name="notes"
                                            rows={3}
                                            defaultValue={customer.notes ?? ''}
                                            placeholder="Tambahkan riwayat transaksi, preferensi material furnitur..."
                                            className="w-full rounded-xl border border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800 p-3.5 text-sm text-neutral-800 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-neutral-400"
                                        />
                                        <InputError message={errors.notes} />
                                    </div>
                                </CardContent>
                            </Card>

                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-800">
                                <Button variant="outline" asChild className="border-neutral-300 dark:border-neutral-700 font-semibold rounded-xl">
                                    <Link href={indexCustomersRoute()}>Batal</Link>
                                </Button>
                                <Button disabled={processing} className="bg-neutral-900 hover:bg-neutral-800 text-white dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-200 font-bold py-2.5 px-5 text-sm rounded-xl shadow-sm">
                                    Simpan Perubahan Pelanggan
                                </Button>
                            </div>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

CustomerEdit.layout = {
    breadcrumbs: [
        {
            title: 'Customers',
            href: indexCustomersRoute(),
        },
        {
            title: 'Edit',
        },
    ],
};
