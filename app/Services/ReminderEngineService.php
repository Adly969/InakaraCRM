<?php

namespace App\Services;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderEngineService
{
    /**
     * Dispatch reminders for invoices due in N days.
     */
    public function dispatchDueReminders(int $daysBefore = 3): int
    {
        $targetDate = Carbon::today()->addDays($daysBefore)->toDateString();
        $invoices = Invoice::with('customer')
            ->whereIn('status', ['issued'])
            ->where('due_date', $targetDate)
            ->where('outstanding_balance', '>', 0)
            ->get();

        $count = 0;
        foreach ($invoices as $invoice) {
            // Queue reminder dispatch email/whatsapp
            Log::info("Queuing Due Reminder for Invoice {$invoice->reference_no} to Customer {$invoice->customer->name}");
            $count++;
        }

        return $count;
    }

    /**
     * Dispatch reminders for overdue invoices.
     */
    public function dispatchOverdueReminders(int $daysOverdue = 1): int
    {
        $targetDate = Carbon::today()->subDays($daysOverdue)->toDateString();
        $invoices = Invoice::with('customer')
            ->whereIn('status', ['overdue'])
            ->where('due_date', $targetDate)
            ->where('outstanding_balance', '>', 0)
            ->get();

        $count = 0;
        foreach ($invoices as $invoice) {
            // Queue overdue reminder dispatch email/whatsapp
            Log::info("Queuing Overdue Reminder for Invoice {$invoice->reference_no} to Customer {$invoice->customer->name}");
            $count++;
        }

        return $count;
    }
}
