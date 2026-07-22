<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Verified = 'verified';
    case FinanceSupervisorApproved = 'finance_supervisor_approved';
    case FinanceManagerApproved = 'finance_manager_approved';
    case DirectorApproved = 'director_approved';
    case Approved = 'approved';
    case Posted = 'posted';
    case Cancelled = 'cancelled';
    case Reversed = 'reversed';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::Verified => 'Verified',
            self::FinanceSupervisorApproved => 'Finance Supervisor Approved',
            self::FinanceManagerApproved => 'Finance Manager Approved',
            self::DirectorApproved => 'Director Approved',
            self::Approved => 'Approved',
            self::Posted => 'Posted',
            self::Cancelled => 'Cancelled',
            self::Reversed => 'Reversed',
        };
    }
}
