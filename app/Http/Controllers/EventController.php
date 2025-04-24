<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * @var EventService
     */
    protected $eventService;

    /**
     * EventController constructor.
     *
     * @param EventService $eventService
     */
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the events.
     *
     * @return View
     */
    public function index(): View
    {
        $events = $this->eventService->getAllEvents();
        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     *
     * @return View
     */
    public function create(): View
    {
        return view('events.create');
    }

    /**
     * Store a newly created event in storage.
     *
     * @param StoreEventRequest $request
     * @return RedirectResponse
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $this->eventService->createEvent($request->validated());
        return redirect()->route('events.index')->with('success', 'Etkinlik başarıyla oluşturuldu.');
    }

    /**
     * Display the specified event.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $event = $this->eventService->getEventById($id);
        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $event = $this->eventService->getEventById($id);
        return view('events.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     *
     * @param UpdateEventRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateEventRequest $request, int $id): RedirectResponse
    {
        $this->eventService->updateEvent($id, $request->validated());
        return redirect()->route('events.index')->with('success', 'Etkinlik başarıyla güncellendi.');
    }

    /**
     * Remove the specified event from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->eventService->deleteEvent($id);
        return redirect()->route('events.index')->with('success', 'Etkinlik başarıyla silindi.');
    }

    /**
     * Display events for a specific date range.
     *
     * @param Request $request
     * @return View
     */
    public function dateRange(Request $request): View
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $events = $this->eventService->getEventsForDateRange(
            $request->start_date,
            $request->end_date
        );

        return view('events.date_range', compact('events'));
    }
} 