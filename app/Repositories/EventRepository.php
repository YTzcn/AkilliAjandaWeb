<?php

namespace App\Repositories;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class EventRepository extends BaseRepository
{
    /**
     * EventRepository constructor.
     *
     * @param Event $model
     */
    public function __construct(Event $model)
    {
        parent::__construct($model);
    }

    /**
     * Get events between start and end dates for a user.
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getEventsBetweenDates(string $startDate, string $endDate): Collection
    {
        return $this->model
            ->where('user_id', Auth::id())
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->orderBy('start_date')
            ->get();
    }

    public function getForCalendar(array $filters = []): Collection
    {
        $query = $this->model->query()
            ->where('user_id', Auth::id());

        if (isset($filters['start'])) {
            $query->where('start_date', '>=', $filters['start']);
        }

        if (isset($filters['end'])) {
            $query->where('end_date', '<=', $filters['end']);
        }

        return $query->get();
    }

    public function createFromCalendar(array $data): Event
    {
        $data['user_id'] = Auth::id();
        $data['all_day'] = $data['all_day'] ?? false;
        
        // Tarihleri UTC'ye çevir
        $data['start_date'] = Carbon::parse($data['start_date']);
        $data['end_date'] = Carbon::parse($data['end_date']);

        return $this->model->create($data);
    }

    public function updateFromCalendar(Event $event, array $data): Event
    {
        // Sadece tarih güncellemesi ise
        if (count($data) === 2 && isset($data['start_date']) && isset($data['end_date'])) {
            $event->update([
                'start_date' => Carbon::parse($data['start_date']),
                'end_date' => Carbon::parse($data['end_date'])
            ]);
            return $event;
        }

        // Tam güncelleme
        if (isset($data['start_date'])) {
            $data['start_date'] = Carbon::parse($data['start_date']);
        }
        if (isset($data['end_date'])) {
            $data['end_date'] = Carbon::parse($data['end_date']);
        }

        $event->update($data);
        return $event;
    }

    public function deleteFromCalendar(Event $event): bool
    {
        return $event->delete();
    }

    public function formatForCalendar(Event $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'start' => $event->start_date->toIso8601String(),
            'end' => $event->end_date->toIso8601String(),
            'description' => $event->description,
            'location' => $event->location,
            'allDay' => $event->all_day,
            'className' => 'calendar-event',
            'extendedProps' => [
                'type' => 'event'
            ]
        ];
    }
} 