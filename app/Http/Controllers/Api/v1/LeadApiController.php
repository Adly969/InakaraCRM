<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Repositories\CRM\LeadRepository;
use App\Services\CRM\LeadConversionService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class LeadApiController extends Controller
{
    public function __construct(
        protected LeadRepository $leadRepository,
        protected LeadConversionService $leadConversionService
    ) {}

    /**
     * Display a listing of leads.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Lead::class);

        $leads = $this->leadRepository->allForTenant();

        return response()->json([
            'success' => true,
            'data' => $leads,
            'meta' => [
                'count' => $leads->count(),
                'timestamp' => now()->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Create a new lead.
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Lead::class);

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'company_name' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation Error',
                'message' => $validator->errors(),
            ], 422);
        }

        $lead = $this->leadRepository->create(array_merge($validator->validated(), [
            'tenant_id' => $user->tenant_id,
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id,
            'status' => 'draft',
            'assigned_to' => max(0, $user->id),
            'version' => 1,
            'created_by' => max(0, $user->id),
        ]));

        return response()->json([
            'success' => true,
            'data' => $lead,
            'meta' => ['timestamp' => now()->toIso8601String()],
        ], 201);
    }

    /**
     * Convert qualified lead to customer profile.
     */
    public function convert(Request $request, int $id): JsonResponse
    {
        $lead = $this->leadRepository->find($id);
        if (! $lead) {
            return response()->json([
                'success' => false,
                'error' => 'Not Found',
                'message' => 'Lead not found.',
            ], 404);
        }

        Gate::authorize('update', $lead);

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        try {
            $customer = $this->leadConversionService->convert($id, max(0, $user->id));

            return response()->json([
                'success' => true,
                'data' => $customer,
                'meta' => [
                    'message' => 'Lead successfully converted to customer profile.',
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Conflict/Invalid Operation',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
