<?php

namespace App\ViewModels;

use Illuminate\Database\Eloquent\Collection;

/**
 * Dashboard görünümü için özet + liste koleksiyonları.
 */
final class DashboardPresentation
{
    /**
     * @param  Collection<int, \App\Models\Event>  $upcomingEvents
     * @param  Collection<int, \App\Models\Task>  $pendingTasks
     */
    public function __construct(
        public DashboardWeekSummary $summary,
        public Collection $upcomingEvents,
        public Collection $pendingTasks,
    ) {}
}
