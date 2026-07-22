<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Repositories\CRM\ActivityRepository;
use App\Repositories\CRM\CustomerRepository;
use App\Services\CRM\CustomerAssignmentService;
use App\Services\CRM\CustomerMergeService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CustomerApiController extends Controller
{
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected ActivityRepository $activityRepository,
        protected CustomerMergeService $customerMergeService,
        protected CustomerAssignmentService $customerAssignmentService
    ) {}

    /**
     * Get all customers for tenant.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Customer::class);

        $customers = $this->customerRepository->allForTenant();

        return response()->json([
            'success' => true,
            'data' => $customers,
            'meta' => [
                'count' => $customers->count(),
                'timestamp' => now()->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Show a customer profile.
     */
    public function show(int $id): JsonResponse
    {
        $customer = $this->customerRepository->findWithRelations($id);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'error' => 'Not Found',
                'message' => 'Customer profile not found.',
            ], 404);
        }

        Gate::authorize('view', $customer);

        return response()->json([
            'success' => true,
            'data' => $customer,
            'meta' => ['timestamp' => now()->toIso8601String()],
        ], 200);
    }

    /**
     * Store new customer.
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Customer::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:individual,company,government,school',
            'source' => 'nullable|string|max:50',
            'classification' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation Error',
                'message' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        $customer = $this->customerRepository->create(array_merge($validator->validated(), [
            'tenant_id' => $user->tenant_id,
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id,
            'status' => 'active',
            'assigned_to' => max(0, $user->id),
            'version' => 1,
            'created_by' => max(0, $user->id),
        ]));

        return response()->json([
            'success' => true,
            'data' => $customer,
            'meta' => ['timestamp' => now()->toIso8601String()],
        ], 201);
    }

    /**
     * Update customer profile with optimistic locking.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $customer = $this->customerRepository->find($id);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'error' => 'Not Found',
                'message' => 'Customer profile not found.',
            ], 404);
        }

        Gate::authorize('update', $customer);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:individual,company,government,school',
            'source' => 'nullable|string|max:50',
            'classification' => 'nullable|string|max:50',
            'version' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation Error',
                'message' => $validator->errors(),
            ], 422);
        }

        // Concurrency Check (Optimistic Lock)
        if ($customer->version !== (int) $request->input('version')) {
            return response()->json([
                'success' => false,
                'error' => 'Conflict',
                'message' => 'Record was modified by another process. Please reload and try again.',
            ], 409);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        $customer->fill($validator->validated());
        $customer->version = $customer->version + 1;
        $customer->updated_by = max(0, $user->id);
        $this->customerRepository->save($customer);

        return response()->json([
            'success' => true,
            'data' => $customer,
            'meta' => ['timestamp' => now()->toIso8601String()],
        ], 200);
    }

    /**
     * Soft delete customer.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $customer = $this->customerRepository->find($id);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'error' => 'Not Found',
                'message' => 'Customer profile not found.',
            ], 404);
        }

        Gate::authorize('delete', $customer);

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        $customer->deleted_by = max(0, $user->id);
        $customer->save();
        $this->customerRepository->delete($customer);

        return response()->json([
            'success' => true,
            'message' => 'Customer soft deleted successfully.',
            'meta' => ['timestamp' => now()->toIso8601String()],
        ], 200);
    }

    /**
     * Merge two customer profiles.
     */
    public function merge(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_customer_id' => 'required|integer',
            'source_customer_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation Error',
                'message' => $validator->errors(),
            ], 422);
        }

        Gate::authorize('merge', Customer::class);

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        try {
            $target = $this->customerMergeService->merge(
                (int) $request->input('target_customer_id'),
                (int) $request->input('source_customer_id'),
                max(0, $user->id)
            );

            return response()->json([
                'success' => true,
                'data' => $target,
                'meta' => [
                    'message' => 'Profiles merged successfully.',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid Operation',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reassign customer owner.
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        $customer = $this->customerRepository->find($id);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'error' => 'Not Found',
                'message' => 'Customer profile not found.',
            ], 404);
        }

        Gate::authorize('assign', $customer);

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|integer',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation Error',
                'message' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        try {
            $updatedCustomer = $this->customerAssignmentService->reassign(
                $id,
                (int) $request->input('assigned_to'),
                max(0, $user->id),
                $request->input('reason')
            );

            return response()->json([
                'success' => true,
                'data' => $updatedCustomer,
                'meta' => [
                    'message' => 'Ownership transferred successfully.',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid Operation',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get timeline activities.
     */
    public function timeline(int $id): JsonResponse
    {
        $customer = $this->customerRepository->find($id);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'error' => 'Not Found',
                'message' => 'Customer profile not found.',
            ], 404);
        }

        Gate::authorize('view', $customer);

        $activities = $this->activityRepository->getTimelineForCustomer($id);

        return response()->json([
            'success' => true,
            'data' => $activities,
            'meta' => ['timestamp' => now()->toIso8601String()],
        ], 200);
    }

    /**
     * Add activity manual log entry.
     */
    public function logActivity(Request $request, int $id): JsonResponse
    {
        $customer = $this->customerRepository->find($id);

        if (! $customer) {
            return response()->json([
                'success' => false,
                'error' => 'Not Found',
                'message' => 'Customer profile not found.',
            ], 404);
        }

        Gate::authorize('update', $customer);

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:call,meeting,visit,email,whatsapp,note',
            'subject' => 'required|string|max:200',
            'description' => 'nullable|string',
            'occurred_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation Error',
                'message' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        $activity = $this->activityRepository->create(array_merge($validator->validated(), [
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'created_by' => max(0, $user->id),
        ]));

        return response()->json([
            'success' => true,
            'data' => $activity,
            'meta' => ['timestamp' => now()->toIso8601String()],
        ], 201);
    }
}
