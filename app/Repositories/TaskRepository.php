<?php

namespace App\Repositories;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class TaskRepository extends BaseRepository
{
    /**
     * TaskRepository constructor.
     *
     * @param Task $model
     */
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    /**
     * Get pending tasks for a user.
     *
     * @return Collection
     */
    public function getPendingTasks(): Collection
    {
        return $this->model
            ->where('user_id', Auth::id())
            ->where('is_completed', false)
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get completed tasks for a user.
     *
     * @return Collection
     */
    public function getCompletedTasks(): Collection
    {
        return $this->model
            ->where('user_id', Auth::id())
            ->where('is_completed', true)
            ->orderBy('due_date', 'desc')
            ->get();
    }

    /**
     * Mark a task as completed.
     *
     * @param int $taskId
     * @return bool
     */
    public function markAsCompleted(int $taskId): bool
    {
        $task = $this->findById($taskId);
        return $task->update(['is_completed' => true]);
    }

    /**
     * Mark a task as pending.
     *
     * @param int $taskId
     * @return bool
     */
    public function markAsPending(int $taskId): bool
    {
        $task = $this->findById($taskId);
        return $task->update(['is_completed' => false]);
    }

    public function getForCalendar(array $filters = []): Collection
    {
        $query = $this->model->query()
            ->where('user_id', Auth::id())
            ->where('is_completed', false);

        if (isset($filters['start'])) {
            $query->where('due_date', '>=', $filters['start']);
        }

        if (isset($filters['end'])) {
            $query->where('due_date', '<=', $filters['end']);
        }

        return $query->get();
    }

    public function createFromCalendar(array $data): Task
    {
        $data['user_id'] = Auth::id();
        $data['status'] = $data['status'] ?? 'pending';
        $data['priority'] = $data['priority'] ?? 2;
        
        // Tarihi UTC'ye çevir
        $data['due_date'] = Carbon::parse($data['due_date']);

        return $this->model->create($data);
    }

    public function updateFromCalendar(Task $task, array $data): Task
    {
        // Sadece tarih güncellemesi ise
        if (count($data) === 1 && isset($data['due_date'])) {
            $task->update([
                'due_date' => Carbon::parse($data['due_date'])
            ]);
            return $task;
        }

        // Tam güncelleme
        if (isset($data['due_date'])) {
            $data['due_date'] = Carbon::parse($data['due_date']);
        }

        $task->update($data);
        return $task;
    }

    public function deleteFromCalendar(Task $task): bool
    {
        return $task->delete();
    }

    public function formatForCalendar(Task $task): array
    {
        $priorityColors = [
            1 => '#28a745', // Düşük - Yeşil
            2 => '#ffc107', // Orta - Sarı
            3 => '#dc3545'  // Yüksek - Kırmızı
        ];

        return [
            'id' => $task->id,
            'title' => $task->title,
            'start' => $task->due_date->toIso8601String(),
            'end' => $task->due_date->toIso8601String(),
            'description' => $task->description,
            'allDay' => true,
            'className' => 'calendar-task priority-' . $task->priority,
            'backgroundColor' => $priorityColors[$task->priority],
            'extendedProps' => [
                'type' => 'task',
                'priority' => $task->priority,
                'status' => $task->status
            ]
        ];
    }
} 