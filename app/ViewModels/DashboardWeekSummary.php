<?php

namespace App\ViewModels;

use Carbon\CarbonInterface;

/**
 * Dashboard haftalık özet istatistikleri (salt okunur).
 */
final class DashboardWeekSummary
{
    public function __construct(
        public CarbonInterface $weekStart,
        public CarbonInterface $weekEnd,
        public int $upcomingEventsCount,
        public int $weekEventsCount,
        public int $pendingTasksCount,
        public int $weekPendingDueCount,
        public int $weekCompletedCount,
        public int $overdueCount,
        public int $highPriorityWeekDueCount,
        public int $pendingDueTodayCount,
    ) {}
}
