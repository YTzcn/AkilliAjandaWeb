<?php

namespace App\Repositories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

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
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getEventsBetweenDates(int $userId, string $startDate, string $endDate): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->get();
    }
} 