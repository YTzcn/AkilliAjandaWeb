<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Task;
use App\Support\RelationalTextSearch;
use Illuminate\Support\Facades\Auth;

class GlobalSearchService
{
    /**
     * Üst çubuk hızlı arama: görev ve etkinlik özetleri (sınırlı kayıt).
     *
     * @return array{tasks: list<array<string, mixed>>, events: list<array<string, mixed>>}
     */
    public function quickResults(string $query, int $perType = 10): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['tasks' => [], 'events' => []];
        }

        $userId = (int) Auth::id();

        $taskQuery = Task::query()
            ->where('user_id', $userId)
            ->select(['id', 'title', 'due_date', 'status', 'is_completed']);
        RelationalTextSearch::apply($taskQuery, $query);
        $tasks = $taskQuery->orderBy('due_date')->limit($perType)->get();

        $eventQuery = Event::query()
            ->where('user_id', $userId)
            ->select(['id', 'title', 'start_date', 'end_date']);
        RelationalTextSearch::apply($eventQuery, $query);
        $events = $eventQuery->orderByDesc('start_date')->limit($perType)->get();

        return [
            'tasks' => $tasks->map(fn (Task $t) => [
                'id' => $t->id,
                'title' => $t->title,
                'url' => route('tasks.show', $t),
                'subtitle' => $t->due_date?->format('d.m.Y H:i') ?? '',
                'status' => $t->status,
                'is_completed' => $t->is_completed,
            ])->all(),
            'events' => $events->map(fn (Event $e) => [
                'id' => $e->id,
                'title' => $e->title,
                'url' => route('events.show', $e),
                'subtitle' => ($e->start_date?->format('d.m.Y H:i') ?? '').' – '.($e->end_date?->format('d.m.Y H:i') ?? ''),
            ])->all(),
        ];
    }
}
