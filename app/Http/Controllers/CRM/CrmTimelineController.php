<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Services\CRM\TimelineService;
use Illuminate\Http\JsonResponse;

class CrmTimelineController extends Controller
{
    public function __construct(
        protected TimelineService $timelineService
    ) {}

    public function customerTimeline(int $id): JsonResponse
    {
        return response()->json($this->timelineService->getCustomerTimeline($id));
    }

    public function leadTimeline(int $id): JsonResponse
    {
        return response()->json($this->timelineService->getLeadTimeline($id));
    }

    public function opportunityTimeline(int $id): JsonResponse
    {
        return response()->json($this->timelineService->getOpportunityTimeline($id));
    }
}
