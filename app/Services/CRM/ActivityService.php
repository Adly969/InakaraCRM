<?php

namespace App\Services\CRM;

use App\Enums\ActivityOutcome;
use App\Enums\CrmActivityStatus;
use App\Models\CrmActivity;
use App\Models\CrmActivityAttachment;
use App\Models\CrmActivityComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityService
{
    /**
     * Get paginated activities with filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CrmActivity::query()
            ->with(['customer:id,name', 'lead:id,first_name,last_name,company_name', 'opportunity:id,title', 'assignedTo:id,name', 'creator:id,name'])
            ->orderBy('start_time', 'desc');

        if (! empty($filters['activity_type'])) {
            $query->where('activity_type', $filters['activity_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['lead_id'])) {
            $query->where('lead_id', $filters['lead_id']);
        }

        if (! empty($filters['opportunity_id'])) {
            $query->where('opportunity_id', $filters['opportunity_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('subject', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new activity.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $creator): CrmActivity
    {
        return DB::transaction(function () use ($data, $creator) {
            $activity = CrmActivity::create([
                'activity_type' => $data['activity_type'],
                'subject' => $data['subject'],
                'description' => $data['description'] ?? null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'] ?? null,
                'status' => $data['status'] ?? CrmActivityStatus::Pending->value,
                'priority' => $data['priority'] ?? 'medium',
                'location' => $data['location'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'reminder_at' => $data['reminder_at'] ?? null,
                'is_recurring' => $data['is_recurring'] ?? false,
                'recurrence_rule' => $data['recurrence_rule'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'lead_id' => $data['lead_id'] ?? null,
                'opportunity_id' => $data['opportunity_id'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? $creator->id,
                'created_by' => $creator->id,
                'company_id' => $creator->company_id,
                'branch_id' => $creator->branch_id,
            ]);

            return $activity;
        });
    }

    /**
     * Update an activity with optimistic lock check.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(CrmActivity $activity, array $data, User $updater): CrmActivity
    {
        if (isset($data['version']) && (int) $data['version'] !== $activity->version) {
            throw ValidationException::withMessages([
                'version' => ['This activity has been modified by another user. Please refresh and try again.'],
            ]);
        }

        return DB::transaction(function () use ($activity, $data, $updater) {
            $activity->update(array_merge($data, [
                'updated_by' => $updater->id,
                'version' => $activity->version + 1,
            ]));

            return $activity->fresh();
        });
    }

    /**
     * Complete an activity with outcome log.
     */
    public function complete(CrmActivity $activity, ActivityOutcome $outcome, ?string $notes, User $user): CrmActivity
    {
        return DB::transaction(function () use ($activity, $outcome, $notes, $user) {
            $activity->update([
                'status' => CrmActivityStatus::Completed,
                'outcome' => $outcome,
                'description' => $notes ? trim(($activity->description ?? '')."\n\n[Completion Notes]: ".$notes) : $activity->description,
                'updated_by' => $user->id,
                'version' => $activity->version + 1,
            ]);

            return $activity->fresh();
        });
    }

    /**
     * Add comment to activity.
     */
    public function addComment(CrmActivity $activity, string $body, User $author, ?int $parentId = null): CrmActivityComment
    {
        return CrmActivityComment::create([
            'activity_id' => $activity->id,
            'user_id' => $author->id,
            'body' => $body,
            'parent_id' => $parentId,
            'company_id' => $author->company_id,
            'branch_id' => $author->branch_id,
        ]);
    }

    /**
     * Attach file to activity.
     */
    public function addAttachment(CrmActivity $activity, UploadedFile $file, User $uploader): CrmActivityAttachment
    {
        $path = $file->store('crm/activities/'.$activity->id, 'public');

        return CrmActivityAttachment::create([
            'activity_id' => $activity->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'uploaded_by' => $uploader->id,
            'company_id' => $uploader->company_id,
        ]);
    }
}
