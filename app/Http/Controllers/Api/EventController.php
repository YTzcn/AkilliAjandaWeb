<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalendarEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $service;

    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $events = $this->service->getCalendarEvents([
            'start' => $request->input('start'),
            'end' => $request->input('end')
        ]);

        return response()->json($events);
    }

    public function store(CalendarEventRequest $request): JsonResponse
    {
        $event = $this->service->handleCalendarCreate($request->validated());

        return response()->json([
            'message' => 'Etkinlik başarıyla oluşturuldu.',
            'event' => $this->service->formatForCalendar($event)
        ]);
    }

    public function update(CalendarEventRequest $request, Event $event): JsonResponse
    {
        $event = $this->service->handleCalendarUpdate($event, $request->validated());

        return response()->json([
            'message' => 'Etkinlik başarıyla güncellendi.',
            'event' => $this->service->formatForCalendar($event)
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->service->handleCalendarDelete($event);

        return response()->json([
            'message' => 'Etkinlik başarıyla silindi.'
        ]);
    }
} 