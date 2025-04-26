<?php

use App\Http\Controllers\Api\EventController as ApiEventController;
use App\Http\Controllers\Api\TaskController as ApiTaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
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

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Events
Route::resource('events', EventController::class);
Route::get('/events/date-range', [EventController::class, 'dateRange'])->name('events.date-range');

// Tasks
Route::resource('tasks', TaskController::class);
Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
Route::patch('/tasks/{task}/pending', [TaskController::class, 'pending'])->name('tasks.pending');

// Calendar API Routes
Route::middleware('auth')->prefix('api/calendar')->group(function () {
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

// Profile
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

require __DIR__.'/auth.php';
