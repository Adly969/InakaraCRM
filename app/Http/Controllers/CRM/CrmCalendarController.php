<?php

namespace App\Http\Controllers\CRM;

use App\Enums\CalendarEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreCalendarEventRequest;
use App\Models\CalendarEvent;
use App\Models\User;
use App\Services\CRM\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CrmCalendarController extends Controller
{
    public function __construct(
        protected CalendarService $calendarService
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', CalendarEvent::class);

        return Inertia::render('crm/calendar/index', [
            'eventTypes' => collect(CalendarEventType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
                'color' => $t->color(),
            ]),
            'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function getEvents(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CalendarEvent::class);

        $start = Carbon::parse($request->query('start', now()->startOfMonth()));
        $end = Carbon::parse($request->query('end', now()->endOfMonth()));
        $organizerId = $request->query('organizer_id') ? (int) $request->query('organizer_id') : null;

        $events = $this->calendarService->getEvents($start, $end, $organizerId);

        return response()->json($events);
    }

    public function store(StoreCalendarEventRequest $request): RedirectResponse
    {
        $this->calendarService->create($request->validated(), $request->user());

        return redirect()->back()->with('success', 'Event scheduled successfully.');
    }

    public function update(Request $request, CalendarEvent $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'start_at' => 'sometimes|required|date',
            'end_at' => 'sometimes|required|date|after:start_at',
            'title' => 'sometimes|required|string|max:200',
            'status' => 'sometimes|required|string',
            'version' => 'nullable|integer',
        ]);

        $this->calendarService->update($event, $validated, $request->user());

        return redirect()->back()->with('success', 'Event updated.');
    }

    public function destroy(CalendarEvent $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()->back()->with('success', 'Event cancelled.');
    }
}
