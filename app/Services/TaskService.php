<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;
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
        return $this->taskRepository->allByUser(Auth::id());
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
     * Create a new task.
     *
     * @param array $data
     * @return Task
     */
    public function createTask(array $data): Task
    {
        $data['user_id'] = Auth::id();
        return $this->taskRepository->create($data);
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
        return $this->taskRepository->update($taskId, $data);
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
        return $this->taskRepository->findById($taskId);
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