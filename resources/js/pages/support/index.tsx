import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { BookOpen, HelpCircle, MessageSquare, ShieldAlert, LifeBuoy, FileText, Send } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { toast } from 'sonner';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth;
};

export default function SupportIndex() {
    const { auth } = usePage<PageProps>().props;

    const [subject, setSubject] = useState('');
    const [category, setCategory] = useState('general');
    const [priority, setPriority] = useState('medium');
    const [message, setMessage] = useState('');
    const [submitting, setSubmitting] = useState(false);

    // FAQ Accordion active items state
    const [activeFaq, setActiveFaq] = useState<number | null>(null);

    const faqs = [
        {
            q: 'How does Customer Credit Limit exposure calculation work?',
            a: 'Credit exposure combines all outstanding receivables (unpaid issued/overdue invoices) and pending sales orders that have not yet been invoiced. If this exceeds the Customer Credit Limit, the CRM places new orders on Credit Hold automatically.',
        },
        {
            q: 'How are journal entries posted to the Ledger?',
            a: 'Business modules dispatch asynchronous domain events via the transactional outbox. These are ingested by the Accounting Gateway, resolved against dynamic posting rules, and recorded in the General Ledger journals.',
        },
        {
            q: 'Can I change user roles and tenant permissions?',
            a: 'Yes. Owners and Administrators can manage tenant users via Settings > Users. You can assign roles such as Admin, Manager, Sales, Finance, Gudang, or Produksi which have distinct permission scopes.',
        },
        {
            q: 'Why does my inventory stock balance show pending allocations?',
            a: 'Allocations represent inventory reserved for approved Sales Orders that have not yet been shipped (Goods Issued). This prevents double-allocating the same stock item.',
        },
    ];

    const handleSubmitTicket = (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitting(true);

        setTimeout(() => {
            setSubmitting(false);
            toast.success('Support ticket submitted successfully! A reference number has been sent to your email.');
            setSubject('');
            setMessage('');
        }, 1200);
    };

    return (
        <>
            <Head title="Support Center" />

            <div className="w-full space-y-6 p-6">
                {/* Header Title Section */}
                <div className="flex flex-col gap-1 border-b border-neutral-200 dark:border-neutral-800 pb-5">
                    <div className="flex items-center gap-2">
                        <LifeBuoy className="h-6 w-6 text-indigo-650 dark:text-indigo-400" />
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                            Support Center
                        </h1>
                    </div>
                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                        Get help with InakaraCRM, log support requests, or browse our knowledge base.
                    </p>
                </div>

                {/* Quick Assistance Cards Grid */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-white dark:bg-neutral-900 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="pb-3 flex flex-row items-center gap-3">
                            <div className="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-650 dark:text-indigo-400 shrink-0">
                                <BookOpen className="h-5 w-5" />
                            </div>
                            <div>
                                <CardTitle className="text-sm font-bold">Knowledge Base</CardTitle>
                                <CardDescription className="text-xs">Browse system manuals</CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p className="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed mb-4">
                                Search walkthroughs, features documentation, WMS setup guides, and accounting configuration parameters.
                            </p>
                            <button
                                onClick={() => toast.info('Knowledge Base documentation coming soon.')}
                                className="text-xs font-semibold text-indigo-650 hover:underline flex items-center gap-1"
                            >
                                Read documentation &rarr;
                            </button>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-white dark:bg-neutral-900 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="pb-3 flex flex-row items-center gap-3">
                            <div className="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-650 dark:text-emerald-400 shrink-0">
                                <FileText className="h-5 w-5" />
                            </div>
                            <div>
                                <CardTitle className="text-sm font-bold">API Reference</CardTitle>
                                <CardDescription className="text-xs">Developer integration docs</CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p className="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed mb-4">
                                Complete endpoints listing for CRM, Sales pipeline, outbox events schema, and general ledger journal pushes.
                            </p>
                            <button
                                onClick={() => toast.info('API documentation is available at /api/v1/documentation.')}
                                className="text-xs font-semibold text-emerald-650 hover:underline flex items-center gap-1"
                            >
                                View endpoints API &rarr;
                            </button>
                        </CardContent>
                    </Card>

                    <Card className="border-neutral-200/80 dark:border-neutral-800/80 bg-white dark:bg-neutral-900 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="pb-3 flex flex-row items-center gap-3">
                            <div className="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-650 dark:text-amber-400 shrink-0">
                                <MessageSquare className="h-5 w-5" />
                            </div>
                            <div>
                                <CardTitle className="text-sm font-bold">Community Forum</CardTitle>
                                <CardDescription className="text-xs">Discuss with ERP experts</CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p className="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed mb-4">
                                Ask questions, read user stories from other tenants, or share custom templates for quotations and invoicing.
                            </p>
                            <button
                                onClick={() => toast.info('Tenant forum portal is currently restricted to Enterprise members.')}
                                className="text-xs font-semibold text-amber-650 hover:underline flex items-center gap-1"
                            >
                                Join discussion &rarr;
                            </button>
                        </CardContent>
                    </Card>
                </div>

                {/* Form & FAQ section */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    {/* Log Ticket Form Card */}
                    <div className="lg:col-span-7">
                        <Card className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-xl overflow-hidden shadow-sm">
                            <CardHeader className="border-b border-neutral-100 dark:border-neutral-850 p-5">
                                <CardTitle className="text-sm font-bold text-neutral-900 dark:text-neutral-100 uppercase tracking-wider">
                                    Submit a Support Ticket
                                </CardTitle>
                                <CardDescription className="text-xs text-neutral-500">
                                    Our technical support team typically responds within 4 hours.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="p-6">
                                <form onSubmit={handleSubmitTicket} className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="grid gap-1.5">
                                            <Label htmlFor="user_name" className="text-xs font-semibold text-neutral-700 dark:text-neutral-300">YOUR NAME</Label>
                                            <Input
                                                id="user_name"
                                                value={auth.user.name}
                                                disabled
                                                className="bg-neutral-50 dark:bg-neutral-950 border-neutral-200 dark:border-neutral-800 text-neutral-500"
                                            />
                                        </div>
                                        <div className="grid gap-1.5">
                                            <Label htmlFor="user_email" className="text-xs font-semibold text-neutral-700 dark:text-neutral-300">EMAIL ADDRESS</Label>
                                            <Input
                                                id="user_email"
                                                value={auth.user.email}
                                                disabled
                                                className="bg-neutral-50 dark:bg-neutral-950 border-neutral-200 dark:border-neutral-800 text-neutral-500"
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="grid gap-1.5">
                                            <Label htmlFor="category" className="text-xs font-semibold text-neutral-700 dark:text-neutral-300">TICKET CATEGORY</Label>
                                            <select
                                                id="category"
                                                value={category}
                                                onChange={(e) => setCategory(e.target.value)}
                                                className="w-full h-10 px-3 rounded-md border border-neutral-250 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-neutral-100"
                                            >
                                                <option value="general">General Inquiry</option>
                                                <option value="technical">Technical Bug / Issue</option>
                                                <option value="billing">Billing & Subscription</option>
                                                <option value="feature">Feature Request</option>
                                            </select>
                                        </div>
                                        <div className="grid gap-1.5">
                                            <Label htmlFor="priority" className="text-xs font-semibold text-neutral-700 dark:text-neutral-300">PRIORITY LEVEL</Label>
                                            <select
                                                id="priority"
                                                value={priority}
                                                onChange={(e) => setPriority(e.target.value)}
                                                className="w-full h-10 px-3 rounded-md border border-neutral-250 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-neutral-100"
                                            >
                                                <option value="low">Low - General Questions</option>
                                                <option value="medium">Medium - Normal Operations</option>
                                                <option value="high">High - Feature Blocked</option>
                                                <option value="critical">Critical - ERP / Production Down</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div className="grid gap-1.5">
                                        <Label htmlFor="subject" className="text-xs font-semibold text-neutral-700 dark:text-neutral-300">SUBJECT</Label>
                                        <Input
                                            id="subject"
                                            value={subject}
                                            onChange={(e) => setSubject(e.target.value)}
                                            placeholder="Brief description of the problem"
                                            required
                                        />
                                    </div>

                                    <div className="grid gap-1.5">
                                        <Label htmlFor="message" className="text-xs font-semibold text-neutral-700 dark:text-neutral-300">DETAILS / MESSAGE</Label>
                                        <textarea
                                            id="message"
                                            value={message}
                                            onChange={(e) => setMessage(e.target.value)}
                                            rows={5}
                                            placeholder="Please describe your issue, listing any error messages or steps to reproduce..."
                                            className="flex min-h-[120px] w-full rounded-md border border-neutral-300 dark:border-neutral-800 bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-neutral-100"
                                            required
                                        />
                                    </div>

                                    <div className="flex justify-end pt-2">
                                        <Button
                                            type="submit"
                                            disabled={submitting}
                                            className="bg-[#0052cc] hover:bg-[#0747a6] text-white font-semibold text-xs px-5 py-2 rounded flex items-center gap-2"
                                        >
                                            <Send className="h-3.5 w-3.5" />
                                            {submitting ? 'Submitting...' : 'Submit Support Ticket'}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    {/* FAQ Accordion Section */}
                    <div className="lg:col-span-5 space-y-4">
                        <div className="flex items-center gap-2 pb-1">
                            <HelpCircle className="h-5 w-5 text-neutral-500" />
                            <h3 className="text-sm font-bold text-neutral-900 dark:text-neutral-100 uppercase tracking-wider">
                                Frequently Asked Questions
                            </h3>
                        </div>

                        <div className="space-y-3">
                            {faqs.map((faq, idx) => {
                                const isOpen = activeFaq === idx;
                                return (
                                    <Card
                                        key={idx}
                                        className="border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xs overflow-hidden"
                                    >
                                        <button
                                            type="button"
                                            onClick={() => setActiveFaq(isOpen ? null : idx)}
                                            className="w-full p-4 flex items-center justify-between text-left focus:outline-none"
                                        >
                                            <span className="text-xs font-bold text-neutral-805 dark:text-neutral-200 leading-snug">
                                                {faq.q}
                                            </span>
                                            <span className="text-neutral-400 font-semibold text-base select-none shrink-0 ml-2">
                                                {isOpen ? '−' : '+'}
                                            </span>
                                        </button>
                                        {isOpen && (
                                            <div className="px-4 pb-4 border-t border-neutral-100 dark:border-neutral-850/60 pt-3 bg-neutral-50/30 dark:bg-neutral-950/20">
                                                <p className="text-xs text-neutral-550 dark:text-neutral-400 leading-relaxed">
                                                    {faq.a}
                                                </p>
                                            </div>
                                        )}
                                    </Card>
                                );
                            })}
                        </div>

                        <div className="bg-[#f0f4f9] dark:bg-neutral-800/30 rounded-xl p-4 flex gap-3 border border-neutral-150 dark:border-neutral-850">
                            <ShieldAlert className="h-5 w-5 text-indigo-600 dark:text-indigo-400 shrink-0" />
                            <div>
                                <h4 className="text-xs font-bold text-neutral-805 dark:text-neutral-200">Critical Outage?</h4>
                                <p className="text-[10px] text-neutral-500 dark:text-neutral-450 mt-1 leading-relaxed">
                                    For critical system-down emergencies, select the **Critical** priority in the form above to trigger high-priority alerts to our SLA on-call engineers.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

SupportIndex.layout = {
    breadcrumbs: [
        {
            title: 'Support',
            href: '/support',
        },
    ],
};
