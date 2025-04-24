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
        return $this->eventRepository->getEventsBetweenDates(Auth::id(), $startDate, $endDate);
    }

    /**
     * Create a new event.
     *
     * @param array $data
     * @return Event
     */
    public function createEvent(array $data): Event
    {
        $data['user_id'] = Auth::id();
        return $this->eventRepository->create($data);
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
        return $this->eventRepository->update($eventId, $data);
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
} 