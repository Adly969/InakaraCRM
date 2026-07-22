<?php

namespace App\Jobs\CRM;

use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReminderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $dueTasks = FollowUp::where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addHours(24)])
            ->get();

        foreach ($dueTasks as $task) {
            // In a production system, this would send an email, sms or push notification
            Log::info("CRM Follow-up task reminder: '{$task->title}' is due on {$task->due_date} for user ID: {$task->assigned_to}");
        }
    }
}
