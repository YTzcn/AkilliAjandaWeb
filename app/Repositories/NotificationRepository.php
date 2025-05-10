<?php

namespace App\Repositories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationRepository
{
    /**
     * Get all notifications for a user.
     */
    public function getUserNotifications(int $userId, array $filters = []): Collection
    {
        $query = Notification::query()->where('user_id', $userId);
        
        if (isset($filters['is_read']) && $filters['is_read'] !== null) {
            $query->where('is_read', $filters['is_read']);
        }
        
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }
    
    /**
     * Get paginated notifications for a user.
     */
    public function getPaginatedNotifications(int $userId, int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Notification::query()->where('user_id', $userId);
        
        if (isset($filters['is_read']) && $filters['is_read'] !== null) {
            $query->where('is_read', $filters['is_read']);
        }
        
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
    
    /**
     * Create a new notification.
     */
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }
    
    /**
     * Get a notification by ID.
     */
    public function find(int $id): ?Notification
    {
        return Notification::find($id);
    }
    
    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $id): bool
    {
        $notification = $this->find($id);
        
        if (!$notification) {
            return false;
        }
        
        return $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
    
    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): bool
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
    
    /**
     * Delete a notification.
     */
    public function delete(int $id): bool
    {
        $notification = $this->find($id);
        
        if (!$notification) {
            return false;
        }
        
        return $notification->delete();
    }
} 