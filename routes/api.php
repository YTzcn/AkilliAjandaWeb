<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LLMController;
use Gemini\Laravel\Facades\Gemini;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\DeviceController;

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

// Gemini modelleri listesi
Route::get('/models', function(){
    try {
        $response = Gemini::models()->list();
        return response()->json([
            'success' => true,
            'models' => $response->models ?? []
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'error_trace' => app()->environment('production') ? null : $e->getTraceAsString()
        ], 500);
    }
});

// Gemini model testi - doğrudan bir prompt göndererek test etmek için
Route::post('/test-model', function(Request $request){
    try {
        $request->validate([
            'prompt' => 'required|string',
            'model' => 'nullable|string'
        ]);
        
        $model = $request->input('model', 'gemini-1.5-pro');
        $response = Gemini::generativeModel($model)->generateContent($request->input('prompt'));
        
        return response()->json([
            'success' => true,
            'response' => $response->text()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'error_trace' => app()->environment('production') ? null : $e->getTraceAsString()
        ], 500);
    }
});

// Message routes
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
