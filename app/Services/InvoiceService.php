<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Events\InvoiceApproved;
use App\Events\InvoiceCreated;
use App\Events\InvoiceIssued;
use App\Events\InvoiceVoided;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceAdjustment;
use App\Models\InvoiceEvent;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class InvoiceService
{
    public function __construct(
        protected InvoiceCalculationService $calculationService,
        protected InvoiceValidationService $validationService,
        protected InvoiceNumberGenerator $numberGenerator
    ) {}

    /**
     * Create a new draft Invoice.
     */
    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            // 1. Calculate totals
            $totals = $this->calculationService->calculate($data);

            // 2. Perform business validation
            $this->validationService->validateCreditLimit((int) $data['customer_id'], (float) $totals['total_amount']);
            $this->validationService->validateQuantities($totals['items']);

            $customer = Customer::findOrFail($data['customer_id']);

            // 3. Create Invoice Header
            $invoice = Invoice::create([
                'sales_order_id' => $data['sales_order_id'] ?? null,
                'delivery_order_id' => $data['delivery_order_id'] ?? null,
                'customer_id' => $data['customer_id'],
                'company_id' => $data['company_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'status' => InvoiceStatus::Draft,
                'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'],
                'payment_term_code' => $data['payment_term_code'],
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'discount_amount' => $totals['discount_amount'],
                'adjustment_amount' => $totals['adjustment_amount'],
                'total_amount' => $totals['total_amount'],
                'outstanding_balance' => $totals['outstanding_balance'],
                'currency' => $data['currency'] ?? 'IDR',
                'exchange_rate' => $data['exchange_rate'] ?? 1.000000,
                'billing_address_snapshot' => [
                    'name' => $customer->name,
                    'address' => $data['billing_address'] ?? $customer->billing_address ?? '',
                ],
                'shipping_address_snapshot' => [
                    'name' => $customer->name,
                    'address' => $data['shipping_address'] ?? $customer->shipping_address ?? '',
                ],
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id() ?? 1,
            ]);

            // 4. Create Invoice Line Items
            foreach ($totals['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'sales_order_item_id' => $item['sales_order_item_id'] ?? null,
                    'delivery_order_item_id' => $item['delivery_order_item_id'] ?? null,
                    'sku' => $item['sku'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'pcs',
                    'unit_price' => $item['unit_price'],
                    'discount_percentage' => $item['discount_percentage'] ?? 0.00,
                    'discount_amount' => $item['discount_amount'],
                    'tax_percentage' => $item['tax_percentage'] ?? 0.00,
                    'tax_amount' => $item['tax_amount'],
                    'total_amount' => $item['total_amount'],
                    'item_details_snapshot' => $item['details_snapshot'] ?? null,
                    'sort_order' => $item['sort_order'] ?? 0,
                ]);
            }

            // 5. Create Adjustments
            foreach ($data['adjustments'] ?? [] as $adj) {
                InvoiceAdjustment::create([
                    'invoice_id' => $invoice->id,
                    'type' => $adj['type'],
                    'description' => $adj['description'],
                    'amount' => $adj['amount'],
                    'is_taxable' => $adj['is_taxable'] ?? false,
                ]);
            }

            // 6. Log system event
            InvoiceEvent::create([
                'invoice_id' => $invoice->id,
                'event_type' => 'created',
                'event_data' => [
                    'totals' => $totals,
                ],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);

            event(new InvoiceCreated($invoice));

            return $invoice;
        });
    }

    /**
     * Approve a Draft Invoice.
     */
    public function approve(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            if ($invoice->status !== InvoiceStatus::Draft) {
                throw new \InvalidArgumentException('Only draft invoices can be approved.');
            }

            // Re-validate limits and quantities upon transition approval
            $itemsData = $invoice->items->map(fn ($item) => [
                'quantity' => $item->quantity,
                'sales_order_item_id' => $item->sales_order_item_id,
                'delivery_order_item_id' => $item->delivery_order_item_id,
            ])->toArray();

            $this->validationService->validateCreditLimit($invoice->customer_id, (float) $invoice->total_amount, $invoice->id);
            $this->validationService->validateQuantities($itemsData, $invoice->id);

            $invoice->update([
                'status' => InvoiceStatus::Approved,
                'approved_by' => Auth::id() ?? 1,
                'approved_at' => now(),
            ]);

            InvoiceEvent::create([
                'invoice_id' => $invoice->id,
                'event_type' => 'approved',
                'event_data' => [],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);

            event(new InvoiceApproved($invoice));
        });
    }

    /**
     * Issue an Approved Invoice.
     */
    public function issue(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            if ($invoice->status !== InvoiceStatus::Approved) {
                throw new \InvalidArgumentException('Only approved invoices can be issued.');
            }

            $refNo = $this->numberGenerator->generateNextNumber();

            $invoice->update([
                'reference_no' => $refNo,
                'status' => InvoiceStatus::Issued,
            ]);

            InvoiceEvent::create([
                'invoice_id' => $invoice->id,
                'event_type' => 'issued',
                'event_data' => [
                    'reference_no' => $refNo,
                ],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);

            event(new InvoiceIssued($invoice));
        });
    }

    /**
     * Void an Issued or Overdue Invoice.
     */
    public function void(Invoice $invoice, string $reason): void
    {
        DB::transaction(function () use ($invoice, $reason) {
            if (! in_array($invoice->status, [InvoiceStatus::Issued, InvoiceStatus::Overdue])) {
                throw new \InvalidArgumentException('Only issued or overdue invoices can be voided.');
            }

            $invoice->update([
                'status' => InvoiceStatus::Void,
                'void_reason' => $reason,
                'outstanding_balance' => 0.00, // Void clears accounts receivable
            ]);

            InvoiceEvent::create([
                'invoice_id' => $invoice->id,
                'event_type' => 'voided',
                'event_data' => [
                    'reason' => $reason,
                ],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);

            event(new InvoiceVoided($invoice));
        });
    }
}
