<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexEventFiltersRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Services\CategoryService;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService,
        protected CategoryService $categoryService
    ) {}

    /**
     * Display a listing of the events.
     */
    public function index(IndexEventFiltersRequest $request): View
    {
        $validated = $request->validated();
        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;
        $events = $this->eventService->getListingForWeb($categoryId);
        $categories = $this->categoryService->listForUser();
        $filters = [
            'category_id' => $categoryId !== null ? (string) $categoryId : '',
        ];

        return view('events.index', compact('events', 'categories', 'filters'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): View
    {
        $categories = $this->categoryService->listForUser();

        return view('events.create', compact('categories'));
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->eventService->createEvent([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_time'],
            'end_date' => $data['end_time'],
            'location' => $data['location'] ?? null,
            'all_day' => false,
            'category_ids' => $data['category_ids'] ?? [],
        ]);

        return redirect()->route('events.index')->with('success', 'Etkinlik başarıyla oluşturuldu.');
    }

    /**
     * Display the specified event.
     */
    public function show(int $id): View
    {
        $event = $this->eventService->getEventById($id);

        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(int $id): View
    {
        $event = $this->eventService->getEventById($id);
        $categories = $this->categoryService->listForUser();

        return view('events.edit', compact('event', 'categories'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(UpdateEventRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();
        $this->eventService->updateEvent($id, [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_time'],
            'end_date' => $data['end_time'],
            'location' => $data['location'] ?? null,
            'category_ids' => array_values($data['category_ids'] ?? []),
        ]);

        return redirect()->route('events.index')->with('success', 'Etkinlik başarıyla güncellendi.');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->eventService->deleteEvent($id);

        return redirect()->route('events.index')->with('success', 'Etkinlik başarıyla silindi.');
    }

    /**
     * Display events for a specific date range.
     */
    public function dateRange(Request $request): View
    {
        $events = collect();
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($startDate !== null || $endDate !== null) {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $events = $this->eventService->getEventsForDateRange(
                $validated['start_date'],
                $validated['end_date']
            );
        }

        return view('events.date_range', [
            'events' => $events,
            'startDate' => $startDate ?? now()->startOfMonth()->toDateString(),
            'endDate' => $endDate ?? now()->endOfMonth()->toDateString(),
        ]);
    }
}
