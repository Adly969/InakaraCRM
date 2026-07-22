<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-invoices');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('view-invoices');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-invoices');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('edit-invoices') && $invoice->status === InvoiceStatus::Draft;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, Invoice $invoice): bool
    {
        return $user->can('approve-invoices') && $invoice->status === InvoiceStatus::Draft;
    }

    /**
     * Determine whether the user can issue the model.
     */
    public function issue(User $user, Invoice $invoice): bool
    {
        return $user->can('issue-invoices') && $invoice->status === InvoiceStatus::Approved;
    }

    /**
     * Determine whether the user can void the model.
     */
    public function void(User $user, Invoice $invoice): bool
    {
        return $user->can('void-invoices') && in_array($invoice->status, [InvoiceStatus::Issued, InvoiceStatus::Overdue]);
    }
}
