<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class Money
{
    /**
     * Money constructor.
     */
    public function __construct(
        public readonly float $amount,
        public readonly string $currencyCode = 'IDR'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }
        if (empty(trim($currencyCode))) {
            throw new InvalidArgumentException('Currency code is required.');
        }
    }

    /**
     * Determine if another Money object is identical.
     */
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currencyCode === $other->currencyCode;
    }

    /**
     * Add Money amounts.
     */
    public function add(Money $other): self
    {
        if ($this->currencyCode !== $other->currencyCode) {
            throw new InvalidArgumentException('Cannot add money of different currencies.');
        }

        return new self($this->amount + $other->amount, $this->currencyCode);
    }

    /**
     * Subtract Money amounts.
     */
    public function subtract(Money $other): self
    {
        if ($this->currencyCode !== $other->currencyCode) {
            throw new InvalidArgumentException('Cannot subtract money of different currencies.');
        }

        return new self($this->amount - $other->amount, $this->currencyCode);
    }
}
