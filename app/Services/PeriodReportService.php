<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Task;
use App\ViewModels\PeriodReportInsights;
use App\ViewModels\PeriodReportPresentation;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PeriodReportService
{
    /**
     * Son tarihi [rangeStart, rangeEnd] içinde olan görevler ve aralıkla çakışan etkinlikler.
     */
    public function buildForUser(int $userId, CarbonInterface $rangeStart, CarbonInterface $rangeEnd): PeriodReportPresentation
    {
        $start = Carbon::instance($rangeStart)->copy()->startOfDay();
        $end = Carbon::instance($rangeEnd)->copy()->endOfDay();

        $tasks = Task::query()
            ->where('user_id', $userId)
            ->whereBetween('due_date', [$start, $end])
            ->orderBy('due_date')
            ->get();

        $events = Event::query()
            ->where('user_id', $userId)
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->orderBy('start_date')
            ->get();

        $taskCompleted = $tasks->where('is_completed', true)->count();
        $taskPending = $tasks->where('is_completed', false)->count();

        $insights = $this->computeInsights($tasks, $events, $start, $end, $taskCompleted, $tasks->count());

        return new PeriodReportPresentation(
            rangeStart: $start,
            rangeEnd: $end,
            tasks: $tasks,
            events: $events,
            taskTotal: $tasks->count(),
            taskCompleted: $taskCompleted,
            taskPending: $taskPending,
            eventTotal: $events->count(),
            insights: $insights,
        );
    }

    private function computeInsights(
        Collection $tasks,
        Collection $events,
        Carbon $rangeStart,
        Carbon $rangeEnd,
        int $taskCompleted,
        int $taskTotal
    ): PeriodReportInsights {
        $completionRate = $taskTotal > 0 ? (int) round(100 * $taskCompleted / $taskTotal) : 0;

        $tasksByPriority = [1 => 0, 2 => 0, 3 => 0];
        $openByPriority = [1 => 0, 2 => 0, 3 => 0];
        foreach ($tasks as $t) {
            $p = (int) $t->priority;
            if (! isset($tasksByPriority[$p])) {
                $p = 2;
            }
            $tasksByPriority[$p]++;
            if (! $t->is_completed) {
                $openByPriority[$p]++;
            }
        }

        $highPriorityOpen = $tasks->where('is_completed', false)->where('priority', '>=', 3)->count();
        $todayStart = Carbon::today()->startOfDay();
        $overdueOpen = $tasks->filter(function (Task $t) use ($todayStart) {
            return ! $t->is_completed && $t->due_date && $t->due_date->lt($todayStart);
        })->count();

        $durations = $events->map(function (Event $e) {
            if (! $e->start_date || ! $e->end_date) {
                return null;
            }

            return max(0, $e->start_date->diffInMinutes($e->end_date) / 60);
        })->filter(fn ($h) => $h !== null && $h > 0);
        $avgHours = $durations->isNotEmpty() ? round((float) $durations->avg(), 1) : null;
        $totalEventHours = (int) round($durations->sum());

        $multiDay = $events->filter(function (Event $e) {
            if (! $e->start_date || ! $e->end_date) {
                return false;
            }

            return ! $e->start_date->copy()->startOfDay()->equalTo($e->end_date->copy()->startOfDay());
        })->count();

        $weekdayIso = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0];
        foreach ($events as $e) {
            if ($e->start_date) {
                $d = (int) $e->start_date->dayOfWeekIso;
                $weekdayIso[$d] = ($weekdayIso[$d] ?? 0) + 1;
            }
        }
        foreach ($tasks as $t) {
            if ($t->due_date) {
                $d = (int) $t->due_date->dayOfWeekIso;
                $weekdayIso[$d] = ($weekdayIso[$d] ?? 0) + 1;
            }
        }
        $busiestIso = null;
        $busiestCount = 0;
        foreach ($weekdayIso as $iso => $c) {
            if ($c > $busiestCount) {
                $busiestCount = $c;
                $busiestIso = $iso;
            }
        }
        $labels = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $busiestLabel = $busiestIso !== null && $busiestCount > 0 ? $labels[$busiestIso] : null;

        $weekBuckets = $this->buildWeekBuckets($tasks, $events, $rangeStart, $rangeEnd);

        $bullets = $this->buildNarrativeBullets(
            $taskTotal,
            $taskCompleted,
            $completionRate,
            $events->count(),
            $highPriorityOpen,
            $overdueOpen,
            $multiDay,
            $avgHours,
            $busiestLabel,
            $busiestCount
        );

        return new PeriodReportInsights(
            completionRatePercent: $completionRate,
            tasksByPriority: $tasksByPriority,
            openTasksByPriority: $openByPriority,
            highPriorityOpenCount: $highPriorityOpen,
            overdueOpenCount: $overdueOpen,
            avgEventDurationHours: $avgHours,
            multiDayEventCount: $multiDay,
            eventsByWeekdayIso: $weekdayIso,
            busiestWeekdayLabel: $busiestLabel,
            busiestWeekdayCount: $busiestCount,
            weekBuckets: $weekBuckets,
            narrativeBullets: $bullets,
            totalEventHoursRounded: $totalEventHours,
        );
    }

    /**
     * @return list<array{label: string, task_count: int, event_count: int}>
     */
    private function buildWeekBuckets(Collection $tasks, Collection $events, Carbon $start, Carbon $end): array
    {
        $buckets = [];
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $chunkEnd = $cursor->copy()->addDays(6)->endOfDay();
            if ($chunkEnd->gt($end)) {
                $chunkEnd = $end->copy();
            }
            $label = $cursor->format('d.m').'–'.$chunkEnd->format('d.m');
            $taskCount = $tasks->filter(function (Task $t) use ($cursor, $chunkEnd) {
                if (! $t->due_date) {
                    return false;
                }

                return $t->due_date->gte($cursor) && $t->due_date->lte($chunkEnd);
            })->count();
            $eventCount = $events->filter(function (Event $e) use ($cursor, $chunkEnd) {
                return $e->start_date && $e->end_date
                    && $e->start_date->lte($chunkEnd) && $e->end_date->gte($cursor);
            })->count();
            $buckets[] = ['label' => $label, 'task_count' => $taskCount, 'event_count' => $eventCount];
            $cursor = $chunkEnd->copy()->addDay()->startOfDay();
        }

        return $buckets;
    }

    /**
     * @return list<string>
     */
    private function buildNarrativeBullets(
        int $taskTotal,
        int $taskCompleted,
        int $completionRate,
        int $eventTotal,
        int $highPriorityOpen,
        int $overdueOpen,
        int $multiDay,
        ?float $avgHours,
        ?string $busiestLabel,
        int $busiestCount
    ): array {
        $lines = [];
        if ($taskTotal === 0 && $eventTotal === 0) {
            $lines[] = 'Bu pencerede kayıtlı görev veya kesişen etkinlik bulunmuyor; aralığı genişleterek tekrar bakabilirsin.';

            return $lines;
        }

        if ($taskTotal > 0) {
            $lines[] = "Görevlerin %{$completionRate}'i tamamlanmış durumda ({$taskCompleted}/{$taskTotal}).";
        }
        if ($highPriorityOpen > 0) {
            $lines[] = "Yüksek öncelikli ve hâlâ açık {$highPriorityOpen} görev dikkat istiyor.";
        }
        if ($overdueOpen > 0) {
            $lines[] = "Bugünün öncesinde son tarihi geçmiş {$overdueOpen} açık görev var.";
        }
        if ($eventTotal > 0 && $avgHours !== null) {
            $lines[] = 'Etkinliklerde ortalama süre yaklaşık '.$avgHours.' saat; yoğun günlerde nefes payı bırakmayı unutma.';
        }
        if ($multiDay > 0) {
            $lines[] = "{$multiDay} etkinlik birden fazla günü kapsıyor — takvim blokajını kontrol etmek faydalı olur.";
        }
        if ($busiestLabel && $busiestCount > 0) {
            $lines[] = "En yoğun gün {$busiestLabel} ({$busiestCount} kayıt bir arada).";
        }
        if ($eventTotal > $taskTotal && $taskTotal > 0) {
            $lines[] = 'Bu dönemde etkinlik sayısı görevlere göre daha yüksek; odak zamanı planlamayı düşünebilirsin.';
        } elseif ($taskTotal > $eventTotal * 2 && $eventTotal > 0) {
            $lines[] = 'Görev ağırlığı baskın; araya kısa bloklar eklemek akışı iyileştirebilir.';
        }

        return array_slice($lines, 0, 6);
    }
}
