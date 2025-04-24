<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

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
} 