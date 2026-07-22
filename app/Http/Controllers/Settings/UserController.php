<?php

namespace App\Http\Controllers\Settings;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $users = User::with('roles')
            ->latest()
            ->paginate(10);

        // Map roles for simple React consumption
        $mappedUsers = $users->getCollection()->map(function (User $u) {
            $firstRole = $u->roles->first();
            $roleName = ($firstRole instanceof Role) ? $firstRole->name : 'viewer';

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => $roleName,
                'is_active' => $u->is_active,
            ];
        })->toArray();

        // Get available roles
        $roles = collect(UserRole::cases())->map(fn ($r) => [
            'value' => $r->value,
            'label' => $r->label(),
        ])->toArray();

        return Inertia::render('settings/users', [
            'users' => [
                'data' => $mappedUsers,
                'links' => $users->linkCollection()->toArray(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:'.implode(',', array_column(UserRole::cases(), 'value'))],
        ]);

        $activeTenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        /** @var User $currentUser */
        $currentUser = $request->user();

        $user = User::create([
            'tenant_id' => $activeTenant ? $activeTenant->id : $currentUser->tenant_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
            'created_by' => $currentUser->id,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('settings.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:'.implode(',', array_column(UserRole::cases(), 'value'))],
            'is_active' => ['required', 'boolean'],
        ]);

        /** @var User $currentUser */
        $currentUser = $request->user();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'is_active' => $validated['is_active'],
            'updated_by' => $currentUser->id,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Sync Spatie role
        $user->syncRoles([$validated['role']]);

        return redirect()->route('settings.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()->route('settings.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
