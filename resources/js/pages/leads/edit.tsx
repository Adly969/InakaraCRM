import { Form, Head, usePage } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { useState } from 'react';
import LeadController from '@/actions/App/Http/Controllers/LeadController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index as indexLeadsRoute } from '@/routes/leads';
import { UserCheck, Building2, Phone, Mail, Globe, Briefcase, Award, Flame, User } from 'lucide-react';
import type { Lead, LeadStatusType } from '@/types';

interface UserOption {
    id: number;
    name: string;
}

interface PageProps extends Record<string, any> {
    lead: Lead;
    users: UserOption[];
}

export default function LeadEdit() {
    const { lead, users } = usePage<PageProps>().props;

    // Track status locally to reactively render disqualification field
    const [status, setStatus] = useState<LeadStatusType>(lead.status);

    // Resolve initial assignee ID
    const initialAssignee = typeof lead.assigned_to === 'object' && lead.assigned_to !== null
        ? lead.assigned_to.id
        : (lead.assigned_to ?? '');

    const selectClassName = "w-full rounded-xl border border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800 pl-3.5 pr-10 py-2.5 text-sm font-medium text-neutral-900 dark:text-neutral-100 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-size-[1.25rem_1.25rem] bg-no-repeat bg-position-[right_0.85rem_center] focus:outline-none focus:ring-2 focus:ring-neutral-400 cursor-pointer shadow-xs";

    return (
        <>
            <Head title={`Edit Lead - ${lead.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between border-b border-neutral-200 dark:border-neutral-800 pb-3">
                    <div className="flex items-center gap-2">
                        <span className="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            LEAD #{lead.id}
                        </span>
                        <Heading
                            title="Edit Data Prospek Sales (Lead)"
                            description={`Perbarui profil kontak & kualifikasi prospek untuk ${lead.name}.`}
                        />
                    </div>
                    <Button variant="outline" asChild className="border-neutral-300 dark:border-neutral-700 font-semibold rounded-xl">
                        <Link href={indexLeadsRoute()}>Kembali</Link>
                    </Button>
                </div>

                <Form
                    {...LeadController.update.form({ lead: lead.id })}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <div className="space-y-6">
                            {/* CARD 1: Profil & Identitas Prospek */}
                            <Card className="border border-neutral-200 dark:border-neutral-800 shadow-sm rounded-2xl bg-white dark:bg-neutral-900 overflow-hidden">
                                <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 py-4 px-6">
                                    <div className="flex items-center gap-2.5">
                                        <div className="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200/60 dark:border-amber-800/60">
                                            <UserCheck className="h-4 w-4" />
                                        </div>
                                        <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">
                                            Informasi Profil & Kontak Prospek
                                        </CardTitle>
                                    </div>
                                </CardHeader>
                                <CardContent className="p-6 space-y-5">
                                    <div className="grid gap-5 sm:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="name" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                                Nama Lengkap Prospek <span className="text-rose-500">*</span>
                                            </Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                required
                                                defaultValue={lead.name}
                                                placeholder="Contoh: Bpk. Hendra Wijaya"
                                                className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800"
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="company_name" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                                Nama Perusahaan / Perusahaan Resort
                                            </Label>
                                            <Input
                                                id="company_name"
                                                name="company_name"
                                                defaultValue={lead.company_name ?? ''}
                                                placeholder="Contoh: PT Seminyak Luxury Villa"
                                                className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800"
                                            />
                                            <InputError message={errors.company_name} />
                                        </div>
                                    </div>

                                    <div className="grid gap-5 sm:grid-cols-3">
                                        <div className="space-y-2">
                                            <Label htmlFor="job_title" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Jabatan / Posisi</Label>
                                            <Input
                                                id="job_title"
                                                name="job_title"
                                                defaultValue={lead.job_title ?? ''}
                                                placeholder="Contoh: Procurement Manager"
                                                className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800"
                                            />
                                            <InputError message={errors.job_title} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="email" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Alamat Email</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                name="email"
                                                defaultValue={lead.email ?? ''}
                                                placeholder="hendra@seminyakvilla.com"
                                                className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800"
                                            />
                                            <InputError message={errors.email} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="phone" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">No. WhatsApp / Telepon</Label>
                                            <Input
                                                id="phone"
                                                name="phone"
                                                defaultValue={lead.phone ?? ''}
                                                placeholder="+628123456789"
                                                className="text-sm font-medium h-10 rounded-xl border-neutral-300/80 dark:border-neutral-750 bg-neutral-50/50 dark:bg-neutral-800"
                                            />
                                            <InputError message={errors.phone} />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* CARD 2: Kualifikasi Sales & Status */}
                            <Card className="border border-neutral-200 dark:border-neutral-800 shadow-sm rounded-2xl bg-white dark:bg-neutral-900 overflow-hidden">
                                <CardHeader className="border-b border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-50/60 dark:bg-neutral-850/60 py-4 px-6">
                                    <div className="flex items-center gap-2.5">
                                        <div className="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center border border-sky-200/60 dark:border-sky-800/60">
                                            <Flame className="h-4 w-4" />
                                        </div>
                                        <CardTitle className="text-base font-bold text-neutral-900 dark:text-neutral-100">
                                            Kualifikasi Pipeline & Status Sales
                                        </CardTitle>
                                    </div>
                                </CardHeader>
                                <CardContent className="p-6 space-y-5">
                                    <div className="grid gap-5 sm:grid-cols-3">
                                        <div className="space-y-2">
                                            <Label htmlFor="source" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Sumber Prospek <span className="text-rose-500">*</span></Label>
                                            <select
                                                id="source"
                                                name="source"
                                                required
                                                defaultValue={lead.source}
                                                className={selectClassName}
                                            >
                                                <option value="referral">Rekomendasi (Referral)</option>
                                                <option value="marketing">Kampanye Marketing</option>
                                                <option value="walk_in">Kunjungan Showroom</option>
                                                <option value="phone">Telepon / WhatsApp Direct</option>
                                                <option value="digital">Website & Media Sosial</option>
                                                <option value="event">Pameran & Hospitality Event</option>
                                            </select>
                                            <InputError message={errors.source} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="priority" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Prioritas Deal</Label>
                                            <select
                                                id="priority"
                                                name="priority"
                                                defaultValue={lead.priority}
                                                className={selectClassName}
                                            >
                                                <option value="low">Rendah (Low)</option>
                                                <option value="medium">Sedang (Medium)</option>
                                                <option value="high">Tinggi (High)</option>
                                            </select>
                                            <InputError message={errors.priority} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="heat_score" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Skor Kebutuhan (Heat)</Label>
                                            <select
                                                id="heat_score"
                                                name="heat_score"
                                                defaultValue={lead.heat_score}
                                                className={selectClassName}
                                            >
                                                <option value="cold">Dingin (Cold - Baru Bertanya)</option>
                                                <option value="warm">Hangat (Warm - Minta Katalog/Harga)</option>
                                                <option value="hot">Panas (Hot - Siap Order)</option>
                                            </select>
                                            <InputError message={errors.heat_score} />
                                        </div>
                                    </div>

                                    <div className="grid gap-5 sm:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="status" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Status Prospek Sales <span className="text-rose-500">*</span></Label>
                                            <select
                                                id="status"
                                                name="status"
                                                required
                                                defaultValue={lead.status}
                                                onChange={(e) => setStatus(e.target.value as LeadStatusType)}
                                                className={selectClassName}
                                            >
                                                <option value="new">Baru (New)</option>
                                                <option value="assigned">Ditugaskan ke Sales (Assigned)</option>
                                                <option value="contacted">Sudah Dihubungi (Contacted)</option>
                                                <option value="qualified">Kualifikasi Sukses (Qualified)</option>
                                                <option value="converted">Konversi ke Pelanggan (Converted)</option>
                                                <option value="disqualified">Dibatalkan / Disqualified</option>
                                            </select>
                                            <InputError message={errors.status} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="assigned_to" className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Sales Representative / PIC</Label>
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

                                    {status === 'disqualified' && (
                                        <div className="space-y-2 pt-2">
                                            <Label htmlFor="disqualification_reason" className="text-sm font-semibold text-rose-600 dark:text-rose-400">Alasan Pembatalan Prospek (Disqualification Reason)</Label>
                                            <textarea
                                                id="disqualification_reason"
                                                name="disqualification_reason"
                                                required
                                                defaultValue={lead.disqualification_reason ?? ''}
                                                placeholder="Jelaskan alasan prospek ini tidak dapat dilanjutkan..."
                                                className="w-full rounded-xl border border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20 p-3.5 text-sm text-neutral-800 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-rose-400"
                                            />
                                            <InputError message={errors.disqualification_reason} />
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-800">
                                <Button variant="outline" asChild className="border-neutral-300 dark:border-neutral-700 font-semibold rounded-xl">
                                    <Link href={indexLeadsRoute()}>Batal</Link>
                                </Button>
                                <Button disabled={processing} className="bg-neutral-900 hover:bg-neutral-800 text-white dark:bg-neutral-100 dark:text-neutral-900 dark:hover:bg-neutral-200 font-bold py-2.5 px-5 text-sm rounded-xl shadow-sm">
                                    Simpan Perubahan Prospek
                                </Button>
                            </div>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

LeadEdit.layout = {
    breadcrumbs: [
        {
            title: 'Leads',
            href: indexLeadsRoute(),
        },
        {
            title: 'Edit',
        },
    ],
};
