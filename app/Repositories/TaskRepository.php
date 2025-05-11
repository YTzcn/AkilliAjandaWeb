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
     * Get all tasks for a user.
     *
     * @param int $userId
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function allByUser(int $userId, array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->with($relations)
            ->orderBy('due_date')
            ->get($columns);
    }

    /**
     * Get pending tasks for a user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getPendingTasks(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_completed', false)
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get completed tasks for a user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getCompletedTasks(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_completed', true)
            ->orderBy('due_date', 'desc')
            ->get();
    }

    /**
     * Get tasks by priority level for a user.
     *
     * @param int $userId
     * @param int $level
     * @return Collection
     */
    public function getByPriority(int $userId, int $level): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('priority', $level)
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get tasks due today for a user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getDueToday(int $userId): Collection
    {
        $today = Carbon::today();
        
        return $this->model
            ->where('user_id', $userId)
            ->whereDate('due_date', $today)
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get overdue tasks for a user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getOverdue(int $userId): Collection
    {
        $today = Carbon::today();
        
        return $this->model
            ->where('user_id', $userId)
            ->where('is_completed', false)
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
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

    /**
     * Get tasks for calendar based on filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getForCalendar(array $filters = []): Collection
    {
        $query = $this->model->where('user_id', Auth::id());
        
        if (isset($filters['start']) && isset($filters['end'])) {
            $startDate = Carbon::parse($filters['start']);
            $endDate = Carbon::parse($filters['end']);
            
            $query->whereBetween('due_date', [$startDate, $endDate]);
        }
        
        return $query->orderBy('due_date')->get();
    }

    /**
     * Create a task from calendar data.
     *
     * @param array $data
     * @return Task
     */
    public function createFromCalendar(array $data): Task
    {
        $data['user_id'] = Auth::id();
        return $this->model->create($data);
    }

    /**
     * Update a task from calendar data.
     *
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function updateFromCalendar(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    /**
     * Delete a task from calendar.
     *
     * @param Task $task
     * @return bool
     */
    public function deleteFromCalendar(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Format a task for calendar.
     *
     * @param Task $task
     * @return array
     */
    public function formatForCalendar(Task $task): array
    {
        $allDay = false;
        $statusColor = $this->getStatusColor($task->status ?? 'pending');
        $priorityColor = $this->getPriorityColor($task->priority ?? 1);
        
        return [
            'id' => $task->id,
            'title' => $task->title,
            'start' => $task->due_date->format('Y-m-d H:i:s'),
            'end' => $task->due_date->format('Y-m-d H:i:s'),
            'allDay' => $allDay,
            'description' => $task->description,
            'status' => $task->status ?? 'pending',
            'priority' => $task->priority ?? 1,
            'is_completed' => $task->is_completed,
            'statusColor' => $statusColor,
            'priorityColor' => $priorityColor,
            'type' => 'task'
        ];
    }

    /**
     * Get color for a task status.
     *
     * @param string $status
     * @return string
     */
    private function getStatusColor(string $status): string
    {
        $colors = [
            'pending' => '#FFA500',    // Turuncu
            'in-progress' => '#4682B4', // Çelik Mavisi
            'completed' => '#32CD32'    // Lime Yeşili
        ];
        
        return $colors[$status] ?? '#808080'; // Varsayılan gri
    }

    /**
     * Get color for a task priority.
     *
     * @param int $priority
     * @return string
     */
    private function getPriorityColor(int $priority): string
    {
        $colors = [
            1 => '#5CB85C', // Düşük - Yeşil
            2 => '#F0AD4E', // Orta - Sarı
            3 => '#D9534F'  // Yüksek - Kırmızı
        ];
        
        return $colors[$priority] ?? '#5CB85C'; // Varsayılan düşük öncelik rengi
    }
} 