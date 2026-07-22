<?php

namespace App\Repositories\CRM;

use App\Models\FollowUp;
use Illuminate\Database\Eloquent\Collection;

class FollowUpRepository
{
    /**
     * Find a FollowUp task by ID.
     */
    public function find(int $id): ?FollowUp
    {
        return FollowUp::find($id);
    }

    /**
     * Create a new FollowUp task.
     */
    public function create(array $attributes): FollowUp
    {
        $followUp = FollowUp::create($attributes);
        if (! $followUp instanceof FollowUp) {
            throw new \RuntimeException('Failed to create follow up.');
        }

        return $followUp;
    }

    /**
     * Save a FollowUp model.
     */
    public function save(FollowUp $followUp): bool
    {
        return $followUp->save();
    }

    /**
     * Get pending tasks for a specific user.
     *
     * @return Collection<int, FollowUp>
     */
    public function getPendingForUser(int $userId): Collection
    {
        return FollowUp::where('assigned_to', $userId)
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->get();
    }
}
