<?php

namespace App\Repositories;

use App\Models\Task;
use App\Support\RelationalTextSearch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class TaskRepository extends BaseRepository
{
    /**
     * TaskRepository constructor.
     */
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all tasks for a user.
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
     * Kullanıcıya ait görevleri filtre ve sıralama ile listeler.
     *
     * @param  array<string, mixed>  $filters
     */
    public function filteredForUser(int $userId, array $filters = [], array $relations = ['categories']): Collection
    {
        $query = $this->newFilteredListQuery($userId, $filters, $relations);

        return $query->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function filteredForUserPaginated(int $userId, array $filters, int $perPage, array $relations = ['categories']): LengthAwarePaginator
    {
        $query = $this->newFilteredListQuery($userId, $filters, $relations);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function newFilteredListQuery(int $userId, array $filters, array $relations): Builder
    {
        $query = $this->model->where('user_id', $userId)->with($relations);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', (int) $filters['priority']);
        }

        if (array_key_exists('is_completed', $filters) && $filters['is_completed'] !== null && $filters['is_completed'] !== '') {
            $val = filter_var($filters['is_completed'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($val !== null) {
                $query->where('is_completed', $val);
            }
        }

        if (! empty($filters['due_from'])) {
            $query->whereDate('due_date', '>=', $filters['due_from']);
        }

        if (! empty($filters['due_to'])) {
            $query->whereDate('due_date', '<=', $filters['due_to']);
        }

        if (! empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];
            $query->whereHas('categories', function (Builder $q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        if (! empty($filters['q']) && is_string($filters['q'])) {
            RelationalTextSearch::apply($query, $filters['q'], ['title', 'description']);
        }

        $sort = $filters['sort'] ?? 'due_date';
        $dir = strtolower((string) ($filters['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['due_date', 'priority', 'created_at', 'title', 'status'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'due_date';
        }
        $query->orderBy($sort, $dir);

        return $query;
    }

    /**
     * Get pending tasks for a user.
     */
    public function getPendingTasks(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_completed', false)
            ->with(['categories'])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get completed tasks for a user.
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
     */
    public function markAsCompleted(int $taskId): bool
    {
        $task = $this->findById($taskId);

        return $task->update(['is_completed' => true]);
    }

    /**
     * Mark a task as pending.
     */
    public function markAsPending(int $taskId): bool
    {
        $task = $this->findById($taskId);

        return $task->update(['is_completed' => false]);
    }

    /**
     * Get tasks for calendar based on filters.
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
     */
    public function createFromCalendar(array $data): Task
    {
        $data['user_id'] = Auth::id();

        return $this->model->create($data);
    }

    /**
     * Update a task from calendar data.
     */
    public function updateFromCalendar(Task $task, array $data): Task
    {
        $task->update($data);

        return $task;
    }

    /**
     * Delete a task from calendar.
     */
    public function deleteFromCalendar(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Format a task for calendar.
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
            'type' => 'task',
        ];
    }

    /**
     * Get color for a task status.
     */
    private function getStatusColor(string $status): string
    {
        $colors = [
            'pending' => '#FFA500',    // Turuncu
            'in-progress' => '#4682B4', // Çelik Mavisi
            'completed' => '#32CD32',    // Lime Yeşili
        ];

        return $colors[$status] ?? '#808080'; // Varsayılan gri
    }

    /**
     * Get color for a task priority.
     */
    private function getPriorityColor(int $priority): string
    {
        $colors = [
            1 => '#5CB85C', // Düşük - Yeşil
            2 => '#F0AD4E', // Orta - Sarı
            3 => '#D9534F',  // Yüksek - Kırmızı
        ];

        return $colors[$priority] ?? '#5CB85C'; // Varsayılan düşük öncelik rengi
    }
}
