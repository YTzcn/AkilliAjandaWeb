<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $perPage = $request->input('per_page', 10);
        
        $filters = [
            'is_read' => $request->has('is_read') ? (bool) $request->input('is_read') : null,
            'type' => $request->input('type'),
        ];
        
        $notifications = $this->notificationService->getPaginatedNotifications($userId, $perPage, $filters);
        
        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }
    
    /**
     * Get a single notification.
     */
    public function show(Notification $notification): JsonResponse
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return response()->json(['data' => $notification]);
    }
    
    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $success = $this->notificationService->markAsRead($notification->id);
        
        if (!$success) {
            return response()->json(['message' => 'Failed to mark notification as read'], 500);
        }
        
        return response()->json(['message' => 'Notification marked as read']);
    }
    
    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead(): JsonResponse
    {
        $userId = auth()->id();
        $success = $this->notificationService->markAllAsRead($userId);
        
        if (!$success) {
            return response()->json(['message' => 'Failed to mark notifications as read'], 500);
        }
        
        return response()->json(['message' => 'All notifications marked as read']);
    }
    
    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $success = $this->notificationService->deleteNotification($notification->id);
        
        if (!$success) {
            return response()->json(['message' => 'Failed to delete notification'], 500);
        }
        
        return response()->json(['message' => 'Notification deleted']);
    }
    
    /**
     * Get unread notification count for the authenticated user.
     */
    public function getUnreadCount(): JsonResponse
    {
        $userId = auth()->id();
        $count = $this->notificationService->getUnreadCount($userId);
        
        return response()->json(['count' => $count]);
    }
} 