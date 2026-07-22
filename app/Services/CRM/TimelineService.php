<?php

namespace App\Services\CRM;

use App\Models\CalendarEvent;
use App\Models\CrmActivity;
use App\Models\CrmDocument;
use App\Models\CrmTask;
use Illuminate\Support\Collection;

class TimelineService
{
    /**
     * Get unified chronological timeline for Customer.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getCustomerTimeline(int $customerId): Collection
    {
        $activities = CrmActivity::query()
            ->with(['creator:id,name', 'assignedTo:id,name'])
            ->where('customer_id', $customerId)
            ->get()
            ->map(fn (CrmActivity $a) => [
                'id' => 'activity-'.$a->id,
                'type' => 'activity',
                'title' => $a->subject,
                'description' => $a->description,
                'activity_type' => $a->activity_type->value,
                'status' => $a->status->value,
                'user_name' => $a->creator->name ?? 'System',
                'timestamp' => $a->start_time->toIso8601String(),
            ]);

        $tasks = CrmTask::query()
            ->with(['creator:id,name', 'assignedTo:id,name'])
            ->where('customer_id', $customerId)
            ->get()
            ->map(fn (CrmTask $t) => [
                'id' => 'task-'.$t->id,
                'type' => 'task',
                'title' => $t->title,
                'description' => $t->description,
                'priority' => $t->priority->value,
                'status' => $t->status->value,
                'user_name' => $t->assignedTo->name ?? 'Unassigned',
                'timestamp' => $t->created_at->toIso8601String(),
            ]);

        $events = CalendarEvent::query()
            ->with(['organizer:id,name'])
            ->where('customer_id', $customerId)
            ->get()
            ->map(fn (CalendarEvent $e) => [
                'id' => 'event-'.$e->id,
                'type' => 'event',
                'title' => $e->title,
                'description' => $e->description,
                'event_type' => $e->event_type->value,
                'user_name' => $e->organizer->name ?? 'System',
                'timestamp' => $e->start_at->toIso8601String(),
            ]);

        $documents = CrmDocument::query()
            ->with(['uploader:id,name', 'latestVersion'])
            ->where('customer_id', $customerId)
            ->get()
            ->map(fn (CrmDocument $d) => [
                'id' => 'doc-'.$d->id,
                'type' => 'document',
                'title' => $d->title,
                'description' => $d->description,
                'document_type' => $d->document_type->value,
                'file_name' => $d->latestVersion->file_name ?? null,
                'user_name' => $d->uploader->name ?? 'System',
                'timestamp' => $d->created_at->toIso8601String(),
            ]);

        return $activities
            ->concat($tasks)
            ->concat($events)
            ->concat($documents)
            ->sortByDesc('timestamp')
            ->values();
    }

    /**
     * Get unified chronological timeline for Lead.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getLeadTimeline(int $leadId): Collection
    {
        $activities = CrmActivity::query()
            ->with(['creator:id,name'])
            ->where('lead_id', $leadId)
            ->get()
            ->map(fn (CrmActivity $a) => [
                'id' => 'activity-'.$a->id,
                'type' => 'activity',
                'title' => $a->subject,
                'description' => $a->description,
                'activity_type' => $a->activity_type->value,
                'status' => $a->status->value,
                'user_name' => $a->creator->name ?? 'System',
                'timestamp' => $a->start_time->toIso8601String(),
            ]);

        $tasks = CrmTask::query()
            ->with(['assignedTo:id,name'])
            ->where('lead_id', $leadId)
            ->get()
            ->map(fn (CrmTask $t) => [
                'id' => 'task-'.$t->id,
                'type' => 'task',
                'title' => $t->title,
                'description' => $t->description,
                'priority' => $t->priority->value,
                'status' => $t->status->value,
                'user_name' => $t->assignedTo->name ?? 'Unassigned',
                'timestamp' => $t->created_at->toIso8601String(),
            ]);

        return $activities
            ->concat($tasks)
            ->sortByDesc('timestamp')
            ->values();
    }

    /**
     * Get unified chronological timeline for Opportunity.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getOpportunityTimeline(int $opportunityId): Collection
    {
        $activities = CrmActivity::query()
            ->with(['creator:id,name'])
            ->where('opportunity_id', $opportunityId)
            ->get()
            ->map(fn (CrmActivity $a) => [
                'id' => 'activity-'.$a->id,
                'type' => 'activity',
                'title' => $a->subject,
                'description' => $a->description,
                'activity_type' => $a->activity_type->value,
                'status' => $a->status->value,
                'user_name' => $a->creator->name ?? 'System',
                'timestamp' => $a->start_time->toIso8601String(),
            ]);

        $tasks = CrmTask::query()
            ->with(['assignedTo:id,name'])
            ->where('opportunity_id', $opportunityId)
            ->get()
            ->map(fn (CrmTask $t) => [
                'id' => 'task-'.$t->id,
                'type' => 'task',
                'title' => $t->title,
                'description' => $t->description,
                'priority' => $t->priority->value,
                'status' => $t->status->value,
                'user_name' => $t->assignedTo->name ?? 'Unassigned',
                'timestamp' => $t->created_at->toIso8601String(),
            ]);

        $documents = CrmDocument::query()
            ->with(['uploader:id,name', 'latestVersion'])
            ->where('opportunity_id', $opportunityId)
            ->get()
            ->map(fn (CrmDocument $d) => [
                'id' => 'doc-'.$d->id,
                'type' => 'document',
                'title' => $d->title,
                'description' => $d->description,
                'document_type' => $d->document_type->value,
                'file_name' => $d->latestVersion->file_name ?? null,
                'user_name' => $d->uploader->name ?? 'System',
                'timestamp' => $d->created_at->toIso8601String(),
            ]);

        return $activities
            ->concat($tasks)
            ->concat($documents)
            ->sortByDesc('timestamp')
            ->values();
    }
}
