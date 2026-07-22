<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoidInvoiceRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class InvoiceApprovalController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Approve the specified draft invoice.
     */
    public function approve(Invoice $invoice): RedirectResponse
    {
        Gate::authorize('approve', $invoice);

        $this->invoiceService->approve($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice has been approved.');
    }

    /**
     * Issue the approved invoice.
     */
    public function issue(Invoice $invoice): RedirectResponse
    {
        Gate::authorize('issue', $invoice);

        $this->invoiceService->issue($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice has been issued with reference number.');
    }

    /**
     * Void the issued invoice.
     */
    public function void(Invoice $invoice, VoidInvoiceRequest $request): RedirectResponse
    {
        Gate::authorize('void', $invoice);

        $this->invoiceService->void($invoice, $request->input('reason'));

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice has been voided.');
    }
}
