<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalendarTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected $service;

    public function __construct(TaskService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $tasks = $this->service->getCalendarTasks([
            'start' => $request->input('start'),
            'end' => $request->input('end')
        ]);

        return response()->json($tasks);
    }

    public function store(CalendarTaskRequest $request): JsonResponse
    {
        $task = $this->service->handleCalendarCreate($request->validated());

        return response()->json([
            'message' => 'Görev başarıyla oluşturuldu.',
            'task' => $this->service->formatForCalendar($task)
        ]);
    }

    public function update(CalendarTaskRequest $request, Task $task): JsonResponse
    {
        $task = $this->service->handleCalendarUpdate($task, $request->validated());

        return response()->json([
            'message' => 'Görev başarıyla güncellendi.',
            'task' => $this->service->formatForCalendar($task)
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->service->handleCalendarDelete($task);

        return response()->json([
            'message' => 'Görev başarıyla silindi.'
        ]);
    }
} 