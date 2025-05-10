<?php

namespace App\Services;

use App\Models\Notification;
use App\Repositories\NotificationRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    protected NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Get all notifications for a user.
     */
    public function getUserNotifications(int $userId, array $filters = []): Collection
    {
        return $this->notificationRepository->getUserNotifications($userId, $filters);
    }
    
    /**
     * Get paginated notifications for a user.
     */
    public function getPaginatedNotifications(int $userId, int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        return $this->notificationRepository->getPaginatedNotifications($userId, $perPage, $filters);
    }
    
    /**
     * Create a new notification.
     */
    public function createNotification(array $data): Notification
    {
        return $this->notificationRepository->create($data);
    }
    
    /**
     * Create a new notification for a user.
     */
    public function notifyUser(int $userId, string $title, string $message, string $type = 'info', array $data = []): Notification
    {
        return $this->notificationRepository->create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);
    }
    
    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $id): bool
    {
        return $this->notificationRepository->markAsRead($id);
    }
    
    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): bool
    {
        return $this->notificationRepository->markAllAsRead($userId);
    }
    
    /**
     * Delete a notification.
     */
    public function deleteNotification(int $id): bool
    {
        return $this->notificationRepository->delete($id);
    }
    
    /**
     * Get unread notification count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->notificationRepository->getUserNotifications($userId, ['is_read' => false])->count();
    }
} 