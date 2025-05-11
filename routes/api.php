<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LLMController;
use Gemini\Laravel\Facades\Gemini;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\TaskController;

// Kullanıcı bilgisi
Route::middleware('ensure.auth')->get('/user', function (Request $request) {
    return $request->user();
});

// LLM işlemleri
Route::prefix('llm')->group(function () {
    // Kullanıcı mesajını işler
    Route::post('/process', [LLMController::class, 'processMessage']);
    
    // Kullanılabilir sağlayıcıları listeler
    Route::get('/providers', [LLMController::class, 'listProviders']);
    
    // Kullanılabilir modelleri listeler
    Route::get('/models', [LLMController::class, 'listModels']);
});

Route::prefix('messages')->group(function () {
    Route::get('/', [MessageController::class, 'index']);
    Route::get('/date-range', [MessageController::class, 'getByDateRange']);
    Route::get('/type/{type}', [MessageController::class, 'getByType']);
    Route::get('/failed', [MessageController::class, 'getFailedMessages']);
    Route::get('/today', [MessageController::class, 'getTodaysMessages']);
    Route::get('/statistics', [MessageController::class, 'getStatistics']);
});

// Chat endpoint
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/chat/send', [App\Http\Controllers\API\ChatController::class, 'send']);
});

// Notification Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [App\Http\Controllers\Api\NotificationController::class, 'getUnreadCount']);
    Route::get('/notifications/{notification}', [App\Http\Controllers\Api\NotificationController::class, 'show']);
    Route::post('/notifications/{notification}/mark-as-read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [App\Http\Controllers\Api\NotificationController::class, 'destroy']);
    Route::post('/save-device-token', [DeviceController::class, 'saveToken']);
});

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Google Calendar Routes
    Route::prefix('google')->group(function () {
        Route::get('/auth-url', [App\Http\Controllers\Api\GoogleCalendarController::class, 'getAuthUrl']);
        Route::post('/callback', [App\Http\Controllers\Api\GoogleCalendarController::class, 'handleCallback']);
        Route::post('/disconnect', [App\Http\Controllers\Api\GoogleCalendarController::class, 'disconnect']);
        Route::get('/events', [App\Http\Controllers\Api\GoogleCalendarController::class, 'listEvents']);
        Route::post('/import-events', [App\Http\Controllers\Api\GoogleCalendarController::class, 'importEvents']);
        Route::get('/connection-status', [App\Http\Controllers\Api\GoogleCalendarController::class, 'connectionStatus']);
        
        // Dışa aktarma/senkronizasyon rotaları
        Route::post('/sync-event', [App\Http\Controllers\Api\GoogleCalendarController::class, 'syncEventToGoogle']);
        Route::delete('/remove-event/{event_id}', [App\Http\Controllers\Api\GoogleCalendarController::class, 'removeEventFromGoogle']);
        Route::post('/sync-all-events', [App\Http\Controllers\Api\GoogleCalendarController::class, 'syncAllEventsToGoogle']);
        Route::post('/sync-tasks', [App\Http\Controllers\Api\GoogleCalendarController::class, 'syncTasksToGoogle']);
    });

    // Event Routes
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']);
        Route::get('/{event}', [EventController::class, 'show']);
        Route::put('/{event}', [EventController::class, 'update']);
        Route::delete('/{event}', [EventController::class, 'destroy']);
    });
    
    // Task Routes
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{task}', [TaskController::class, 'show']);
        Route::put('/{task}', [TaskController::class, 'update']);
        Route::delete('/{task}', [TaskController::class, 'destroy']);
    });
});
