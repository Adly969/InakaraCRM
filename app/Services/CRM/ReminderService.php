<?php

namespace App\Services\CRM;

use App\Models\CrmReminder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ReminderService
{
    public function schedule(Model $remindable, Carbon $remindAt, ?string $message, User $user): CrmReminder
    {
        return CrmReminder::create([
            'remindable_type' => $remindable->getMorphClass(),
            'remindable_id' => $remindable->getKey(),
            'remind_at' => $remindAt,
            'message' => $message,
            'is_sent' => false,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
        ]);
    }

    public function processPending(): int
    {
        $pending = CrmReminder::query()
            ->where('is_sent', false)
            ->where('remind_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($pending as $reminder) {
            $reminder->update([
                'is_sent' => true,
                'sent_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }
}
