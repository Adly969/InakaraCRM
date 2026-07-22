<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Lead;

class DuplicateDetectionService
{
    /**
     * Checks if a lead or customer is potential duplicate.
     * Returns match similarity details and merge recommendations.
     */
    public function check(array $data): array
    {
        $matches = [];

        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;
        $companyName = $data['company_name'] ?? null;

        if ($email) {
            // Exact email match in Leads
            $duplicateLead = Lead::where('email', $email)->first();
            if ($duplicateLead) {
                $matches[] = [
                    'type' => 'lead',
                    'id' => $duplicateLead->id,
                    'name' => $duplicateLead->name,
                    'similarity' => 100,
                    'reason' => "Exact email match: {$email}",
                ];
            }

            // Exact email match in Customers
            $duplicateCustomer = Customer::where('email', $email)->first();
            if ($duplicateCustomer) {
                $matches[] = [
                    'type' => 'customer',
                    'id' => $duplicateCustomer->id,
                    'name' => $duplicateCustomer->name,
                    'similarity' => 100,
                    'reason' => "Exact email match: {$email}",
                ];
            }
        }

        if ($phone) {
            // Strip non-numeric chars for matching
            $cleanPhone = preg_replace('/\D/', '', $phone);

            $duplicateLeadByPhone = Lead::whereNotNull('phone')->get()->first(function ($l) use ($cleanPhone) {
                return preg_replace('/\D/', '', $l->phone) === $cleanPhone;
            });
            if ($duplicateLeadByPhone) {
                $matches[] = [
                    'type' => 'lead',
                    'id' => $duplicateLeadByPhone->id,
                    'name' => $duplicateLeadByPhone->name,
                    'similarity' => 95,
                    'reason' => "Phone match: {$phone}",
                ];
            }
        }

        if ($companyName) {
            // Similarity checks on Company Name using standard SQL LIKE
            $duplicateCompany = Lead::where('company_name', 'like', "%{$companyName}%")->first();
            if ($duplicateCompany && strtolower($duplicateCompany->company_name) !== strtolower($companyName)) {
                $matches[] = [
                    'type' => 'lead',
                    'id' => $duplicateCompany->id,
                    'name' => $duplicateCompany->name,
                    'similarity' => 80,
                    'reason' => "Similar company name: {$duplicateCompany->company_name}",
                ];
            }
        }

        return [
            'has_duplicates' => ! empty($matches),
            'matches' => $matches,
        ];
    }
}
