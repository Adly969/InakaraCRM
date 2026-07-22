<?php

namespace App\Repositories\CRM;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Collection;

class ActivityRepository
{
    /**
     * Find an Activity by ID.
     */
    public function find(int $id): ?Activity
    {
        return Activity::find($id);
    }

    /**
     * Create a new Activity.
     */
    public function create(array $attributes): Activity
    {
        $activity = Activity::create($attributes);
        if (! $activity instanceof Activity) {
            throw new \RuntimeException('Failed to create activity.');
        }

        return $activity;
    }

    /**
     * Get the timeline of activities for a specific Customer.
     *
     * @return Collection<int, Activity>
     */
    public function getTimelineForCustomer(int $customerId): Collection
    {
        return Activity::with('attachments')
            ->where('customer_id', $customerId)
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    /**
     * Get the timeline of activities for a specific Lead.
     *
     * @return Collection<int, Activity>
     */
    public function getTimelineForLead(int $leadId): Collection
    {
        return Activity::with('attachments')
            ->where('lead_id', $leadId)
            ->orderBy('occurred_at', 'desc')
            ->get();
    }
}
