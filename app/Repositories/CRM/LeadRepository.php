<?php

namespace App\Repositories\CRM;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Collection;

class LeadRepository
{
    /**
     * Find a Lead by its ID.
     */
    public function find(int $id): ?Lead
    {
        return Lead::find($id);
    }

    /**
     * Save a Lead model.
     */
    public function save(Lead $lead): bool
    {
        return $lead->save();
    }

    /**
     * Create a new Lead.
     */
    public function create(array $attributes): Lead
    {
        $lead = Lead::create($attributes);
        if (! $lead instanceof Lead) {
            throw new \RuntimeException('Failed to create lead.');
        }

        return $lead;
    }

    /**
     * Delete a Lead.
     */
    public function delete(Lead $lead): bool
    {
        return $lead->delete();
    }

    /**
     * Get all Leads for the current tenant.
     * Note: Tenant isolation is handled automatically via global scopes.
     *
     * @return Collection<int, Lead>
     */
    public function allForTenant(): Collection
    {
        return Lead::all();
    }
}
