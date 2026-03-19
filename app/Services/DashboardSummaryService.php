<?php

namespace App\Services;

use App\ViewModels\DashboardPresentation;
use App\ViewModels\DashboardWeekSummary;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class DashboardSummaryService
{
    public function __construct(
        protected EventService $eventService,
        protected TaskService $taskService
    ) {}

    /**
     * Hafta başı/sonu (ör. locale haftası) için dashboard verisini üretir.
     */
    public function buildPresentation(CarbonInterface $weekStart, CarbonInterface $weekEnd): DashboardPresentation
    {
        $weekStart = Carbon::instance($weekStart)->copy();
        $weekEnd = Carbon::instance($weekEnd)->copy();

        $today = Carbon::today();
        $oneWeekLater = $today->copy()->addWeek();

        $upcomingEvents = $this->eventService->getEventsForDateRange(
            $today->format('Y-m-d H:i:s'),
            $oneWeekLater->format('Y-m-d H:i:s')
        );
        $pendingTasks = $this->taskService->getPendingTasks();

        $summary = new DashboardWeekSummary(
            weekStart: $weekStart,
            weekEnd: $weekEnd,
            upcomingEventsCount: $upcomingEvents->count(),
            weekEventsCount: $this->eventService->countOverlappingRange($weekStart, $weekEnd),
            pendingTasksCount: $pendingTasks->count(),
            weekPendingDueCount: $this->taskService->countPendingDueBetween($weekStart, $weekEnd),
            weekCompletedCount: $this->taskService->countCompletedInPeriod($weekStart, $weekEnd),
            overdueCount: $this->taskService->getOverdueTasks()->count(),
            highPriorityWeekDueCount: $this->taskService->countHighPriorityPendingDueBetween($weekStart, $weekEnd),
            pendingDueTodayCount: $this->taskService->countPendingDueToday(),
        );

        return new DashboardPresentation($summary, $upcomingEvents, $pendingTasks);
    }
}
