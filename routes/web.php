<?php

use App\Http\Controllers\Api\EventController as ApiEventController;
use App\Http\Controllers\Api\TaskController as ApiTaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
})->middleware('guest');

// Özel middleware ile korunan rotalar
Route::middleware(['ensure.auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Events
    Route::resource('events', EventController::class);
    Route::get('/events/date-range', [EventController::class, 'dateRange'])->name('events.date-range');
    
    // Tasks
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::patch('/tasks/{task}/pending', [TaskController::class, 'pending'])->name('tasks.pending');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Calendar API Routes - Özel middleware ile korunan api rotaları
Route::middleware(['ensure.auth'])->prefix('api/calendar')->group(function () {
    // Events
    Route::get('/events', [ApiEventController::class, 'index']);
    Route::post('/events', [ApiEventController::class, 'store']);
    Route::put('/events/{event}', [ApiEventController::class, 'update']);
    Route::delete('/events/{event}', [ApiEventController::class, 'destroy']);
    
    // Tasks
    Route::get('/tasks', [ApiTaskController::class, 'index']);
    Route::post('/tasks', [ApiTaskController::class, 'store']);
    Route::put('/tasks/{task}', [ApiTaskController::class, 'update']);
    Route::delete('/tasks/{task}', [ApiTaskController::class, 'destroy']);
});

// Message routes
Route::prefix('messages')->middleware(['ensure.auth'])->group(function () {
    Route::get('/', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/date-range', [MessageController::class, 'dateRange'])->name('messages.date-range');
    Route::get('/type/{type}', [MessageController::class, 'byType'])->name('messages.by-type');
    Route::get('/failed', [MessageController::class, 'failed'])->name('messages.failed');
    Route::get('/today', [MessageController::class, 'today'])->name('messages.today');
    Route::get('/statistics', [MessageController::class, 'statistics'])->name('messages.statistics');
});

// Chat routes
Route::post('/api/chat/send', [App\Http\Controllers\API\ChatController::class, 'send'])->middleware('ensure.auth');

// Google Takvim Entegrasyonu için route'lar
Route::middleware(['ensure.auth'])->group(function () {
    // Auth Routes
    Route::get('/auth/google', [App\Http\Controllers\GoogleAuthController::class, 'redirectToGoogle'])->name('google.auth');
    Route::get('/auth/google/callback', [App\Http\Controllers\GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::post('/auth/google/disconnect', [App\Http\Controllers\GoogleAuthController::class, 'disconnectGoogle'])->name('google.disconnect');
    
    // Sync Routes
    Route::get('/calendar/sync', [App\Http\Controllers\CalendarSyncController::class, 'syncPage'])->name('calendar.sync');
    Route::post('/calendar/sync/google', [App\Http\Controllers\CalendarSyncController::class, 'syncWithGoogle'])->name('calendar.sync.google');
    Route::post('/calendar/import/google', [App\Http\Controllers\CalendarSyncController::class, 'importFromGoogle'])->name('calendar.import.google');
    Route::get('/calendar/settings', [App\Http\Controllers\CalendarSyncController::class, 'settings'])->name('calendar.settings');
});

Route::middleware(['ensure.auth'])->group(function () {
    Route::post('/save-device-token', [App\Http\Controllers\DeviceController::class, 'saveToken'])->name('save.device.token');
    Route::post('/delete-device-token', [App\Http\Controllers\DeviceController::class, 'deleteToken'])->name('delete.device.token');
});

require __DIR__.'/auth.php';
