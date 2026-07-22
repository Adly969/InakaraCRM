<?php

namespace App\Services;

use App\Enums\PaymentMethodType;
use App\Enums\PaymentStatus;
use App\Exceptions\InvalidPaymentTransitionException;
use App\Exceptions\PaymentAlreadyPostedException;
use App\Models\Payment;
use Illuminate\Validation\ValidationException;

class PaymentValidationService
{
    /**
     * Validate payment data structure.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function validatePaymentData(array $data): void
    {
        $amount = (float) ($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Payment amount must be greater than zero.'],
            ]);
        }

        $paymentDate = $data['payment_date'] ?? null;
        if ($paymentDate && strtotime($paymentDate) > time()) {
            throw ValidationException::withMessages([
                'payment_date' => ['Payment date cannot be in the future.'],
            ]);
        }

        $method = $data['payment_method'] ?? null;
        if ($method && ! in_array($method, PaymentMethodType::values())) {
            throw ValidationException::withMessages([
                'payment_method' => ['Invalid payment method type.'],
            ]);
        }
    }

    /**
     * Enforce the four-eyes principle for approval.
     *
     * @throws InvalidPaymentTransitionException
     */
    public function validateFourEyes(int $creatorId, int $approverId): void
    {
        if ($creatorId === $approverId) {
            throw new InvalidPaymentTransitionException('The creator of a payment record cannot approve it.');
        }
    }

    /**
     * Assert payment is not posted or reversed.
     *
     * @throws PaymentAlreadyPostedException
     */
    public function validateNotAlreadyPosted(Payment $payment): void
    {
        if ($payment->status === PaymentStatus::Posted) {
            throw new PaymentAlreadyPostedException('Payment has already been posted and cannot be modified.');
        }

        if ($payment->status === PaymentStatus::Reversed) {
            throw new PaymentAlreadyPostedException('Payment has been reversed and cannot be modified.');
        }
    }
}
