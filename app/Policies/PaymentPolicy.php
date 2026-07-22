<?php

namespace App\Policies;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-payments');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payment $payment): bool
    {
        return $user->can('view-payments');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-payments');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payment $payment): bool
    {
        return $user->can('edit-payments') && $payment->status === PaymentStatus::Draft;
    }

    /**
     * Determine whether the user can submit the model.
     */
    public function submit(User $user, Payment $payment): bool
    {
        return $user->can('submit-payments') && $payment->status === PaymentStatus::Draft;
    }

    /**
     * Determine whether the user can verify the model.
     */
    public function verify(User $user, Payment $payment): bool
    {
        return $user->can('verify-payments') && $payment->status === PaymentStatus::Submitted;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, Payment $payment): bool
    {
        $amount = (float) $payment->amount;

        // Four-eyes principle: Creator cannot approve
        if ($payment->created_by === $user->id) {
            return false;
        }

        if ($amount < 50000000.00) {
            // Tier 1: supervisor approval
            return $user->can('approve-payments-l1') && $payment->status === PaymentStatus::Verified;
        } elseif ($amount >= 50000000.00 && $amount < 500000000.00) {
            // Tier 2: supervisor or manager approval
            if ($payment->status === PaymentStatus::Verified) {
                return $user->can('approve-payments-l1');
            }
            if ($payment->status === PaymentStatus::FinanceSupervisorApproved) {
                // Verified user cannot approve at next level
                if ($payment->verified_by === $user->id) {
                    return false;
                }

                return $user->can('approve-payments-l2');
            }
        } else {
            // Tier 3: supervisor, manager or director approval
            if ($payment->status === PaymentStatus::Verified) {
                return $user->can('approve-payments-l1');
            }
            if ($payment->status === PaymentStatus::FinanceSupervisorApproved) {
                if ($payment->verified_by === $user->id) {
                    return false;
                }

                return $user->can('approve-payments-l2');
            }
            if ($payment->status === PaymentStatus::FinanceManagerApproved) {
                if ($payment->verified_by === $user->id || $payment->approved_by === $user->id) {
                    return false;
                }

                return $user->can('approve-payments-l3');
            }
        }

        return false;
    }

    /**
     * Determine whether the user can post the model.
     */
    public function post(User $user, Payment $payment): bool
    {
        return $user->can('post-payments') && $payment->status === PaymentStatus::Approved;
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, Payment $payment): bool
    {
        return $user->can('cancel-payments') &&
            ! in_array($payment->status, [PaymentStatus::Posted, PaymentStatus::Reversed, PaymentStatus::Cancelled]);
    }

    /**
     * Determine whether the user can reverse the model.
     */
    public function reverse(User $user, Payment $payment): bool
    {
        return $user->can('reverse-payments') && $payment->status === PaymentStatus::Posted;
    }

    /**
     * Determine whether the user can view receivables.
     */
    public function viewReceivables(User $user): bool
    {
        return $user->can('view-receivables');
    }
}
