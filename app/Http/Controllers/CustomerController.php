<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected CustomerService $customerService) {}

    /**
     * Display a listing of the customers.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Customer::class);

        $query = Customer::query();

        // Row-level scope: Sales representatives can only view customers assigned to them.
        // Owners, Admins, Managers, and Customer Service can view all customers.
        $user = $request->user();
        if (! ($user->hasRole(UserRole::Owner->value) ||
               $user->hasRole(UserRole::Admin->value) ||
               $user->hasRole(UserRole::Manager->value) ||
               $user->hasRole(UserRole::CustomerService->value))) {
            $query->where('assigned_to', $user->id);
        }

        $customers = $query->with('assignedTo')->latest()->paginate(10);

        return Inertia::render('customers/index', [
            'customers' => $customers,
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): Response
    {
        Gate::authorize('create', Customer::class);

        $users = User::select(['id', 'name'])->get();

        return Inertia::render('customers/create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Gate::authorize('create', Customer::class);

        $this->customerService->create($request->validated(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Customer created successfully.',
        ]);

        return to_route('customers.index');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): Response
    {
        Gate::authorize('view', $customer);

        return Inertia::render('customers/show', [
            'customer' => $customer->load(['assignedTo', 'creator', 'updater']),
        ]);
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): Response
    {
        Gate::authorize('update', $customer);

        $users = User::select(['id', 'name'])->get();

        return Inertia::render('customers/edit', [
            'customer' => $customer,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Customer $customer, UpdateCustomerRequest $request): RedirectResponse
    {
        Gate::authorize('update', $customer);

        $this->customerService->update($customer, $request->validated(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Customer updated successfully.',
        ]);

        return to_route('customers.index');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        Gate::authorize('delete', $customer);

        $customer->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Customer deleted successfully.',
        ]);

        return to_route('customers.index');
    }
}
