<?php

namespace App\ViewModels;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Tarih aralığı görev + etkinlik raporu (Hafta 5).
 */
final class PeriodReportPresentation
{
    /**
     * @param  Collection<int, \App\Models\Task>  $tasks
     * @param  Collection<int, \App\Models\Event>  $events
     */
    public function __construct(
        public Carbon $rangeStart,
        public Carbon $rangeEnd,
        public Collection $tasks,
        public Collection $events,
        public int $taskTotal,
        public int $taskCompleted,
        public int $taskPending,
        public int $eventTotal,
        public PeriodReportInsights $insights,
    ) {}
}
