<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class EventService
{
    /**
     * EventService constructor.
     */
    public function __construct(
        protected EventRepository $eventRepository,
        protected CategoryService $categoryService
    ) {}

    /**
     * Get all events for the authenticated user.
     *
     * @return Collection
     */
    public function getAllEvents(): Collection
    {
        return $this->eventRepository->allByUser(Auth::id(), ['*'], ['categories']);
    }

    /**
     * Get events for a specific date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getEventsForDateRange(string $startDate, string $endDate): Collection
    {
        return $this->eventRepository->getEventsBetweenDates($startDate, $endDate);
    }

    /**
     * Hafta penceresiyle çakışan etkinlik sayısı (başlangıç/bitiş aralığı örtüşmesi).
     */
    public function countOverlappingRange(Carbon $rangeStart, Carbon $rangeEnd): int
    {
        return Event::query()
            ->where('user_id', Auth::id())
            ->where('start_date', '<=', $rangeEnd->copy()->endOfDay())
            ->where('end_date', '>=', $rangeStart->copy()->startOfDay())
            ->count();
    }

    /**
     * Create a new event.
     *
     * @param array $data
     * @return Event
     */
    public function createEvent(array $data): Event
    {
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);
        $data['user_id'] = Auth::id();
        $event = $this->eventRepository->create($data);
        $this->categoryService->syncCategories($event, $categoryIds);

        return $event->load('categories');
    }

    /**
     * Update an existing event.
     *
     * @param int $eventId
     * @param array $data
     * @return Event|null
     */
    public function updateEvent(int $eventId, array $data): ?Event
    {
        $categoryIds = null;
        if (array_key_exists('category_ids', $data)) {
            $categoryIds = $data['category_ids'] ?? [];
            unset($data['category_ids']);
        }
        $event = $this->eventRepository->update($eventId, $data);
        if ($event && $categoryIds !== null) {
            $this->categoryService->syncCategories($event, $categoryIds);

            return $event->fresh(['categories']);
        }

        return $event ? $event->load('categories') : null;
    }

    /**
     * Delete an event.
     *
     * @param int $eventId
     * @return bool
     */
    public function deleteEvent(int $eventId): bool
    {
        return $this->eventRepository->deleteById($eventId);
    }

    /**
     * Get a specific event by ID.
     *
     * @param int $eventId
     * @return Event|null
     */
    public function getEventById(int $eventId): ?Event
    {
        /** @var Event|null $event */
        $event = $this->eventRepository->findById($eventId, ['*'], ['categories']);

        return $event;
    }

    public function getCalendarEvents(array $filters = []): array
    {
        $events = $this->eventRepository->getForCalendar($filters);
        return $events->map(function ($event) {
            return $this->formatForCalendar($event);
        })->toArray();
    }

    public function handleCalendarCreate(array $data): Event
    {
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $event = $this->eventRepository->createFromCalendar($data);
        $this->categoryService->syncCategories($event, $categoryIds);

        return $event->load('categories');
    }

    public function handleCalendarUpdate(Event $event, array $data): Event
    {
        $categoryIds = null;
        if (array_key_exists('category_ids', $data)) {
            $categoryIds = $data['category_ids'] ?? [];
            unset($data['category_ids']);
        }

        $event = $this->eventRepository->updateFromCalendar($event, $data);
        if ($categoryIds !== null) {
            $this->categoryService->syncCategories($event, $categoryIds);
        }

        return $event->load('categories');
    }

    public function handleCalendarDelete(Event $event): bool
    {
        return $this->eventRepository->deleteFromCalendar($event);
    }

    public function formatForCalendar(Event $event): array
    {
        return $this->eventRepository->formatForCalendar($event);
    }
} 