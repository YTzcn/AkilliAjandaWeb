<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LLMController;
use Gemini\Laravel\Facades\Gemini;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;

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

Route::middleware('auth')->group(function () {
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

    // Event Routes
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']);
        Route::get('/{event}', [EventController::class, 'show']);
        Route::put('/{event}', [EventController::class, 'update']);
        Route::delete('/{event}', [EventController::class, 'destroy']);
        Route::get('/calendar/{year}/{month}', [EventController::class, 'getMonthlyEvents']);
        Route::get('/upcoming', [EventController::class, 'getUpcomingEvents']);
        Route::post('/{event}/share', [EventController::class, 'shareEvent']);
        Route::post('/{event}/reminder', [EventController::class, 'setReminder']);
    });
});
