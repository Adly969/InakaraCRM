<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Events\OutstandingBalanceUpdated;
use App\Events\PaymentApproved;
use App\Events\PaymentCancelled;
use App\Events\PaymentCreated;
use App\Events\PaymentPosted;
use App\Events\PaymentReversed;
use App\Events\PaymentVerified;
use App\Exceptions\InvalidPaymentTransitionException;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class PaymentPostingService
{
    public function __construct(
        protected PaymentRepository $paymentRepository,
        protected InvoiceRepository $invoiceRepository,
        protected PaymentCalculationService $calculationService,
        protected PaymentAllocationService $allocationService,
        protected OutstandingBalanceService $outstandingBalanceService,
        protected PaymentValidationService $validationService,
        protected PaymentNumberGenerator $numberGenerator,
        protected OfficialReceiptService $receiptService,
        protected ReceivableDashboardService $dashboardService
    ) {}

    /**
     * Create a payment draft.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $allocations = $data['allocations'] ?? [];

            // Calculate totals
            $totals = $this->calculationService->calculate((float) $data['amount'], $allocations);

            $paymentData = array_merge($data, [
                'status' => PaymentStatus::Draft->value,
                'allocated_amount' => $totals['allocated_amount'],
                'unallocated_amount' => $totals['unallocated_amount'],
                'created_by' => Auth::id() ?? 1,
            ]);

            $payment = $this->paymentRepository->createWithAllocations($paymentData, $allocations);

            $this->logEvent($payment, 'created', ['totals' => $totals]);
            event(new PaymentCreated($payment));

            return $payment;
        });
    }

    /**
     * Submit payment from Draft to Submitted.
     */
    public function submit(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            if ($payment->status !== PaymentStatus::Draft) {
                throw new InvalidPaymentTransitionException('Only draft payments can be submitted.');
            }

            $payment->status = PaymentStatus::Submitted;
            $payment->submitted_by = Auth::id() ?? 1;
            $payment->submitted_at = now();
            $payment->save();

            $this->logEvent($payment, 'submitted');
        });
    }

    /**
     * Verify payment.
     */
    public function verify(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            if ($payment->status !== PaymentStatus::Submitted) {
                throw new InvalidPaymentTransitionException('Only submitted payments can be verified.');
            }

            // Perform business validations on allocations
            $allocationsArray = $payment->allocations->map(fn ($a) => [
                'invoice_id' => $a->invoice_id,
                'amount' => (float) $a->amount,
            ])->toArray();

            $this->allocationService->validateAllocations($allocationsArray, (float) $payment->amount);

            $payment->status = PaymentStatus::Verified;
            $payment->verified_by = Auth::id() ?? 1;
            $payment->verified_at = now();
            $payment->save();

            $this->logEvent($payment, 'verified', ['allocations' => $allocationsArray]);
            event(new PaymentVerified($payment));
        });
    }

    /**
     * Approve payment (multi-level matrix routing based on amount).
     */
    public function approve(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $currentStatus = $payment->status;
            $currentUserId = Auth::id() ?? 1;

            // Enforce four-eyes against creator
            $this->validationService->validateFourEyes($payment->created_by, $currentUserId);

            $amount = (float) $payment->amount;

            // Tier 1: Under 50M. verified -> Approved.
            if ($amount < 50000000.00) {
                if ($currentStatus !== PaymentStatus::Verified) {
                    throw new InvalidPaymentTransitionException('Payment status must be Verified for Tier 1 approval.');
                }
                $payment->status = PaymentStatus::Approved;
            }
            // Tier 2: 50M to 500M. verified -> FinanceSupervisorApproved -> Approved.
            elseif ($amount >= 50000000.00 && $amount < 500000000.00) {
                if ($currentStatus === PaymentStatus::Verified) {
                    $payment->status = PaymentStatus::FinanceSupervisorApproved;
                } elseif ($currentStatus === PaymentStatus::FinanceSupervisorApproved) {
                    // Four-eyes check against supervisor who approved L1
                    $this->validationService->validateFourEyes($payment->verified_by ?? 0, $currentUserId);
                    $payment->status = PaymentStatus::Approved;
                } else {
                    throw new InvalidPaymentTransitionException('Invalid status for Tier 2 approval.');
                }
            }
            // Tier 3: >= 500M. verified -> FinanceSupervisorApproved -> FinanceManagerApproved -> Approved.
            else {
                if ($currentStatus === PaymentStatus::Verified) {
                    $payment->status = PaymentStatus::FinanceSupervisorApproved;
                } elseif ($currentStatus === PaymentStatus::FinanceSupervisorApproved) {
                    $this->validationService->validateFourEyes($payment->verified_by ?? 0, $currentUserId);
                    $payment->status = PaymentStatus::FinanceManagerApproved;
                } elseif ($currentStatus === PaymentStatus::FinanceManagerApproved) {
                    // Four-eyes check against creator, supervisor, and manager
                    $this->validationService->validateFourEyes($payment->approved_by ?? 0, $currentUserId);
                    $payment->status = PaymentStatus::Approved;
                } else {
                    throw new InvalidPaymentTransitionException('Invalid status for Tier 3 approval.');
                }
            }

            // If it transitioned to final Approved state, log approvals
            if ($payment->status === PaymentStatus::Approved) {
                $payment->approved_by = $currentUserId;
                $payment->approved_at = now();
            }

            $payment->save();

            $this->logEvent($payment, 'approved', ['role_approved' => $payment->status->value]);

            if ($payment->status === PaymentStatus::Approved) {
                event(new PaymentApproved($payment));
            }
        });
    }

    /**
     * Post payment (deduct balances atomically, generate reference sequence).
     */
    public function post(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            if ($payment->status !== PaymentStatus::Approved) {
                throw new InvalidPaymentTransitionException('Only approved payments can be posted.');
            }

            // Lock payment record
            $lockedPayment = $this->paymentRepository->lockForUpdate($payment->id);

            // Re-validate allocations and invoice balances inside locked transaction
            $allocationsArray = $lockedPayment->allocations->map(fn ($a) => [
                'invoice_id' => $a->invoice_id,
                'amount' => (float) $a->amount,
            ])->toArray();

            $this->allocationService->validateAllocations($allocationsArray, (float) $lockedPayment->amount);

            // Generate sequence number
            $refNo = $this->numberGenerator->generateNextNumber();

            // Deduct balance from invoices
            foreach ($lockedPayment->allocations as $alloc) {
                $invoice = $this->invoiceRepository->lockForUpdate($alloc->invoice_id);
                $prevBalance = (float) $invoice->outstanding_balance;

                $this->outstandingBalanceService->deductBalance($invoice, (float) $alloc->amount);

                event(new OutstandingBalanceUpdated($invoice, $prevBalance, (float) $invoice->fresh()->outstanding_balance));
            }

            // Update status
            $lockedPayment->reference_no = $refNo;
            $lockedPayment->status = PaymentStatus::Posted;
            $lockedPayment->posted_by = Auth::id() ?? 1;
            $lockedPayment->posted_at = now();
            $lockedPayment->save();

            // Generate idempotent Official Receipt
            $this->receiptService->generateReceipt($lockedPayment);

            // Invalidate dashboard cache
            $this->dashboardService->invalidateCache();

            // ponytail: Financial period lock check — EXT-10-001.
            // ponytail: GL journal entry generation — EXT-10-003.
            // ponytail: Notification dispatch — EXT-10-008.

            $this->logEvent($lockedPayment, 'posted', ['reference_no' => $refNo]);
            event(new PaymentPosted($lockedPayment));
        });
    }

    /**
     * Cancel a pre-posted payment.
     */
    public function cancel(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            if (in_array($payment->status, [PaymentStatus::Posted, PaymentStatus::Reversed, PaymentStatus::Cancelled])) {
                throw new InvalidPaymentTransitionException('Posted, reversed, or cancelled payments cannot be cancelled.');
            }

            $payment->status = PaymentStatus::Cancelled;
            $payment->cancellation_reason = $reason;
            $payment->save();

            $this->logEvent($payment, 'cancelled', ['reason' => $reason]);
            event(new PaymentCancelled($payment));
        });
    }

    /**
     * Reverse a posted payment (atomic balances restoration).
     */
    public function reverse(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            if ($payment->status !== PaymentStatus::Posted) {
                throw new InvalidPaymentTransitionException('Only posted payments can be reversed.');
            }

            // Lock payment record
            $lockedPayment = $this->paymentRepository->lockForUpdate($payment->id);

            // Restore balance to invoices
            foreach ($lockedPayment->allocations as $alloc) {
                $invoice = $this->invoiceRepository->lockForUpdate($alloc->invoice_id);
                $prevBalance = (float) $invoice->outstanding_balance;

                $this->outstandingBalanceService->restoreBalance($invoice, (float) $alloc->amount);

                event(new OutstandingBalanceUpdated($invoice, $prevBalance, (float) $invoice->fresh()->outstanding_balance));
            }

            // Update status
            $lockedPayment->status = PaymentStatus::Reversed;
            $lockedPayment->reversal_reason = $reason;
            $lockedPayment->reversed_by = Auth::id() ?? 1;
            $lockedPayment->reversed_at = now();
            $lockedPayment->save();

            // Void Official Receipt
            $this->receiptService->voidReceipt($lockedPayment);

            // Invalidate dashboard cache
            $this->dashboardService->invalidateCache();

            // ponytail: GL reversal journal entry — EXT-10-004.

            $this->logEvent($lockedPayment, 'reversed', ['reason' => $reason]);
            event(new PaymentReversed($lockedPayment));
        });
    }

    /**
     * Helper to write immutable audit logs.
     *
     * @param  array<string, mixed>  $eventData
     */
    protected function logEvent(Payment $payment, string $type, array $eventData = []): void
    {
        PaymentEvent::create([
            'payment_id' => $payment->id,
            'event_type' => $type,
            'event_data' => $eventData,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_by' => Auth::id() ?? 1,
        ]);
    }
}
