import { Head, Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import type { Payment } from '@/types/payment';
import { ArrowLeft, CheckCircle, FileText, Send, XCircle, Undo2, Upload, Trash2 } from 'lucide-react';
import React, { useState } from 'react';

interface Props {
    payment: Payment;
}

export default function ShowPayment({ payment }: Props) {
    const { can } = usePermission();
    const { auth } = usePage().props as any;

    const [showCancelModal, setShowCancelModal] = useState(false);
    const [cancelReason, setCancelReason] = useState('');
    const [showReverseModal, setShowReverseModal] = useState(false);
    const [reverseReason, setReverseReason] = useState('');

    const [fileToUpload, setFileToUpload] = useState<File | null>(null);

    const getStatusBadgeClass = (status: string) => {
        switch (status) {
            case 'draft':
                return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
            case 'submitted':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
            case 'verified':
                return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400';
            case 'approved':
                return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
            case 'posted':
                return 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400';
            case 'cancelled':
                return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
            case 'reversed':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            default:
                return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-400';
        }
    };

    const getStatusLabel = (status: string) => {
        return status.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
    };

    const formatCurrency = (val: number | string) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(val));
    };

    const handleWorkflowAction = (action: string) => {
        router.post(`/payments/${payment.id}/${action}`);
    };

    const handleCancel = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(`/payments/${payment.id}/cancel`, { reason: cancelReason }, {
            onSuccess: () => {
                setShowCancelModal(false);
                setCancelReason('');
            }
        });
    };

    const handleReverse = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(`/payments/${payment.id}/reverse`, { reason: reverseReason }, {
            onSuccess: () => {
                setShowReverseModal(false);
                setReverseReason('');
            }
        });
    };

    const handleFileUpload = (e: React.FormEvent) => {
        e.preventDefault();
        if (!fileToUpload) return;

        const formData = new FormData();
        formData.append('file', fileToUpload);

        router.post(`/payments/${payment.id}/attachments`, formData, {
            forceFormData: true,
            onSuccess: () => {
                setFileToUpload(null);
            }
        });
    };

    // Determine authorization dynamically based on matrix & creator values
    const canSubmit = payment.status === 'draft' && can('submit-payments');
    const canVerify = payment.status === 'submitted' && can('verify-payments');

    const isCreator = payment.created_by === auth.user.id;
    const canApprove = !isCreator && can('approve-payments-l1') && (
        (payment.status === 'verified' && can('approve-payments-l1')) ||
        (payment.status === 'finance_supervisor_approved' && can('approve-payments-l2') && payment.verified_by !== auth.user.id) ||
        (payment.status === 'finance_manager_approved' && can('approve-payments-l3') && payment.approved_by !== auth.user.id)
    );

    const canPost = payment.status === 'approved' && can('post-payments');
    const canCancel = ['draft', 'submitted', 'verified', 'approved'].includes(payment.status) && can('cancel-payments');
    const canReverse = payment.status === 'posted' && can('reverse-payments');

    return (
        <>
            <Head title={`Payment Details - ${payment.reference_no || 'Draft'}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header panel */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div className="flex items-center gap-4">
                        <Button asChild variant="ghost" size="icon" className="h-8 w-8 text-neutral-500 hover:text-neutral-900">
                            <Link href="/payments">
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50">
                                    Payment {payment.reference_no || <span className="text-neutral-400 italic">Draft</span>}
                                </h1>
                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${getStatusBadgeClass(payment.status)}`}>
                                    {getStatusLabel(payment.status)}
                                </span>
                            </div>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Customer: {payment.customer?.name} | Created By: {payment.creator?.name}
                            </p>
                        </div>
                    </div>

                    {/* Actions Widget */}
                    <div className="flex flex-wrap items-center gap-2">
                        {canSubmit && (
                            <Button onClick={() => handleWorkflowAction('submit')} className="bg-blue-600 hover:bg-blue-700 text-white">
                                <Send className="mr-2 h-4 w-4" /> Submit
                            </Button>
                        )}
                        {canVerify && (
                            <Button onClick={() => handleWorkflowAction('verify')} className="bg-indigo-600 hover:bg-indigo-700 text-white">
                                <CheckCircle className="mr-2 h-4 w-4" /> Verify
                            </Button>
                        )}
                        {canApprove && (
                            <Button onClick={() => handleWorkflowAction('approve')} className="bg-emerald-600 hover:bg-emerald-700 text-white">
                                <CheckCircle className="mr-2 h-4 w-4" /> Approve
                            </Button>
                        )}
                        {canPost && (
                            <Button onClick={() => handleWorkflowAction('post')} className="bg-teal-600 hover:bg-teal-700 text-white">
                                <CheckCircle className="mr-2 h-4 w-4" /> Post Payment
                            </Button>
                        )}
                        {canCancel && (
                            <Button onClick={() => setShowCancelModal(true)} variant="outline" className="text-red-600 border-red-200 hover:bg-red-50">
                                <XCircle className="mr-2 h-4 w-4" /> Cancel
                            </Button>
                        )}
                        {canReverse && (
                            <Button onClick={() => setShowReverseModal(true)} variant="outline" className="text-amber-600 border-amber-200 hover:bg-amber-50">
                                <Undo2 className="mr-2 h-4 w-4" /> Reverse Posting
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        {/* Allocations Table */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm overflow-hidden">
                            <CardHeader>
                                <CardTitle className="text-lg">Allocated Invoices</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {!payment.allocations || payment.allocations.length === 0 ? (
                                    <div className="p-6 text-center text-neutral-500">
                                        No allocations linked to this payment.
                                    </div>
                                ) : (
                                    <table className="w-full border-collapse text-left text-sm">
                                        <thead className="bg-neutral-50 dark:bg-neutral-900 text-xs font-semibold text-neutral-600 dark:text-neutral-400 border-b border-neutral-200 dark:border-neutral-800">
                                            <tr>
                                                <th className="px-6 py-3">Invoice Ref</th>
                                                <th className="px-6 py-3">Due Date</th>
                                                <th className="px-6 py-3">Invoice Total</th>
                                                <th className="px-6 py-3 text-right">Allocated Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800 bg-white dark:bg-neutral-950">
                                            {payment.allocations.map((alloc) => (
                                                <tr key={alloc.id} className="hover:bg-neutral-50/50 dark:hover:bg-neutral-900/30">
                                                    <td className="px-6 py-4 font-medium text-neutral-900 dark:text-neutral-100">
                                                        {alloc.invoice?.reference_no}
                                                    </td>
                                                    <td className="px-6 py-4">{alloc.invoice?.due_date}</td>
                                                    <td className="px-6 py-4">{formatCurrency(alloc.invoice?.total_amount || 0)}</td>
                                                    <td className="px-6 py-4 text-right font-semibold text-indigo-600 dark:text-indigo-400">
                                                        {formatCurrency(alloc.amount)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                )}
                            </CardContent>
                        </Card>

                        {/* Audit histories logs */}
                        {payment.histories && payment.histories.length > 0 && (
                            <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm overflow-hidden">
                                <CardHeader>
                                    <CardTitle className="text-lg">Allocation Adjustment Log</CardTitle>
                                </CardHeader>
                                <CardContent className="p-0">
                                    <table className="w-full border-collapse text-left text-sm">
                                        <thead className="bg-neutral-50 dark:bg-neutral-900 text-xs font-semibold text-neutral-600 dark:text-neutral-400 border-b border-neutral-200 dark:border-neutral-800">
                                            <tr>
                                                <th className="px-6 py-3">Modified By</th>
                                                <th className="px-6 py-3">Before</th>
                                                <th className="px-6 py-3">After</th>
                                                <th className="px-6 py-3">Reason</th>
                                                <th className="px-6 py-3">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-neutral-200 dark:divide-neutral-800 bg-white dark:bg-neutral-950">
                                            {payment.histories.map((hist) => (
                                                <tr key={hist.id}>
                                                    <td className="px-6 py-4">{hist.modifier?.name}</td>
                                                    <td className="px-6 py-4">{formatCurrency(hist.before_amount)}</td>
                                                    <td className="px-6 py-4 text-indigo-600">{formatCurrency(hist.after_amount)}</td>
                                                    <td className="px-6 py-4">{hist.reason}</td>
                                                    <td className="px-6 py-4">{new Date(hist.created_at).toLocaleString('id-ID')}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar metrics & uploads */}
                    <div className="space-y-6">
                        {/* Financial summary */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                            <CardHeader>
                                <CardTitle className="text-lg">Payment Summary</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex justify-between text-sm">
                                    <span className="text-neutral-500">Total Amount:</span>
                                    <span className="font-semibold text-neutral-900 dark:text-neutral-50">{formatCurrency(payment.amount)}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-neutral-500">Allocated to Invoices:</span>
                                    <span className="font-semibold text-indigo-600 dark:text-indigo-400">{formatCurrency(payment.allocated_amount)}</span>
                                </div>
                                <div className="flex justify-between text-sm border-b border-neutral-100 dark:border-neutral-900 pb-2">
                                    <span className="text-neutral-500">Unallocated Prepayment:</span>
                                    <span className="font-semibold text-amber-600 dark:text-amber-400">{formatCurrency(payment.unallocated_amount)}</span>
                                </div>

                                <div className="pt-2 text-xs text-neutral-500 space-y-1">
                                    <div>Method: <span className="font-medium text-neutral-900 dark:text-neutral-200">{payment.payment_method.toUpperCase()}</span></div>
                                    <div>Date: <span className="font-medium text-neutral-900 dark:text-neutral-200">{payment.payment_date}</span></div>
                                    {payment.transaction_ref && <div>Ref Code: <span className="font-medium text-neutral-900 dark:text-neutral-200">{payment.transaction_ref}</span></div>}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Attachments panel */}
                        <Card className="border-neutral-200/80 dark:border-neutral-800/80 shadow-sm">
                            <CardHeader>
                                <CardTitle className="text-lg">Payment Attachments</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {payment.status === 'draft' && (
                                    <form onSubmit={handleFileUpload} className="flex gap-2">
                                        <input
                                            type="file"
                                            onChange={(e) => setFileToUpload(e.target.files?.[0] || null)}
                                            className="text-xs w-full block"
                                            accept="image/*,application/pdf"
                                        />
                                        <Button type="submit" size="sm" className="bg-indigo-600 text-white">
                                            <Upload className="h-4 w-4" />
                                        </Button>
                                    </form>
                                )}

                                {!payment.attachments || payment.attachments.length === 0 ? (
                                    <div className="text-center text-xs text-neutral-500 py-2">
                                        No proof documents attached.
                                    </div>
                                ) : (
                                    <div className="space-y-2">
                                        {payment.attachments.map((attach) => (
                                            <div key={attach.id} className="flex items-center justify-between p-2 rounded bg-neutral-50 border text-xs dark:bg-neutral-900 dark:border-neutral-800">
                                                <div className="truncate pr-2">
                                                    <span className="font-medium text-neutral-900 dark:text-neutral-200">{attach.file_name}</span>
                                                    <div className="text-neutral-400">{(attach.file_size / 1024).toFixed(1)} KB</div>
                                                </div>
                                                <a href={`/storage/${attach.file_path}`} target="_blank" className="text-indigo-600 hover:underline">
                                                    View
                                                </a>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Reversal / Cancellation Modals */}
                {showCancelModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <Card className="w-full max-w-md">
                            <CardHeader>
                                <CardTitle>Cancel Payment</CardTitle>
                            </CardHeader>
                            <form onSubmit={handleCancel}>
                                <CardContent className="space-y-4">
                                    <p className="text-xs text-neutral-500">Provide the reason for cancelling this payment draft.</p>
                                    <textarea
                                        value={cancelReason}
                                        onChange={(e) => setCancelReason(e.target.value)}
                                        className="w-full rounded border p-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500"
                                        rows={3}
                                        required
                                    />
                                    <div className="flex justify-end gap-2">
                                        <Button type="button" variant="outline" onClick={() => setShowCancelModal(false)}>Close</Button>
                                        <Button type="submit" className="bg-red-600 text-white">Confirm Cancellation</Button>
                                    </div>
                                </CardContent>
                            </form>
                        </Card>
                    </div>
                )}

                {showReverseModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <Card className="w-full max-w-md">
                            <CardHeader>
                                <CardTitle>Reverse Posted Payment</CardTitle>
                            </CardHeader>
                            <form onSubmit={handleReverse}>
                                <CardContent className="space-y-4">
                                    <p className="text-xs text-neutral-500">Provide the reason for reversing this posted payment. This will restore outstanding balances on affected invoices.</p>
                                    <textarea
                                        value={reverseReason}
                                        onChange={(e) => setReverseReason(e.target.value)}
                                        className="w-full rounded border p-2 text-sm focus:outline-none focus:ring-1 focus:ring-amber-500"
                                        rows={3}
                                        required
                                    />
                                    <div className="flex justify-end gap-2">
                                        <Button type="button" variant="outline" onClick={() => setShowReverseModal(false)}>Close</Button>
                                        <Button type="submit" className="bg-amber-600 text-white">Confirm Reversal</Button>
                                    </div>
                                </CardContent>
                            </form>
                        </Card>
                    </div>
                )}
            </div>
        </>
    );
}
