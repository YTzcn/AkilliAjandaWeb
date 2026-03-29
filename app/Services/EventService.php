<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class EventService
{
    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * EventService constructor.
     *
     * @param EventRepository $eventRepository
     */
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * Get all events for the authenticated user.
     *
     * @return Collection
     */
    public function getAllEvents(): Collection
    {
        return $this->eventRepository->allByUser(Auth::id());
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
        if ($categoryIds !== []) {
            $event->categories()->sync($categoryIds);
        }

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
            $event->categories()->sync($categoryIds);

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
        return $this->eventRepository->findById($eventId);
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
        if ($categoryIds !== []) {
            $event->categories()->sync($categoryIds);
        }

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
            $event->categories()->sync($categoryIds);
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