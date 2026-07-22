<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Company;
use App\Models\CrmAuditLog;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantFeatureFlag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class TenantOnboardingController extends Controller
{
    /**
     * Show the onboarding wizard form.
     */
    public function show(): Response
    {
        // If already authenticated and has tenant, redirect to dashboard
        if (Auth::check() && Auth::user()->tenant_id) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('provisioning/wizard');
    }

    /**
     * Store and provision the new tenant context atomically.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_tax_id' => ['nullable', 'string', 'max:100'],
            'branch_name' => ['required', 'string', 'max:255'],
            'branch_code' => ['required', 'string', 'max:50'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'email', 'max:255'],
            'owner_password' => ['required', 'string', 'min:8'],
        ]);

        $slug = Str::slug($validated['tenant_name']);

        // Ensure slug is unique, append random string if conflicts exist
        if (Tenant::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.Str::lower(Str::random(4));
        }

        // DB Transaction for atomicity and rollback safety
        $result = DB::transaction(function () use ($validated, $slug) {
            // 1. Create Tenant
            $tenant = Tenant::create([
                'name' => $validated['tenant_name'],
                'slug' => $slug,
                'status' => 'active',
                'version' => 1,
            ]);

            // 2. Create Starter Subscription (14-day trial)
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_name' => 'trial',
                'status' => 'trial',
                'starts_at' => now(),
                'ends_at' => now()->addDays(14),
                'grace_ends_at' => now()->addDays(21),
                'version' => 1,
            ]);

            // 3. Create Company
            $company = Company::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['company_name'],
                'tax_id' => $validated['company_tax_id'],
                'version' => 1,
            ]);

            // 4. Create Branch
            $branch = Branch::create([
                'company_id' => $company->id,
                'tenant_id' => $tenant->id,
                'name' => $validated['branch_name'],
                'code' => $validated['branch_code'],
                'version' => 1,
            ]);

            // 5. Create Owner User
            $user = User::create([
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($validated['owner_password']),
                'is_active' => true,
                'version' => 1,
            ]);

            // 6. Assign role Owner with Spatie permission teams scoping
            if (config('permission.teams')) {
                setPermissionsTeamId($tenant->id);
            }

            foreach (UserRole::cases() as $roleEnum) {
                $role = Role::firstOrCreate([
                    'team_id' => $tenant->id,
                    'name' => $roleEnum->value,
                    'guard_name' => 'web',
                ]);

                if ($roleEnum === UserRole::Owner || $roleEnum === UserRole::Admin) {
                    $role->syncPermissions(Permission::all());
                }
            }

            $user->assignRole(UserRole::Owner->value);

            // 7. Seed starter feature flags (default features: 'crm' enabled)
            TenantFeatureFlag::create([
                'tenant_id' => $tenant->id,
                'feature_code' => 'crm',
                'is_enabled' => true,
            ]);

            // 8. Log audit trail
            CrmAuditLog::create([
                'tenant_id' => $tenant->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'auditable_type' => Tenant::class,
                'auditable_id' => $tenant->id,
                'event' => 'created',
                'action' => 'created',
                'new_values' => ['name' => $tenant->name, 'slug' => $tenant->slug],
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $user;
        });

        // Log in the newly created owner user
        Auth::login($result);

        return redirect()->route('dashboard');
    }
}
