<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelPaymentRequest;
use App\Http\Requests\ReversePaymentRequest;
use App\Models\Payment;
use App\Services\PaymentPostingService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PaymentWorkflowController extends Controller
{
    public function __construct(
        protected PaymentPostingService $postingService
    ) {}

    /**
     * Submit payment.
     */
    public function submit(Payment $payment): RedirectResponse
    {
        Gate::authorize('submit', $payment);

        try {
            $this->postingService->submit($payment);

            return back()->with('success', 'Payment submitted for verification.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Verify payment allocations.
     */
    public function verify(Payment $payment): RedirectResponse
    {
        Gate::authorize('verify', $payment);

        try {
            $this->postingService->verify($payment);

            return back()->with('success', 'Payment verified successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Approve payment level.
     */
    public function approve(Payment $payment): RedirectResponse
    {
        Gate::authorize('approve', $payment);

        try {
            $this->postingService->approve($payment);

            return back()->with('success', 'Payment approved successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Post payment (final balance deduction).
     */
    public function post(Payment $payment): RedirectResponse
    {
        Gate::authorize('post', $payment);

        try {
            $this->postingService->post($payment);

            return back()->with('success', 'Payment posted successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel a pre-posted payment.
     */
    public function cancel(CancelPaymentRequest $request, Payment $payment): RedirectResponse
    {
        Gate::authorize('cancel', $payment);

        try {
            $this->postingService->cancel($payment, $request->input('reason'));

            return back()->with('success', 'Payment cancelled.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reverse a posted payment.
     */
    public function reverse(ReversePaymentRequest $request, Payment $payment): RedirectResponse
    {
        Gate::authorize('reverse', $payment);

        try {
            $this->postingService->reverse($payment, $request->input('reason'));

            return back()->with('success', 'Payment reversed successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
