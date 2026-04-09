<?php

namespace App\ViewModels;

/**
 * Dönem raporu için türetilmiş metrikler ve kısa yorum satırları.
 *
 * @phpstan-type WeekBucket array{label: string, task_count: int, event_count: int}
 */
final class PeriodReportInsights
{
    /**
     * @param  array<int, int>  $tasksByPriority  1..3 => tüm görevler (aralıkta)
     * @param  array<int, int>  $openTasksByPriority  1..3 => tamamlanmamış
     * @param  array<int, int>  $eventsByWeekdayIso  1=Pzt … 7=Paz
     * @param  list<WeekBucket>  $weekBuckets
     * @param  list<string>  $narrativeBullets
     */
    public function __construct(
        public int $completionRatePercent,
        public array $tasksByPriority,
        public array $openTasksByPriority,
        public int $highPriorityOpenCount,
        public int $overdueOpenCount,
        public ?float $avgEventDurationHours,
        public int $multiDayEventCount,
        public array $eventsByWeekdayIso,
        public ?string $busiestWeekdayLabel,
        public int $busiestWeekdayCount,
        public array $weekBuckets,
        public array $narrativeBullets,
        public int $totalEventHoursRounded,
    ) {}
}
