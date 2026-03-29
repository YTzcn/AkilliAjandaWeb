<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    /**
     * @var TaskRepository
     */
    protected $taskRepository;

    /**
     * TaskService constructor.
     *
     * @param TaskRepository $taskRepository
     */
    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Get all tasks for the authenticated user.
     *
     * @return Collection
     */
    public function getAllTasks(): Collection
    {
        return $this->taskRepository->allByUser(Auth::id(), ['*'], ['categories']);
    }

    /**
     * Web/API filtre parametreleriyle görevleri döndürür.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFilteredTasks(array $filters): Collection
    {
        return $this->taskRepository->filteredForUser(Auth::id(), $filters);
    }

    /**
     * Son tarihi verilen aralıkta olan bekleyen görev sayısı.
     */
    public function countPendingDueBetween(\Carbon\Carbon $start, \Carbon\Carbon $end): int
    {
        return Task::query()
            ->where('user_id', Auth::id())
            ->where('is_completed', false)
            ->where('due_date', '>=', $start->copy()->startOfDay())
            ->where('due_date', '<=', $end->copy()->endOfDay())
            ->count();
    }

    /**
     * Belirtilen hafta içinde tamamlanan görev sayısı (güncelleme zamanına göre).
     */
    public function countCompletedInPeriod(\Carbon\Carbon $start, \Carbon\Carbon $end): int
    {
        return Task::query()
            ->where('user_id', Auth::id())
            ->where('is_completed', true)
            ->whereBetween('updated_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->count();
    }

    /**
     * Get pending tasks for the authenticated user.
     *
     * @return Collection
     */
    public function getPendingTasks(): Collection
    {
        return $this->taskRepository->getPendingTasks(Auth::id());
    }

    /**
     * Get completed tasks for the authenticated user.
     *
     * @return Collection
     */
    public function getCompletedTasks(): Collection
    {
        return $this->taskRepository->getCompletedTasks(Auth::id());
    }

    /**
     * Get tasks by priority level for the authenticated user.
     *
     * @param int $level
     * @return Collection
     */
    public function getTasksByPriority(int $level): Collection
    {
        return $this->taskRepository->getByPriority(Auth::id(), $level);
    }

    /**
     * Get tasks due today for the authenticated user.
     *
     * @return Collection
     */
    public function getTasksDueToday(): Collection
    {
        return $this->taskRepository->getDueToday(Auth::id());
    }

    /**
     * Get overdue tasks for the authenticated user.
     *
     * @return Collection
     */
    public function getOverdueTasks(): Collection
    {
        return $this->taskRepository->getOverdue(Auth::id());
    }

    /**
     * Create a new task.
     *
     * @param array $data
     * @return Task
     */
    public function createTask(array $data): Task
    {
        $categoryIds = [];
        if (array_key_exists('category_ids', $data)) {
            $categoryIds = $data['category_ids'] ?? [];
            unset($data['category_ids']);
        }
        $data['user_id'] = Auth::id();
        $task = $this->taskRepository->create($data);
        if ($categoryIds !== []) {
            $task->categories()->sync($categoryIds);
        }

        return $task->fresh(['categories']);
    }

    /**
     * Update an existing task.
     *
     * @param int $taskId
     * @param array $data
     * @return Task|null
     */
    public function updateTask(int $taskId, array $data): ?Task
    {
        $categoryIds = null;
        if (array_key_exists('category_ids', $data)) {
            $categoryIds = $data['category_ids'] ?? [];
            unset($data['category_ids']);
        }
        $task = $this->taskRepository->update($taskId, $data);
        if ($task && $categoryIds !== null) {
            $task->categories()->sync($categoryIds);

            return $task->fresh(['categories']);
        }

        return $task ? $task->load('categories') : null;
    }

    /**
     * Delete a task.
     *
     * @param int $taskId
     * @return bool
     */
    public function deleteTask(int $taskId): bool
    {
        return $this->taskRepository->deleteById($taskId);
    }

    /**
     * Get a specific task by ID.
     *
     * @param int $taskId
     * @return Task|null
     */
    public function getTaskById(int $taskId): ?Task
    {
        /** @var Task $task */
        $task = $this->taskRepository->findById($taskId, ['*'], ['categories']);

        return $task;
    }

    /**
     * Mark task as completed.
     *
     * @param int $taskId
     * @return bool
     */
    public function markAsCompleted(int $taskId): bool
    {
        return $this->taskRepository->markAsCompleted($taskId);
    }

    /**
     * Mark task as pending.
     *
     * @param int $taskId
     * @return bool
     */
    public function markAsPending(int $taskId): bool
    {
        return $this->taskRepository->markAsPending($taskId);
    }

    public function getCalendarTasks(array $filters = []): array
    {
        $tasks = $this->taskRepository->getForCalendar($filters);
        return $tasks->map(function ($task) {
            return $this->formatForCalendar($task);
        })->toArray();
    }

    public function handleCalendarCreate(array $data): Task
    {
        return $this->taskRepository->createFromCalendar($data);
    }

    public function handleCalendarUpdate(Task $task, array $data): Task
    {
        return $this->taskRepository->updateFromCalendar($task, $data);
    }

    public function handleCalendarDelete(Task $task): bool
    {
        return $this->taskRepository->deleteFromCalendar($task);
    }

    public function formatForCalendar(Task $task): array
    {
        return $this->taskRepository->formatForCalendar($task);
    }
} 