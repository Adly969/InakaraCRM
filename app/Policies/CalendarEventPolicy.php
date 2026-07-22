<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\CalendarEvent;
use App\Models\User;

class CalendarEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ViewCalendar->value);
    }

    public function view(User $user, CalendarEvent $event): bool
    {
        if (! $user->hasPermissionTo(Permission::ViewCalendar->value)) {
            return false;
        }

        return $event->company_id === null || $event->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CreateCalendarEvents->value);
    }

    public function update(User $user, CalendarEvent $event): bool
    {
        if (! $user->hasPermissionTo(Permission::EditCalendarEvents->value)) {
            return false;
        }

        if ($event->company_id !== null && $event->company_id !== $user->company_id) {
            return false;
        }

        return $event->organizer_id === $user->id || $user->hasRole(['admin', 'owner', 'manager']);
    }

    public function delete(User $user, CalendarEvent $event): bool
    {
        if (! $user->hasPermissionTo(Permission::DeleteCalendarEvents->value)) {
            return false;
        }

        return $event->organizer_id === $user->id || $user->hasRole(['admin', 'owner', 'manager']);
    }
}
